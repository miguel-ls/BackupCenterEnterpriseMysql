<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Auth;
use BackupCenter\Services\CronService;

Auth::require();

$app = new Application();

$db = $app->database()->getConnection();

$jobs = (int)$db->query("
    SELECT COUNT(*) FROM jobs
")->fetchColumn();

$connections = (int)$db->query("
    SELECT COUNT(*) FROM connections
")->fetchColumn();

$lastBackup = $db->query("
    SELECT started_at
    FROM execution_history
    ORDER BY started_at DESC
    LIMIT 1
")->fetchColumn();

$cron = new CronService();

$nextBackup = null;

$schedules = $db->query("
    SELECT schedule
    FROM jobs
    WHERE enabled=1
")->fetchAll(PDO::FETCH_COLUMN);

foreach ($schedules as $schedule) {

    $nextRun = $cron->nextRun($schedule);

    if ($nextRun !== null && ($nextBackup === null || $nextRun < $nextBackup)) {
        $nextBackup = $nextRun;
    }

}

echo json_encode([

    "success"=>true,

    "data"=>[

        "clients"=>$jobs,

        "repositories"=>$connections,

        "last_backup"=>$lastBackup ?? "-",

        "next_backup"=>$nextBackup ?? "-",

        "health"=>98

    ]

]);
