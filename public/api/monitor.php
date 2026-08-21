<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Auth;
use BackupCenter\Services\MonitorLogService;

date_default_timezone_set('America/Lima');

Auth::require();

$today = date('Y-m-d');
$file = MonitorLogService::fileForDate($today);

$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : null;
$maxInitialLines = 1000;

if (!file_exists($file)) {
    echo json_encode([
        'success' => true,
        'date' => $today,
        'lines' => [],
        'next_offset' => 0
    ]);
    exit;
}

$size = filesize($file);

// Carga inicial (sin offset): solo las últimas N líneas para no sobrecargar el navegador.
if ($offset === null) {

    $content = file_get_contents($file);
    $allLines = explode(PHP_EOL, trim($content, PHP_EOL));

    if (count($allLines) > $maxInitialLines) {
        $allLines = array_slice($allLines, -$maxInitialLines);
    }

    echo json_encode([
        'success' => true,
        'date' => $today,
        'lines' => $allLines,
        'next_offset' => $size
    ]);
    exit;
}

// El archivo del día cambió (rollover de medianoche): el frontend debe resetear su offset a 0.
if ($offset > $size) {
    echo json_encode([
        'success' => true,
        'date' => $today,
        'lines' => [],
        'next_offset' => 0
    ]);
    exit;
}

// Sin datos nuevos desde la última lectura.
if ($offset === $size) {
    echo json_encode([
        'success' => true,
        'date' => $today,
        'lines' => [],
        'next_offset' => $size
    ]);
    exit;
}

$handle = fopen($file, 'r');
fseek($handle, $offset);
$new = fread($handle, $size - $offset);
fclose($handle);

$lines = $new !== '' ? explode(PHP_EOL, trim($new, PHP_EOL)) : [];

echo json_encode([
    'success' => true,
    'date' => $today,
    'lines' => $lines,
    'next_offset' => $size
]);
