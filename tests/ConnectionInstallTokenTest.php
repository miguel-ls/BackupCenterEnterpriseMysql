<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Repositories\ConnectionRepository;

$dbPath = sys_get_temp_dir() . '/backupcenter-connection-install-token-test.sqlite';

if (file_exists($dbPath)) {
    unlink($dbPath);
}

$database = new Database($dbPath);
$repository = new ConnectionRepository($database);

$repository->initialize();

$id = $repository->create(
    1,
    'Test Connection',
    'example.com',
    22,
    'root',
    'secret',
    '',
    'SFTP',
    '/backup'
);

$connection = $repository->get($id);

if (!isset($connection['install_token']) || empty($connection['install_token'])) {
    throw new RuntimeException('install_token was not generated');
}

if (!preg_match('/^[a-z0-9]{16,}$/i', $connection['install_token'])) {
    throw new RuntimeException('install_token format is invalid');
}

echo 'Connection install token test passed' . PHP_EOL;
