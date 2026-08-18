<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Models\ExecutionSummary;

$app = new Application();

$summary = new ExecutionSummary();

sleep(1);

$summary->found = 10;
$summary->uploaded = 8;
$summary->skipped = 1;
$summary->errors = 1;

$summary->finish();

$app->executionHistoryRepository()->save(
    $summary,
    "Unimarket"
);

echo "Historial guardado correctamente." . PHP_EOL;