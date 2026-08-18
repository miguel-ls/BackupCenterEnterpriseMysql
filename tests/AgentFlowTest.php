<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\ConfigurationManager;
use BackupCenter\Core\FileScanner;
use BackupCenter\Core\FileValidator;

$config = new ConfigurationManager(
    __DIR__ . '/../resources/config/config.json'
);

$scanner = new FileScanner();

$validator = new FileValidator();

$files = $scanner->scan(__DIR__ . '/data');

echo "=== ARCHIVOS LISTOS ===" . PHP_EOL . PHP_EOL;

foreach ($files as $file) {

    if (!$validator->validate($file)) {
        continue;
    }

    echo $file->getName() . PHP_EOL;
}