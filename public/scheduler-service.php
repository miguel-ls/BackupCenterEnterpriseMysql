<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo " Backup Center Scheduler Service" . PHP_EOL;
echo "==========================================" . PHP_EOL;

$app
    ->schedulerLoop()
    ->run(60);