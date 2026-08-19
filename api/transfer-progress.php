<?php

require __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

header('Content-Type: application/json');

try {
    $app = new Application();
    $db = $app->database()->connection();

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $jobId    = $input['job_id'] ?? null;
    $filename = $input['filename'] ?? null;
    $progress = $input['progress'] ?? 0;
    $status   = $input['status'] ?? 'uploading';

    if (!$jobId || !$filename) {
        throw new Exception('Missing required fields');
    }

    $stmt = $pdo->prepare("
        INSERT INTO transfer_progress (
            job_id,
            file_name,
            total_bytes,
            uploaded_bytes,
            speed,
            status,
            updated_at
        )
        VALUES (
            :job_id,
            :file_name,
            :total_bytes,
            :uploaded_bytes,
            :speed,
            :status,
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            total_bytes = VALUES(total_bytes),
            uploaded_bytes = VALUES(uploaded_bytes),
            speed = VALUES(speed),
            status = VALUES(status),
            updated_at = NOW()
    ");

    $stmt->execute([
        ':job_id'         => $jobId,
        ':file_name'      => $fileName,
        ':total_bytes'    => $total,
        ':uploaded_bytes' => $uploaded,
        ':speed'          => $speed,
        ':status'         => $status
    ]);

    echo json_encode([
        'success' => true
    ]);

} catch (Throwable $e) {

    file_put_contents(
        __DIR__ . '/../storage/logs/transfer-progress-error.log',
        '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . PHP_EOL,
        FILE_APPEND
    );

    http_response_code(500);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}