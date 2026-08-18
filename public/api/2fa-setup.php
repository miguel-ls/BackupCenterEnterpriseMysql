<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use PragmaRX\Google2FA\Google2FA;

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method("POST");

$data = ApiController::body();

$id = (int)($data["id"] ?? 0);

if($id<=0){

    ApiResponse::error(
        "Usuario inválido."
    );

}

$stmt=$pdo->prepare("

SELECT

    id,

    username,

    fullname

FROM users

WHERE id=?

");

$stmt->execute([$id]);

$user=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){

    ApiResponse::error(
        "Usuario no encontrado."
    );

}

$google2fa = new Google2FA();

$secret = $google2fa->generateSecretKey();

$pdo->prepare("

UPDATE users

SET

    twofactor_secret=?,

    twofactor_enabled=1

WHERE id=?

")->execute([

    $secret,

    $id

]);

$appName="Backup Center Enterprise";

$inlineUrl=$google2fa->getQRCodeUrl(

    $appName,

    $user["username"],

    $secret

);

Audit::info(

    "USERS",

    "ENABLE_2FA",

    "2FA habilitado para ".$user["username"],

    "admin"

);

ApiResponse::success([

    "secret"=>$secret,

    "otpAuth"=>$inlineUrl

]);