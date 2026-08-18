<?php

namespace BackupCenter\Models;

class ConnectionConfig
{
    public function __construct(
        private string $host,
        private int $port,
        private string $username,
        private string $password,
        private string $hostKey,
        private string $remotePath = ''
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getHostKey(): string
    {
        return $this->hostKey;
    }

    public function getRemotePath(): string
    {
        return $this->remotePath;
    }
}
