<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Audit;
use BackupCenter\Repositories\SessionRepository;

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

ApiController::boot();

ApiController::method('POST');

$data = ApiController::body();

$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if ($username === '' || $password === '') {

    Audit::error(
        'LOGIN',
        'LOGIN_FAILED',
        'Usuario o contraseña vacíos',
        $username
    );

    ApiResponse::error(
        'Debe ingresar usuario y contraseña.',
        400
    );
}

$stmt = $pdo->prepare("
SELECT
    id,
    username,
    password,
    fullname,
    role,
    enabled,
    twofactor_enabled
FROM users
WHERE username=?
");

$stmt->execute([
    $username
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    Audit::error(
        'LOGIN',
        'LOGIN_FAILED',
        'Usuario inexistente',
        $username
    );

    ApiResponse::error(
        'Usuario o contraseña incorrectos.',
        401
    );
}

if ((int)$user['enabled'] !== 1) {

    Audit::error(
        'LOGIN',
        'LOGIN_BLOCKED',
        'Usuario deshabilitado',
        $username
    );

    ApiResponse::error(
        'El usuario está deshabilitado.',
        403
    );
}

if (!password_verify($password, $user['password'])) {

    Audit::error(
        'LOGIN',
        'LOGIN_FAILED',
        'Contraseña incorrecta',
        $username
    );

    ApiResponse::error(
        'Usuario o contraseña incorrectos.',
        401
    );
}

/*
|--------------------------------------------------------------------------
| Usuario con 2FA
|--------------------------------------------------------------------------
*/

if ((int)$user["twofactor_enabled"] === 1) {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $challenge = bin2hex(random_bytes(32));

    $_SESSION["2fa"] = [

        "challenge" => $challenge,

        "user_id" => $user["id"],

        "username" => $user["username"],

        "expires" => time() + 300

    ];

    Audit::info(

        "LOGIN",

        "2FA_REQUIRED",

        "Segundo factor requerido",

        $username

    );

    ApiResponse::success([

        "requires2FA" => true,

        "challenge" => $challenge,

        "username" => $username

    ]);

}

/*
|--------------------------------------------------------------------------
| Login correcto
|--------------------------------------------------------------------------
*/

$pdo->prepare("
UPDATE users
SET last_login=datetime('now')
WHERE id=?
")->execute([
    $user["id"]
]);

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

Audit::info(

    "LOGIN",

    "LOGIN_OK",

    "Inicio de sesión correcto",

    $username

);

unset($user["password"]);

$user["token"] = $token;

ApiResponse::success($user);