<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json');

// Leer JSON
$data = json_decode(file_get_contents("php://input"), true);

$token = $data['InstallToken'] ?? '';

if (!$token) {
    echo json_encode([
        "valid" => false,
        "message" => "Token requerido"
    ]);
    exit;
}

try {

    // 👉 USAMOS TU CONEXIÓN EXISTENTE
    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("
        SELECT id, client_id, installed
        FROM connections
        WHERE install_token = ?
        LIMIT 1
    ");

    $stmt->execute([$token]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {

        if ((int)$row['installed'] === 1) {
            echo json_encode([
                "valid" => false,
                "message" => "Este token ya fue utilizado"
            ]);
            exit;
        }

        echo json_encode([
            "valid" => true,
            "connection_id" => $row['id'],
            "client_id" => $row['client_id']
        ]);

    } else {
        echo json_encode([
            "valid" => false,
            "message" => "Token inválido"
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "valid" => false,
        "message" => "Error interno"
    ]);
}