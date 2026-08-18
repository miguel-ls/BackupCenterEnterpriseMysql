<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Services\CronService;

$cron = new CronService();

$expression = "0 */2 * * *";

echo "Cron: {$expression}\n\n";

echo "Próxima ejecución:\n";
echo $cron->nextRun($expression) . PHP_EOL . PHP_EOL;

echo "Última ejecución:\n";
echo $cron->previousRun($expression) . PHP_EOL . PHP_EOL;

echo "Tiempo restante:\n";
echo $cron->remaining($expression) . PHP_EOL . PHP_EOL;

echo "¿Debe ejecutarse?: ";
echo $cron->isDue($expression) ? "SI" : "NO";