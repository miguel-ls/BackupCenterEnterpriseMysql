<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

echo get_class($app->agent()) . PHP_EOL;