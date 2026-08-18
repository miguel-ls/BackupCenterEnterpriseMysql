<?php

require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Core\Auth;

header('Content-Type: application/json');

try {

    Auth::require();

    $db = new Database(
        Paths::database() . '/backupcenter.db'
    );

    $pdo = $db->getConnection();

    $settings = $pdo->query("
        SELECT *
        FROM settings
        WHERE id=1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        throw new Exception("No existe configuración.");
    }

    if (empty($settings['sftpgo_host'])) {
        throw new Exception("Debe configurar el Host de SFTPGo.");
    }

    if (empty($settings['sftpgo_username'])) {
        throw new Exception("Debe configurar el Usuario de SFTPGo.");
    }

    if (empty($settings['sftpgo_password'])) {
        throw new Exception("Debe configurar la Contraseña de SFTPGo.");
    }

    $baseUrl =
        $settings["sftpgo_protocol"] .
        "://" .
        $settings["sftpgo_host"] .
        ":" .
        $settings["sftpgo_port"];

    /*
    |--------------------------------------------------------------------------
    | Paso 1: Obtener JWT
    |--------------------------------------------------------------------------
    */

    $tokenUrl = $baseUrl . "/api/v2/token";

    $ch = curl_init($tokenUrl);

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPGET => true,

        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: Basic " . base64_encode(
                $settings["sftpgo_username"] .
                ":" .
                $settings["sftpgo_password"]
            )
        ]

    ]);

    $tokenResponse = curl_exec($ch);

    $tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $tokenError = curl_error($ch);

    curl_close($ch);

    if ($tokenError) {
        throw new Exception($tokenError);
    }

    if ($tokenHttpCode != 200) {

        echo json_encode([
            "success" => false,
            "message" => "No fue posible obtener el token.",
            "response" => json_decode($tokenResponse, true)
        ]);

        exit;

    }

    $tokenData = json_decode($tokenResponse, true);

    if (empty($tokenData["access_token"])) {
        throw new Exception("SFTPGo no devolvió un access_token.");
    }

    $accessToken = $tokenData["access_token"];

    /*
    |--------------------------------------------------------------------------
    | Paso 2: Consultar versión
    |--------------------------------------------------------------------------
    */

    $versionUrl = $baseUrl . "/api/v2/version";

    $ch = curl_init($versionUrl);

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,

        CURLOPT_HTTPHEADER => [

            "Accept: application/json",

            "Authorization: Bearer " . $accessToken

        ]

    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $error = curl_error($ch);

    curl_close($ch);

    if ($error) {
        throw new Exception($error);
    }

    if ($httpCode >= 200 && $httpCode < 300) {

        echo json_encode([

            "success" => true,
            "message" => "Conexión correcta con SFTPGo.",
            "data" => json_decode($response, true)

        ]);

    } else {

        echo json_encode([

            "success" => false,
            "message" => "HTTP " . $httpCode,
            "response" => json_decode($response, true)

        ]);

    }

} catch (Throwable $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}