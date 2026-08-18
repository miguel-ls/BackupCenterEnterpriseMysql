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
    $progress = $input['progress'] ?? null;
    $status   = $input['status'] ?? 'uploading';

    if (!$jobId || !$filename) {
        throw new Exception('Missing required fields');
    }

    $stmt = $db->prepare("
        INSERT INTO transfer_progress (
            job_id,
            filename,
            progress,
            status,
            updated_at
        ) VALUES (
            :job_id,
            :filename,
            :progress,
            :status,
            datetime('now')
        )
        ON CONFLICT(job_id, filename)
        DO UPDATE SET
            progress = excluded.progress,
            status = excluded.status,
            updated_at = datetime('now')
    ");

    $stmt->execute([
        ':job_id'   => $jobId,
        ':filename' => $filename,
        ':progress' => $progress,
        ':status'   => $status
    ]);

    echo json_encode([
        'success' => true
    ]);

} catch (Throwable $e) {

    // 🔥 AHORA SÍ LOGUEA EL ERROR
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
