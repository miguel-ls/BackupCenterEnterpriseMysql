<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\HostedAgent;
use BackupCenter\Core\Scheduler;

$host = new HostedAgent(
    new Application(),
    new Scheduler()
);

echo get_class($host) . PHP_EOL;