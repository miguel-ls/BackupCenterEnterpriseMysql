<?php

require_once __DIR__ . '/vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Repositories\ConnectionRepository;
use BackupCenter\Repositories\JobRepository;

echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo " Development Seeder" . PHP_EOL;
echo "==========================================" . PHP_EOL;

$db = new Database(
    Paths::database() . '/backupcenter.db'
);

$connections = new ConnectionRepository($db);
$jobs = new JobRepository($db);

$pdo = $db->getConnection();

/*
|--------------------------------------------------------------------------
| Limpiar datos
|--------------------------------------------------------------------------
*/

$pdo->exec("DELETE FROM uploaded_files;");
$pdo->exec("DELETE FROM execution_history;");
$pdo->exec("DELETE FROM jobs;");
$pdo->exec("DELETE FROM connections;");

/*
|--------------------------------------------------------------------------
| Conexión
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
INSERT INTO connections
(
    name,
    host,
    port,
    username,
    password,
    hostkey,
    remote_path
)
VALUES
(
    ?,?,?,?,?,?,?
)
");

$stmt->execute([
    "TrueNAS Principal",
    "38.253.171.145",
    30126,
    "unimarket",
    "TU_PASSWORD",
    "TU_HOSTKEY",
    "/backups/unimarket"
]);

$connectionId = $pdo->lastInsertId();

/*
|--------------------------------------------------------------------------
| Trabajo
|--------------------------------------------------------------------------clear

*/

$stmt = $pdo->prepare("
INSERT INTO jobs
(
    connection_id,
    name,
    source,
    destination,
    schedule,
    enabled,
    last_status
)
VALUES
(
    ?,?,?,?,?,1,'Pendiente'
)
");

$stmt->execute([
    $connectionId,
    "ERP SQL",
    "D:\\Backup ERP",
    "/backups/unimarket",
    "0 */6 * * *"
]);

echo PHP_EOL;
echo "Seeder ejecutado correctamente." . PHP_EOL;