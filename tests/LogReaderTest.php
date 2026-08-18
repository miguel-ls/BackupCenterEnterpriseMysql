<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\LogReader;
use BackupCenter\Core\Paths;

$reader = new LogReader(Paths::logs());

$lines = $reader->latest();

if (empty($lines)) {

    echo "No existen registros." . PHP_EOL;
    exit(0);
}

foreach ($lines as $line) {
    echo $line . PHP_EOL;
}