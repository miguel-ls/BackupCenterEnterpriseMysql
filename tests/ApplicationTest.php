<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

echo $app->config()->get('client.name') . PHP_EOL;

$app->logger()->info("Application funcionando correctamente.");

echo "Logger OK" . PHP_EOL;