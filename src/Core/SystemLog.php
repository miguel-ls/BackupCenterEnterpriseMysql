<?php

namespace BackupCenter\Core;

use BackupCenter\Repositories\SystemLogRepository;

class SystemLog
{
    private static ?SystemLogRepository $repository = null;

    public static function initialize(
        SystemLogRepository $repository
    ): void
    {
        self::$repository = $repository;
    }

    public static function info(
        string $module,
        string $action,
        string $message,
        array $context = [],
        ?string $username = null
    ): void
    {
        if (!self::$repository) {
            return;
        }

        self::$repository->add(
            'INFO',
            $module,
            $action,
            $message,
            $context,
            $username
        );
    }

    public static function warning(
        string $module,
        string $action,
        string $message,
        array $context = [],
        ?string $username = null
    ): void
    {
        if (!self::$repository) {
            return;
        }

        self::$repository->add(
            'WARNING',
            $module,
            $action,
            $message,
            $context,
            $username
        );
    }

    public static function error(
        string $module,
        string $action,
        string $message,
        array $context = [],
        ?string $username = null
    ): void
    {
        if (!self::$repository) {
            return;
        }

        self::$repository->add(
            'ERROR',
            $module,
            $action,
            $message,
            $context,
            $username
        );
    }

    public static function success(
        string $module,
        string $action,
        string $message,
        array $context = [],
        ?string $username = null
    ): void
    {
        if (!self::$repository) {
            return;
        }

        self::$repository->add(
            'SUCCESS',
            $module,
            $action,
            $message,
            $context,
            $username
        );
    }
}