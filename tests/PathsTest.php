<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Paths;

echo "ROOT      : " . Paths::root() . PHP_EOL;
echo "STORAGE   : " . Paths::storage() . PHP_EOL;
echo "DATABASE  : " . Paths::database() . PHP_EOL;
echo "LOGS      : " . Paths::logs() . PHP_EOL;
echo "TEMP      : " . Paths::temp() . PHP_EOL;
echo "CONFIG    : " . Paths::config() . PHP_EOL;