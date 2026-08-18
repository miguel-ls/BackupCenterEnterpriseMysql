<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\HostedAgent;
use BackupCenter\Core\Scheduler;

$host = new HostedAgent(
    new Application(),
    new Scheduler()
);

$host->executeOnce();

echo PHP_EOL;
echo "HostedAgent ejecutado correctamente." . PHP_EOL;