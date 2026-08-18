<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Models\BackupFile;

$file = new BackupFile(
    'UNIMUNDO.zip',
    'B:\\Backup ERP\\UNIMUNDO.zip',
    1048576,
    time(),
    'zip'
);

echo "Nombre     : " . $file->getName() . PHP_EOL;
echo "Ruta       : " . $file->getPath() . PHP_EOL;
echo "Tamaño     : " . $file->getSize() . PHP_EOL;
echo "Extensión  : " . $file->getExtension() . PHP_EOL;