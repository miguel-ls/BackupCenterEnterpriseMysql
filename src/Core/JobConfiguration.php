<?php

namespace BackupCenter\Core;

use BackupCenter\Contracts\IConfiguration;
use BackupCenter\Models\ConnectionConfig;

class JobConfiguration implements IConfiguration
{
    private array $job;

    public function __construct(array $job)
    {
        $this->job = $job;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->job[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->job;
    }

    public function getConnectionConfig(): ConnectionConfig
    {
        return new ConnectionConfig(
            $this->get('host'),
            (int)$this->get('port', 22),
            $this->get('username'),
            $this->get('password'),
            $this->get('hostkey'),
            (string)$this->get('remote_path', '')
        );
    }
}