<?php

require __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\AgentAuth;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Application;
use BackupCenter\Repositories\AgentRepository;

header('Content-Type: application/json');

try {

    $app = new Application();
    $pdo = $app->database()->getConnection();

    $authenticatedConnectionId = null;

    if (
        array_key_exists('HTTP_AUTHORIZATION', $_SERVER)
    ) {
        $agentIdentity = AgentAuth::validateAgentToken(new AgentRepository($pdo));
        $authenticatedConnectionId = $agentIdentity['connection_id'];
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON');
    }

    /*
    |--------------------------------------------------------------------------
    | Datos esperados (CONTRATO CON EL AGENT)
    |--------------------------------------------------------------------------
    */
    $jobId    = $input['job_id'] ?? null;
    $fileName = $input['file_name'] ?? null;
    $uploaded = $input['uploaded_bytes'] ?? 0;
    $total    = $input['total_bytes'] ?? 0;
    $speed    = $input['speed'] ?? 0;
    $status   = $input['status'] ?? 'uploading';

    if (!$jobId || !$fileName) {
        throw new Exception('Missing data');
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN
    |--------------------------------------------------------------------------
    */
    $jobId    = (int) $jobId;
    $uploaded = max(0, (int)$uploaded);
    $total    = max(0, (int)$total);
    $speed    = max(0, (float)$speed);

    if ($authenticatedConnectionId !== null) {
        $jobConnectionId = (new AgentRepository($pdo))->findJobConnectionId($jobId);

        if ($jobConnectionId === null) {
            ApiResponse::error('Job not found', 404);
        }

        if ($jobConnectionId !== $authenticatedConnectionId) {
            ApiResponse::error('Forbidden', 403);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECCIÓN: NO EXCEDER TOTAL
    |--------------------------------------------------------------------------
    */
    if ($total > 0) {
        $uploaded = min($uploaded, $total);
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADOS PERMITIDOS
    |--------------------------------------------------------------------------
    */
    $allowedStatuses = ['uploading', 'completed', 'failed'];

    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'uploading';
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO-DETECCIÓN DE COMPLETADO
    |--------------------------------------------------------------------------
    */
    if ($total > 0 && $uploaded >= $total) {
        $status = 'completed';
        $uploaded = $total;
    }

    /*
    |--------------------------------------------------------------------------
    | EVITAR REGRESIÓN DE PROGRESO
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        SELECT uploaded_bytes 
        FROM transfer_progress
        WHERE job_id = :job_id AND file_name = :file_name
    ");
    $stmt->execute([
        ':job_id' => $jobId,
        ':file_name' => $fileName
    ]);

    $current = $stmt->fetchColumn();

    if ($current !== false) {
        $uploaded = max($uploaded, (int)$current);
    }

    /*
    |--------------------------------------------------------------------------
    | UPSERT (INSERT / UPDATE)
    |--------------------------------------------------------------------------
    */
    $stmt = $pdo->prepare("
        INSERT INTO transfer_progress (
            job_id,
            file_name,
            uploaded_bytes,
            total_bytes,
            speed,
            status,
            updated_at
        )
        VALUES (
            :job_id,
            :file_name,
            :uploaded,
            :total,
            :speed,
            :status,
            datetime('now')
        )
        ON CONFLICT(job_id, file_name)
        DO UPDATE SET
            uploaded_bytes = excluded.uploaded_bytes,
            total_bytes    = excluded.total_bytes,
            speed          = excluded.speed,
            status         = excluded.status,
            updated_at     = datetime('now')
    ");

    $stmt->execute([
        ':job_id'   => $jobId,
        ':file_name'=> $fileName,
        ':uploaded' => $uploaded,
        ':total'    => $total,
        ':speed'    => $speed,
        ':status'   => $status
    ]);

    /*
    |--------------------------------------------------------------------------
    | NOTIFICACIONES
    |--------------------------------------------------------------------------
    */
    if ($status === 'completed') {

        $pdo->prepare("
            INSERT INTO notifications (
                level,
                title,
                message,
                created_at
            )
            VALUES (
                'SUCCESS',
                :title,
                :message,
                datetime('now')
            )
        ")->execute([
            ':title'   => 'Backup completado',
            ':message' => "Backup completado: $fileName"
        ]);
    }

    if ($status === 'failed') {

        $pdo->prepare("
            INSERT INTO notifications (
                level,
                title,
                message,
                created_at
            )
            VALUES (
                'ERROR',
                :title,
                :message,
                datetime('now')
            )
        ")->execute([
            ':title'   => 'Error en backup',
            ':message' => "Error en backup: $fileName"
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | RESPUESTA
    |--------------------------------------------------------------------------
    */
    echo json_encode([
        'success' => true,
        'data' => [
            'job_id' => $jobId,
            'file'   => $fileName,
            'progress' => ($total > 0) ? round(($uploaded / $total) * 100, 2) : 0,
            'status' => $status
        ]
    ]);

} catch (Throwable $e) {

    file_put_contents(
        __DIR__ . '/../../storage/logs/transfer-error.log',
        date('Y-m-d H:i:s') . ' - ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );

    http_response_code(500);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}