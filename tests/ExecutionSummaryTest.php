<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Models\ExecutionSummary;

$summary = new ExecutionSummary();

sleep(2);

$summary->finish();

echo "Inicio   : {$summary->startedAt}" . PHP_EOL;
echo "Fin      : {$summary->finishedAt}" . PHP_EOL;
echo "Duración : {$summary->getDuration()} s" . PHP_EOL;
echo "Estado   : " . ($summary->isSuccess() ? 'OK' : 'ERROR') . PHP_EOL;