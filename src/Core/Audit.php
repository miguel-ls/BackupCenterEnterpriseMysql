<?php

namespace BackupCenter\Core;

use BackupCenter\Repositories\AuditRepository;

class Audit
{
    private static ?AuditRepository $repository = null;

    public static function initialize(
        AuditRepository $repository
    ): void
    {
        self::$repository = $repository;
    }

    public static function info(
        string $module,
        string $action,
        string $description = '',
        ?string $username = null
    ): void
    {
        if (!self::$repository) {
            return;
        }

        self::$repository->add(
            $username,
            $module,
            $action,
            $description,
            true
        );
    }

    public static function error(
        string $module,
        string $action,
        string $description = '',
        ?string $username = null
    ): void
    {
        if (!self::$repository) {
            return;
        }

        self::$repository->add(
            $username,
            $module,
            $action,
            $description,
            false
        );
    }
}