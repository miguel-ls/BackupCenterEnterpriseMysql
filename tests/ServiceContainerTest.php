<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\ServiceContainer;

$container = new ServiceContainer();

echo get_class(
    $container->app()
) . PHP_EOL;