<?php

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// 🔥 LOG DEBUG (opcional)
file_put_contents(__DIR__ . '/debug_mark.log', print_r($data, true), FILE_APPEND);

$token = trim($data['InstallToken'] ?? '');

if (!$token) {
    echo json_encode([
        "success" => false,
        "message" => "Token vacío"
    ]);
    exit;
}

try {
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("
        UPDATE connections
        SET installed = 1
        WHERE install_token = ?
    ");

    $stmt->execute([$token]);

    echo json_encode([
        "success" => true,
        "rows_affected" => $stmt->rowCount()
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}