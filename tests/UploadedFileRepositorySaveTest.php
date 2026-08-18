<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Repositories\UploadedFileRepository;

$database = new Database(
    __DIR__ . '/../database/backupcenter.db'
);

$repository = new UploadedFileRepository($database);

$repository->initialize();

$sha256 = hash('sha256', 'archivo_prueba');

echo "Existe antes: ";
var_dump($repository->exists($sha256));

$repository->save(
    'archivo_prueba.zip',
    1024,
    $sha256
);

echo "Existe después: ";
var_dump($repository->exists($sha256));