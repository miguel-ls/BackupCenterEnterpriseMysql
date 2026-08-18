<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use BackupCenter\Services\JobLogService;

require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method(['GET','POST','PUT','DELETE']);

$logService = new JobLogService();

if($_SERVER['REQUEST_METHOD']=="GET"){

    $stmt=$pdo->query("
        SELECT
            id,
            username,
            fullname,
            role,
            enabled,
            twofactor_enabled,
            last_login,
            created_at
        FROM users
        ORDER BY fullname
    ");

    ApiResponse::success(
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

}

$data=ApiController::body();

switch($_SERVER['REQUEST_METHOD']){

case "POST":

    $stmt=$pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE username=?
    ");

    $stmt->execute([
        $data["username"]
    ]);

    if($stmt->fetchColumn()>0){

        Audit::error(
            "USERS",
            "CREATE",
            "Intento de crear usuario duplicado: ".$data["username"],
            "admin"
        );

        ApiResponse::error(
            "El usuario ya existe."
        );

    }

    $stmt=$pdo->prepare("
        INSERT INTO users
        (
            username,
            password,
            fullname,
            role,
            enabled,
            twofactor_enabled,
            created_at
        )
        VALUES
        (
            ?,?,?,?,?,?,datetime('now')
        )
    ");

    $stmt->execute([

        $data["username"],

        password_hash(
            $data["password"],
            PASSWORD_DEFAULT
        ),

        $data["fullname"],

        $data["role"],

        !empty($data["enabled"])?1:0,

        !empty($data["twofactor_enabled"])?1:0

    ]);

    Audit::info(
        "USERS",
        "CREATE",
        "Usuario ".$data["username"]." creado",
        "admin"
    );

    $logService->logUserEvent(
        'CREATE',
        $data['username'],
        null,
        'admin'
    );

    ApiResponse::success(
        null,
        "Usuario creado."
    );

break;

case "PUT":

    $sql="

        UPDATE users

        SET

            fullname=?,

            role=?,

            enabled=?,

            twofactor_enabled=?

    ";

    $params=[

        $data["fullname"],

        $data["role"],

        !empty($data["enabled"])?1:0,

        !empty($data["twofactor_enabled"])?1:0

    ];

    if(!empty($data["password"])){

        $sql.=",

        password=?";

        $params[]=password_hash(

            $data["password"],

            PASSWORD_DEFAULT

        );

    }

    $sql.="

        WHERE id=?

    ";

    $params[]=$data["id"];

    $stmt=$pdo->prepare($sql);

    $stmt->execute($params);

    Audit::info(
        "USERS",
        "UPDATE",
        "Usuario ".$data["username"]." actualizado",
        "admin"
    );

    $logService->logUserEvent(
        'UPDATE',
        $data['username'] ?? '',
        (int)($data['id'] ?? 0),
        'admin'
    );

    ApiResponse::success(
        null,
        "Usuario actualizado."
    );

break;

case "DELETE":

    if((int)$data["id"]===1){

        Audit::error(
            "USERS",
            "DELETE",
            "Intento de eliminar administrador",
            "admin"
        );

        ApiResponse::error(
            "No se puede eliminar el administrador."
        );

    }

    $stmt=$pdo->prepare("
        SELECT username
        FROM users
        WHERE id=?
    ");

    $stmt->execute([
        $data["id"]
    ]);

    $username=$stmt->fetchColumn();

    $stmt=$pdo->prepare("
        DELETE
        FROM users
        WHERE id=?
    ");

    $stmt->execute([
        $data["id"]
    ]);

    Audit::info(
        "USERS",
        "DELETE",
        "Usuario ".$username." eliminado",
        "admin"
    );

    $logService->logUserEvent(
        'DELETE',
        $username ?? '',
        (int)($data['id'] ?? 0),
        'admin'
    );

    ApiResponse::success(
        null,
        "Usuario eliminado."
    );

break;

}