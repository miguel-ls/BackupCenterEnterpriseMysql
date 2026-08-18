<?php

namespace BackupCenter\Core;

use BackupCenter\Repositories\SessionRepository;

class Auth
{
    public static function token(): ?string
    {
        return ApiController::bearerToken();
    }

    public static function check(): bool
    {
        $token = self::token();

        if (!$token) {
            return false;
        }

        $database = new Database(
            Paths::database() . '/backupcenter.db'
        );

        $session = (new SessionRepository($database))->validate($token);

        return $session !== null;
    }

    public static function require(): void
    {
        if (!self::check()) {

            ApiResponse::error(
                "No autenticado.",
                401
            );

        }
    }
}