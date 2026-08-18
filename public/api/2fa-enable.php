<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use PragmaRX\Google2FA\Google2FA;

require_once __DIR__ . '/cors.php';
require_once __DIR__.'/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method("POST");

$data=ApiController::body();

$id=(int)($data["id"]??0);

$code=trim($data["code"]??"");

if($id<=0 || $code==""){

    ApiResponse::error(
        "Datos incompletos."
    );

}

$stmt=$pdo->prepare("

SELECT

id,

username,

twofactor_secret

FROM users

WHERE id=?

");

$stmt->execute([

$id

]);

$user=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){

    ApiResponse::error(
        "Usuario no encontrado."
    );

}

if(empty($user["twofactor_secret"])){

    ApiResponse::error(
        "Primero debe generar el código QR."
    );

}

$google2fa=new Google2FA();

if(

!$google2fa->verifyKey(

$user["twofactor_secret"],

$code

)

){

    Audit::error(

        "USERS",

        "2FA_ENABLE",

        "Código incorrecto",

        $user["username"]

    );

    ApiResponse::error(
        "Código incorrecto."
    );

}

$pdo->prepare("

UPDATE users

SET twofactor_enabled=1

WHERE id=?

")->execute([

$id

]);

Audit::info(

    "USERS",

    "2FA_ENABLE",

    "2FA activado para ".$user["username"],

    "admin"

);

ApiResponse::success(

null,

"Autenticación en dos pasos activada."

);