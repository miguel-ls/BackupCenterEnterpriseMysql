<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Services\JobLogService;

$app = new Application();

$app->database()->getConnection()->exec("DELETE FROM system_logs");

$service = new JobLogService();
$service->logUserEvent('CREATE', 'adminuser', 10, 'admin');
$service->logSettingsEvent('UPDATE', 'Configuración modificada', 1, 'admin');

$count = (int)$app
    ->database()
    ->getConnection()
    ->query("SELECT COUNT(*) FROM system_logs WHERE (module = 'USERS' AND action = 'CREATE') OR (module = 'SETTINGS' AND action = 'UPDATE')")
    ->fetchColumn();

if ($count < 2) {
    throw new RuntimeException('No se registraron los eventos de Usuarios o Configuración en system_logs');
}

echo "User/settings log test OK" . PHP_EOL;
