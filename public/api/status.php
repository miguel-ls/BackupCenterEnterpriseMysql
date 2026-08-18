<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Auth;

Auth::require();

$status = [

    'jobs' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM jobs
    ")->fetchColumn(),

    'connections' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM connections
    ")->fetchColumn(),

    'uploaded' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM uploaded_files
    ")->fetchColumn(),

    'executions' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM execution_history
    ")->fetchColumn(),

    'queue' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM job_queue
        WHERE status='Pending'
    ")->fetchColumn(),

    'running' => (int)$pdo->query("
        SELECT COUNT(*)
        FROM job_queue
        WHERE status='Running'
    ")->fetchColumn()

];

ApiResponse::success($status);