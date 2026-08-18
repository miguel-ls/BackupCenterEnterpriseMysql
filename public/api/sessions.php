<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\SessionRepository;

require_once __DIR__ . '/cors.php';
require_once __DIR__.'/bootstrap.php';

ApiController::boot();

Auth::require();

$repository=new SessionRepository(
    $app->database()
);

ApiController::method(["GET","DELETE"]);

if($_SERVER["REQUEST_METHOD"]==="GET"){

    $userId=(int)($_GET["user_id"]??0);

    ApiResponse::success(

        $repository->getByUser($userId)

    );

}

$data=ApiController::body();

$repository->close(

    (int)$data["id"]

);

ApiResponse::success(

    null,

    "Sesión cerrada."

);