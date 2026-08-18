<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\JobQueueRepository;

Auth::require();

$app = new Application();

$repository = new JobQueueRepository(
    $app->database()
);

$repository->initialize();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true) ?: [];

    if (($payload['action'] ?? '') !== 'complete') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida.'
        ]);
        exit;
    }

    $completed = $repository->completePending((int)($payload['id'] ?? 0));

    if (!$completed) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'La cola no existe o ya no está pendiente.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cola completada.'
    ]);
    exit;
}

$filters = [
    'job_id' => (int)($_GET['job_id'] ?? 0),
    'client_id' => (int)($_GET['client_id'] ?? 0),
    'status' => trim((string)($_GET['status'] ?? '')),
    'from' => trim((string)($_GET['from'] ?? '')),
    'to' => trim((string)($_GET['to'] ?? '')),
];

$data = $repository->getAll($filters);

$clientRows = $app->database()->getConnection()->query("
    SELECT id, business_name AS name
    FROM clients
    ORDER BY business_name
")->fetchAll(PDO::FETCH_ASSOC);

$jobRows = $app->database()->getConnection()->query("
    SELECT id, name
    FROM jobs
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$statusRows = $app->database()->getConnection()->query("
    SELECT DISTINCT status
    FROM job_queue
    WHERE status IS NOT NULL AND TRIM(status) <> ''
    ORDER BY status
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => $data,
    'filters' => [
        'clients' => $clientRows,
        'jobs' => $jobRows,
        'statuses' => array_map(static function (array $row): string {
            return (string)($row['status'] ?? '');
        }, $statusRows)
    ]
]);