<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\RecoveryCodeRepository;

require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method(["GET","POST"]);

$repository = new RecoveryCodeRepository(
    $app->database()
);

if($_SERVER["REQUEST_METHOD"]==="GET"){

    $userId=(int)($_GET["user_id"]??0);

    if($userId<=0){

        ApiResponse::error(
            "Usuario inválido."
        );

    }

    ApiResponse::success([]);

}

$data=ApiController::body();

$userId=(int)($data["user_id"]??0);

if($userId<=0){

    ApiResponse::error(
        "Usuario inválido."
    );

}

$codes=$repository->regenerate($userId);

ApiResponse::success(

    $codes,

    "Códigos generados correctamente."

);