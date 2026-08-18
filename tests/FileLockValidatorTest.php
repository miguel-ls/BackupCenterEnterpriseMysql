<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\FileLockValidator;

$file = __DIR__ . '/data/prueba.txt';

$validator = new FileLockValidator();

if ($validator->isLocked($file)) {
    echo "Archivo bloqueado" . PHP_EOL;
} else {
    echo "Archivo disponible" . PHP_EOL;
}