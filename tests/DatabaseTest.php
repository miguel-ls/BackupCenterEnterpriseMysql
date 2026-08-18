<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Database;

$db = new Database(
    __DIR__ . '/../database/backupcenter.db'
);

$db->getConnection()->exec("
CREATE TABLE IF NOT EXISTS uploaded_files
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    filesize INTEGER NOT NULL,
    sha256 TEXT NOT NULL,
    uploaded_at TEXT NOT NULL
);
");

echo "Base de datos creada correctamente." . PHP_EOL;