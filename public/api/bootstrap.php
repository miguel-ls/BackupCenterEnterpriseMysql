<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;

/*
|--------------------------------------------------------------------------
| Manejo global de errores
|--------------------------------------------------------------------------
*/

set_exception_handler(function ($e) {
    http_response_code(500);
    header('Content-Type: application/json');

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
});

/*
|--------------------------------------------------------------------------
| Crear aplicación
|--------------------------------------------------------------------------
*/

$app = new Application();

/*
|--------------------------------------------------------------------------
| Base de datos
|--------------------------------------------------------------------------
*/

$db = $app->database();
$pdo = $db->getConnection();