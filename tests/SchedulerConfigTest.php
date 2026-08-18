<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

echo "Intervalo: ";

echo $app
    ->config()
    ->get('scheduler.interval_seconds');

echo " segundos" . PHP_EOL;