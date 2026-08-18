<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

$uploadManager = $app->uploadManager();

echo get_class($uploadManager) . PHP_EOL;