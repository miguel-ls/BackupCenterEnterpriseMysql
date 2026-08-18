<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Repositories\SessionRepository;

require_once __DIR__ . '/cors.php';
require_once __DIR__.'/bootstrap.php';

ApiController::boot();

ApiController::method("POST");

$token=ApiController::bearerToken();

if(!$token){

    ApiResponse::error(
        "Token inválido."
    );

}

$repository=new SessionRepository(

    $app->database()

);

$repository->logout($token);

ApiResponse::success(

    null,

    "Sesión finalizada."

);