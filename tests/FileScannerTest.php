<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\FileScanner;

try {

    $scanner = new FileScanner();

    $files = $scanner->scan(__DIR__ . '/data');

    echo "Archivos encontrados: " . count($files) . PHP_EOL . PHP_EOL;

    foreach ($files as $file) {

        echo "Nombre     : " . $file->getName() . PHP_EOL;
        echo "Extensión  : " . $file->getExtension() . PHP_EOL;
        echo "Tamaño     : " . $file->getSize() . " bytes" . PHP_EOL;
        echo "-----------------------------------" . PHP_EOL;

    }

} catch (Exception $e) {

    echo $e->getMessage();

}