<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Audit;
use BackupCenter\Services\JobLogService;

$app = new Application();
$db = $app->database();
$pdo = $db->getConnection();

$pdo->exec('DELETE FROM system_logs');
$pdo->exec('DELETE FROM audit_log');

Audit::info('CONNECTIONS', 'CREATE', 'Conexión test creada', 'admin');

$service = new JobLogService();
$service->logConnectionEvent('CREATE', 'TestConn', 99, 'Cliente Demo', 7, 'admin');

$auditCount = (int)$pdo->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
$systemCount = (int)$pdo->query("SELECT COUNT(*) FROM system_logs")->fetchColumn();

if ($auditCount < 1 || $systemCount < 1) {
    throw new RuntimeException('No se registraron los logs de Conexiones sin Application');
}

echo "Connection log init test OK" . PHP_EOL;
