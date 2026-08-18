<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Repositories\ConnectionRepository;

$db = new Database(Paths::database() . '/backupcenter.db');
$repository = new ConnectionRepository($db);

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$connectionId = (int)($data['connectionId'] ?? 0);
$installToken = trim((string)($data['installToken'] ?? ''));

if ($connectionId <= 0 || $installToken === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos de autenticación incompletos'
    ]);
    exit;
}

$connection = $repository->get($connectionId);

if (!$connection) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Conexión no encontrada'
    ]);
    exit;
}

if (($connection['install_token'] ?? '') !== $installToken) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Token de instalación inválido'
    ]);
    exit;
}

$repository->update(
    $connectionId,
    (int)($connection['client_id'] ?? 0),
    $connection['name'],
    $connection['host'],
    (int)($connection['port'] ?? 22),
    $connection['username'],
    $connection['password'],
    $connection['hostkey'] ?? '',
    $connection['protocol'] ?? 'SFTP',
    $connection['remote_path'] ?? ''
);

echo json_encode([
    'success' => true,
    'message' => 'Autenticación correcta',
    'connectionId' => $connectionId,
    'connectionName' => $connection['name']
]);
