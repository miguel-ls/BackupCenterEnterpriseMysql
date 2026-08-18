<?php

require_once __DIR__ . '/vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Repositories\UploadedFileRepository;
use BackupCenter\Repositories\JobRepository;
use BackupCenter\Repositories\ConnectionRepository;
use BackupCenter\Repositories\NotificationRepository;

echo PHP_EOL;
echo "============================================" . PHP_EOL;
echo " Backup Center Enterprise Installer" . PHP_EOL;
echo "============================================" . PHP_EOL;
echo PHP_EOL;

echo "[1/5] Verificando entorno..." . PHP_EOL;

$errors = false;

// PHP
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    echo "  [ERROR] PHP 8.2 o superior es requerido." . PHP_EOL;
    $errors = true;
} else {
    echo "  [OK] PHP " . PHP_VERSION . PHP_EOL;
}

// SQLite
if (!extension_loaded('pdo_sqlite')) {
    echo "  [ERROR] Extensión pdo_sqlite no encontrada." . PHP_EOL;
    $errors = true;
} else {
    echo "  [OK] pdo_sqlite habilitado." . PHP_EOL;
}

// WinSCP
$winScp = "C:\\Program Files (x86)\\WinSCP\\WinSCP.com";

if (!file_exists($winScp)) {
    echo "  [ERROR] WinSCP no encontrado." . PHP_EOL;
    $errors = true;
} else {
    echo "  [OK] WinSCP encontrado." . PHP_EOL;
}

if ($errors) {
    echo PHP_EOL;
    echo "La instalación no puede continuar." . PHP_EOL;
    exit(1);
}

echo PHP_EOL;
echo "[2/5] Verificando estructura..." . PHP_EOL;

$directories = [
    Paths::storage(),
    Paths::database(),
    Paths::logs(),
    Paths::temp(),
    Paths::config()
];

foreach ($directories as $directory) {

    if (!is_dir($directory)) {

        mkdir($directory, 0777, true);

        echo "  [CREADO] {$directory}" . PHP_EOL;

    } else {

        echo "  [OK] {$directory}" . PHP_EOL;

    }
}

echo PHP_EOL;
echo "[3/5] Verificando configuración..." . PHP_EOL;

$configFile = __DIR__ . '/resources/config/config.json';

if (!file_exists($configFile)) {

    echo "  [ERROR] No existe config.json" . PHP_EOL;
    exit(1);

}

$json = json_decode(file_get_contents($configFile), true);

if (json_last_error() !== JSON_ERROR_NONE) {

    echo "  [ERROR] config.json no es un JSON válido." . PHP_EOL;
    exit(1);

}

echo "  [OK] config.json válido." . PHP_EOL;

echo PHP_EOL;
echo "[4/5] Inicializando base de datos..." . PHP_EOL;

$database = new Database(
    Paths::database() . '/backupcenter.db'
);

$uploadedRepository = new UploadedFileRepository($database);
$uploadedRepository->initialize();
$uploadedRepository->initializeExecutionHistory();

$jobRepository = new JobRepository($database);
$jobRepository->initialize();

$jobQueueRepository = new \BackupCenter\Repositories\JobQueueRepository($database);
$jobQueueRepository->initialize();

/*
|--------------------------------------------------------------------------
| NUEVO
|--------------------------------------------------------------------------
*/

$connectionRepository = new ConnectionRepository($database);
$connectionRepository->initialize();

$notificationRepository = new NotificationRepository($database);
$notificationRepository->initialize();

echo "  [OK] Base de datos inicializada." . PHP_EOL;

echo PHP_EOL;
echo "[5/5] Instalación finalizada." . PHP_EOL;
echo PHP_EOL;
echo "============================================" . PHP_EOL;
echo " Instalación completada correctamente" . PHP_EOL;
echo "============================================" . PHP_EOL;
echo PHP_EOL;
echo "Ahora puedes ejecutar el servicio." . PHP_EOL;