<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo " Backup Center Scheduler" . PHP_EOL;
echo "==========================================" . PHP_EOL;

$app = new Application();

$force = in_array('--force', $argv);

$app
    ->schedulerEngine()
    ->execute($force);


        
echo PHP_EOL;
echo "Scheduler finalizado." . PHP_EOL;