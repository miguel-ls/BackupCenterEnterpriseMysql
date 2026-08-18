<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class SystemLogRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
{
    $this->db->exec("
        CREATE TABLE IF NOT EXISTS system_logs
        (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at TEXT NOT NULL,
            level TEXT NOT NULL,
            module TEXT NOT NULL,
            action TEXT,
            message TEXT NOT NULL,
            context TEXT,
            username TEXT,
            client_id INTEGER,
            connection_id INTEGER,
            job_id INTEGER
        )
    ");
}

public function add(
    string $level,
    string $module,
    string $action,
    string $message,
    array $context = [],
    ?string $username = null,
    ?int $clientId = null,
    ?int $connectionId = null,
    ?int $jobId = null
): void
{
    $stmt = $this->db->prepare("
        INSERT INTO system_logs
        (
            created_at,
            level,
            module,
            action,
            message,
            context,
            username,
            client_id,
            connection_id,
            job_id
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?
        )
    ");

    $stmt->execute([
        date('Y-m-d H:i:s'),
        strtoupper($level),
        $module,
        $action,
        $message,
        json_encode($context, JSON_UNESCAPED_UNICODE),
        $username,
        $clientId,
        $connectionId,
        $jobId
    ]);
}

}