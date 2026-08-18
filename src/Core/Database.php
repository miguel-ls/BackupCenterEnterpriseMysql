<?php

namespace BackupCenter\Core;

use PDO;

class Database
{
    private PDO $connection;

    public function __construct(string $databaseFile)
    {
        $this->connection = new PDO(
            'sqlite:' . $databaseFile
        );

        $this->connection->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}