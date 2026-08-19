<?php

namespace BackupCenter\Core;

use PDO;

class Database
{
    private PDO $connection;

public function __construct()
{
    $this->connection = new PDO(
        'mysql:host=172.16.50.23;dbname=backupcenter;charset=utf8mb4',
        'backupcenter',
        '1q2w3e4r5t.',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}