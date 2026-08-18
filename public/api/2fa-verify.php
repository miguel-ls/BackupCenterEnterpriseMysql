<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Repositories\SessionRepository;
use PragmaRX\Google2FA\Google2FA;

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

ApiController::method("POST");

if(session_status()===PHP_SESSION_NONE){
    session_start();
}

$data=ApiController::body();

$challenge=$data["challenge"] ?? "";

$code=trim($data["code"] ?? "");

if($challenge=="" || $code==""){

    ApiResponse::error(
        "Datos incompletos."
    );

}

if(empty($_SESSION["2fa"])){

    ApiResponse::error(
        "La sesión de autenticación expiró."
    );

}

$temp=$_SESSION["2fa"];

if($temp["challenge"]!==$challenge){

    ApiResponse::error(
        "Challenge inválido."
    );

}

if($temp["expires"]<time()){

    unset($_SESSION["2fa"]);

    ApiResponse::error(
        "La autenticación expiró."
    );

}

$stmt=$pdo->prepare("

SELECT

    id,

    username,

    fullname,

    role,

    enabled,

    twofactor_secret

FROM users

WHERE id=?

");

$stmt->execute([

    $temp["user_id"]

]);

$user=$stmt->fetch(PDO::FETCH_ASSOC);

if(!$user){

    unset($_SESSION["2fa"]);

    ApiResponse::error(
        "Usuario no encontrado."
    );

}

$google2fa=new Google2FA();

$valid=$google2fa->verifyKey(

    $user["twofactor_secret"],

    $code

);

if(!$valid){

    Audit::error(

        "2FA",

        "FAILED",

        "Código incorrecto",

        $user["username"]

    );

    ApiResponse::error(
        "Código incorrecto."
    );

}

$pdo->prepare("

UPDATE users

SET last_login=datetime('now')

WHERE id=?

")->execute([

    $user["id"]

]);

Audit::info(

    "2FA",

    "SUCCESS",

    "Segundo factor correcto",

    $user["username"]

);

unset($_SESSION["2fa"]);

unset($user["twofactor_secret"]);

$token = bin2hex(random_bytes(32));

$sessionRepository = new SessionRepository(
    $app->database()
);

$sessionRepository->create(
    (int)$user["id"],
    $token,
    $_SERVER["HTTP_USER_AGENT"] ?? "Unknown",
    $_SERVER["REMOTE_ADDR"] ?? "LOCAL",
    date(
        "Y-m-d H:i:s",
        strtotime("+30 days")
    )
);

$user["token"] = $token;

ApiResponse::success($user);
