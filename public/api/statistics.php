<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Core\Auth;

Auth::require();

$db = new Database(
    Paths::database() . '/backupcenter.db'
);

$pdo = $db->getConnection();

$data = [

    'uploaded_today' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM uploaded_files
        WHERE date(uploaded_at)=date('now')
    ")->fetchColumn(),

    'executions_today' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM execution_history
        WHERE date(started_at)=date('now')
    ")->fetchColumn(),

    'errors_today' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM execution_history
        WHERE files_failed > 0
        AND date(started_at) = date('now')
    ")->fetchColumn(),

    'total_uploaded' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM uploaded_files
    ")->fetchColumn(),

    'total_jobs' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM jobs
    ")->fetchColumn(),

    'total_connections' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM connections
    ")->fetchColumn()

];

echo json_encode([
    'success' => true,
    'data' => $data
]);