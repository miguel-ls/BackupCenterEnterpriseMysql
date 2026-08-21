<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Auth;
use BackupCenter\Services\MonitorLogService;

date_default_timezone_set('America/Lima');

Auth::require();

$dir = MonitorLogService::directory();

function isValidMonitorFileName(string $name): bool
{
    // Solo nombres del propio patrón generado por MonitorLogService, sin rutas ni caracteres extraños.
    return (bool)preg_match('/^monitor-\d{4}-\d{2}-\d{2}\.log$/', $name);
}

$action = $_GET['action'] ?? '';

/*
|--------------------------------------------------------------------------
| DESCARGAR
|--------------------------------------------------------------------------
*/
if ($action === 'download') {

    $name = basename((string)($_GET['file'] ?? ''));

    if (!isValidMonitorFileName($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Archivo no válido']);
        exit;
    }

    $path = $dir . '/' . $name;

    if (!is_file($path)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
        exit;
    }

    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}

/*
|--------------------------------------------------------------------------
| ELIMINAR
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    $payload = json_decode(file_get_contents('php://input'), true) ?: [];
    $name = basename((string)($payload['file'] ?? ''));

    if (!isValidMonitorFileName($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Archivo no válido']);
        exit;
    }

    $path = $dir . '/' . $name;

    if (!is_file($path)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Archivo no encontrado']);
        exit;
    }

    echo json_encode(['success' => unlink($path)]);
    exit;
}

/*
|--------------------------------------------------------------------------
| LISTAR
|--------------------------------------------------------------------------
*/
$files = [];

if (is_dir($dir)) {

    foreach (glob($dir . '/monitor-*.log') as $path) {

        $files[] = [
            'name' => basename($path),
            'size' => filesize($path),
            'modified' => date('Y-m-d H:i:s', filemtime($path))
        ];

    }

}

usort($files, fn($a, $b) => strcmp($b['name'], $a['name']));

echo json_encode([
    'success' => true,
    'data' => $files
]);
