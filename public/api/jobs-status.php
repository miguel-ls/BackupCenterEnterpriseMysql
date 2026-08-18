<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Auth;
use BackupCenter\Services\CronService;

Auth::require();

$app = new Application();

$db = $app->database()->getConnection();

$cron = new CronService();

$rows = $db->query("
SELECT
    id,
    name,
    schedule,
    enabled,
    running,
    last_run,
    last_status
FROM jobs
ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as &$row) {

    $row["next_run"] = $cron->nextRun($row["schedule"]);

    $row["remaining"] = $cron->remaining($row["schedule"]);

    $row["due"] = $cron->isDue($row["schedule"]);

}

echo json_encode([
    "success" => true,
    "data" => $rows
]);