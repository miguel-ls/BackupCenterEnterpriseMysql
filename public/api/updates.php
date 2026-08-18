<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Auth;

Auth::require();

$versionFile = __DIR__ . '/../../resources/updates/version.json';

if (!file_exists($versionFile)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró la metadata de actualización'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => json_decode(file_get_contents($versionFile), true)
]);
