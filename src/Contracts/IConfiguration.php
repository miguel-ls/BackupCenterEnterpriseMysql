<?php

namespace BackupCenter\Contracts;

use BackupCenter\Models\ConnectionConfig;

interface IConfiguration
{
    public function get(string $key, mixed $default = null): mixed;

    public function all(): array;

    public function getConnectionConfig(): ConnectionConfig;
}