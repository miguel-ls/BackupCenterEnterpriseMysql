<?php

namespace BackupCenter\Core;

class Paths
{
    public static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    public static function storage(): string
    {
        return self::root() . '/storage';
    }

    public static function database(): string
    {
        return self::storage() . '/database';
    }

    public static function logs(): string
    {
        return self::storage() . '/logs';
    }

    public static function temp(): string
    {
        return self::storage() . '/temp';
    }

    public static function config(): string
    {
        return self::root() . '/resources/config';
    }
}