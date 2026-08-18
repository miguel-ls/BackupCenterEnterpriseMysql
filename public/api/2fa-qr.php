<?php

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Auth;
use PragmaRX\Google2FA\Google2FA;

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';


ApiController::boot();

Auth::require();

ApiController::method("POST");

$data = ApiController::body();

$id = (int)($data["id"] ?? 0);

if ($id <= 0) {

    ApiResponse::error(
        "Usuario inválido."
    );

}

$stmt = $pdo->prepare("
SELECT
    id,
    username,
    fullname,
    twofactor_secret
FROM users
WHERE id=?
");

$stmt->execute([
    $id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    ApiResponse::error(
        "Usuario no encontrado."
    );

}

$google2fa = new Google2FA();

$secret = $user["twofactor_secret"];

if (empty($secret)) {

    $secret = $google2fa->generateSecretKey();

    $pdo->prepare("
        UPDATE users
        SET twofactor_secret=?
        WHERE id=?
    ")->execute([

        $secret,

        $id

    ]);

}

$uri = $google2fa->getQRCodeUrl(

    "Backup Center Enterprise",

    $user["username"],

    $secret

);

$renderer = new ImageRenderer(
    new RendererStyle(250),
    new SvgImageBackEnd()
);

$writer = new Writer($renderer);

$svg = $writer->writeString($uri);


ApiResponse::success([

    "secret" => $secret,

    "uri" => $uri,

    "qr" => "data:image/svg+xml;base64," . base64_encode($svg),

    "manual" => true

]);