<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

Auth::require();

ApiController::method("POST");

$data = ApiController::body();

$username = trim($data["username"] ?? "");

$currentPassword = $data["current_password"] ?? "";

$newPassword = $data["new_password"] ?? "";

$confirmPassword = $data["confirm_password"] ?? "";

if (

    $username === "" ||

    $currentPassword === "" ||

    $newPassword === "" ||

    $confirmPassword === ""

) {

    ApiResponse::error(
        "Debe completar todos los campos."
    );

}

if ($newPassword !== $confirmPassword) {

    ApiResponse::error(
        "La nueva contraseña no coincide."
    );

}

if (strlen($newPassword) < 8) {

    ApiResponse::error(
        "La contraseña debe tener al menos 8 caracteres."
    );

}

$stmt = $pdo->prepare("

SELECT

    id,

    username,

    password

FROM users

WHERE username=?

");

$stmt->execute([

    $username

]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    ApiResponse::error(
        "Usuario no encontrado."
    );

}

if (!password_verify($currentPassword, $user["password"])) {

    Audit::error(

        "SECURITY",

        "CHANGE_PASSWORD",

        "Contraseña actual incorrecta",

        $username

    );

    ApiResponse::error(
        "La contraseña actual es incorrecta."
    );

}

if (password_verify($newPassword, $user["password"])) {

    ApiResponse::error(
        "La nueva contraseña debe ser diferente a la actual."
    );

}

$hash = password_hash(

    $newPassword,

    PASSWORD_DEFAULT

);

$stmt = $pdo->prepare("

UPDATE users

SET password=?

WHERE id=?

");

$stmt->execute([

    $hash,

    $user["id"]

]);

Audit::info(

    "SECURITY",

    "CHANGE_PASSWORD",

    "Contraseña modificada correctamente",

    $username

);

ApiResponse::success(

    null,

    "Contraseña actualizada correctamente."

);