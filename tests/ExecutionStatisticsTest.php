<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

$stats = $app
    ->executionHistoryRepository()
    ->statistics();

echo "Total           : {$stats['total_runs']}" . PHP_EOL;
echo "OK              : {$stats['success_runs']}" . PHP_EOL;
echo "ERROR           : {$stats['error_runs']}" . PHP_EOL;
echo "Subidos         : {$stats['uploaded']}" . PHP_EOL;
echo "Omitidos        : {$stats['skipped']}" . PHP_EOL;
echo "Promedio        : {$stats['average_duration']} s" . PHP_EOL;