<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Repositories\UploadedFileRepository;

$database = new Database(
    __DIR__ . '/../storage/database/backupcenter.db'
);

$repository = new UploadedFileRepository($database);

$repository->initialize();


echo "Repositorio inicializado correctamente." . PHP_EOL;