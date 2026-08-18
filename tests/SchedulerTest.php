<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Scheduler;

$scheduler = new Scheduler();

$count = 0;

$scheduler->run(function () use (&$count) {

    $count++;

    echo "Ejecución {$count} - " . date('H:i:s') . PHP_EOL;

    if ($count >= 3) {
        exit(0);
    }

}, 2);