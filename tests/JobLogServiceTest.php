<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Services\JobLogService;

$app = new Application();

$app->database()->getConnection()->exec("DELETE FROM system_logs");

$service = new JobLogService();
$service->logCreate(
    'Trabajo de prueba',
    7,
    3,
    12,
    'tester'
);

$count = (int)$app
    ->database()
    ->getConnection()
    ->query("SELECT COUNT(*) FROM system_logs WHERE module = 'JOBS' AND action = 'CREATE'")
    ->fetchColumn();

if ($count < 1) {
    throw new RuntimeException('No se registró el evento de creación del trabajo en system_logs');
}

echo "Job log test OK" . PHP_EOL;
