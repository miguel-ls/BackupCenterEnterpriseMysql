<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\JobQueueRepository;
use BackupCenter\Repositories\ExecutionHistoryRepository;
use BackupCenter\Services\WindowsServiceMonitor;

Auth::require();

$app = new Application();

$queue = new JobQueueRepository(
    $app->database()
);

$history = new ExecutionHistoryRepository(
    $app->database()
);

$queue->initialize();

$monitor = new WindowsServiceMonitor();

$last = $history->latest(1);

$stats = $history->statistics();

$scheduler = $monitor->getServiceInfo(
    "BackupCenterScheduler"
);

$worker = $monitor->getServiceInfo(
    "BackupCenterWorker"
);

echo json_encode([

    "success"=>true,

    "data"=>[

        "scheduler"=>$scheduler,

        "worker"=>$worker,

        "queue"=>$queue->countPending(),

        "running"=>$queue->countRunning(),

        "failed"=>$queue->countFailed(),

        "completed"=>$queue->countCompleted(),

        "executions"=>(int)($stats["total_runs"] ?? 0),

        "last_execution"=>$last[0]["started_at"] ?? "-"

    ]

]);