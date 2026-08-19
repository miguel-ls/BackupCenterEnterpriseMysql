<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Repositories\UploadedFileRepository;

$database = new Database();

$repository = new UploadedFileRepository($database);

$repository->initialize();


echo "Repositorio inicializado correctamente." . PHP_EOL;