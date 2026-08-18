<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;

require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method(["GET","PUT"]);

if($_SERVER["REQUEST_METHOD"]==="GET"){

    $username=trim($_GET["username"]??"");

    if($username===""){

        ApiResponse::error(
            "Usuario inválido."
        );

    }

    $stmt=$pdo->prepare("

        SELECT

            id,

            username,

            fullname,

            role,

            enabled,

            last_login,

            created_at,

            twofactor_enabled

        FROM users

        WHERE username=?

    ");

    $stmt->execute([

        $username

    ]);

    $user=$stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){

        ApiResponse::error(
            "Usuario no encontrado."
        );

    }

    ApiResponse::success(
        $user
    );

}

$data=ApiController::body();

$id=(int)($data["id"]??0);

$fullname=trim($data["fullname"]??"");

if($id<=0){

    ApiResponse::error(
        "Usuario inválido."
    );

}

if($fullname===""){

    ApiResponse::error(
        "Debe ingresar el nombre."
    );

}

$stmt=$pdo->prepare("

UPDATE users

SET

fullname=?

WHERE id=?

");

$stmt->execute([

    $fullname,

    $id

]);

Audit::info(

    "PROFILE",

    "UPDATE",

    "Perfil actualizado",

    $data["username"]

);

$stmt=$pdo->prepare("

SELECT

id,

username,

fullname,

role,

enabled,

last_login,

created_at,

twofactor_enabled

FROM users

WHERE id=?

");

$stmt->execute([

    $id

]);

$user=$stmt->fetch(PDO::FETCH_ASSOC);

ApiResponse::success(

    $user,

    "Perfil actualizado correctamente."

);