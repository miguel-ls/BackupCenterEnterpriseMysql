<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class JobRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
    {
        $this->db->exec("
CREATE TABLE IF NOT EXISTS jobs
(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    connection_id INTEGER,
    name TEXT NOT NULL,
    source TEXT,
    destination TEXT,
    remote_path TEXT,
    schedule TEXT,
    enabled INTEGER DEFAULT 1,
    last_run TEXT,
    last_status TEXT,
    running INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
        ");
    }

    public function getJobs(): array
    {
        $stmt = $this->db->query("
            SELECT
                j.id,
                j.connection_id,
                j.name,
                j.source,
                j.destination,
                j.schedule,
                j.enabled,
COALESCE(
    (
        SELECT e.started_at
        FROM execution_history e
        WHERE e.job_id = j.id
        ORDER BY e.id DESC
        LIMIT 1
    ),
    j.last_run,
    '-'
) AS time,

COALESCE(
    (
        SELECT e.status
        FROM execution_history e
        WHERE e.job_id = j.id
        ORDER BY e.id DESC
        LIMIT 1
    ),
    j.last_status,
    'Pendiente'
) AS status,
                COALESCE(c.name,'Sin conexión') AS connection,
                COALESCE(cl.business_name,'-') AS client_name
            FROM jobs j

            LEFT JOIN connections c
                ON c.id=j.connection_id

            LEFT JOIN clients cl
                ON cl.id = c.client_id

            ORDER BY j.id
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJob(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM jobs
            WHERE id=?
        ");

        $stmt->execute([$id]);

        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        return $job ?: null;
    }

public function createJob(
    ?int $connectionId,
    string $name,
    string $source,
    string $destination,
    string $schedule,
    int $enabled = 1
): int
{
    $stmt = $this->db->prepare("
        INSERT INTO jobs
        (
            connection_id,
            name,
            source,
            destination,
            schedule,
            enabled,
            last_status
        )
        VALUES
        (
            ?,?,?,?,?,?,?
        )
    ");

    $stmt->execute([
        $connectionId,
        $name,
        $source,
        $destination,
        $schedule,
        $enabled,
        'Pendiente'
    ]);

    return (int)$this->db->lastInsertId();
}

public function updateJob(
    int $id,
    ?int $connectionId,
    string $name,
    string $source,
    string $destination,
    string $schedule,
    int $enabled = 1
): bool
{
    $stmt = $this->db->prepare("
        UPDATE jobs
        SET
            connection_id=?,
            name=?,
            source=?,
            destination=?,
            schedule=?,
            enabled=?
        WHERE id=?
    ");

    return $stmt->execute([
        $connectionId,
        $name,
        $source,
        $destination,
        $schedule,
        $enabled,
        $id
    ]);
}

public function setEnabled(int $id, bool $enabled): bool
{
    $stmt = $this->db->prepare("
        UPDATE jobs
        SET enabled=?
        WHERE id=?
    ");

    return $stmt->execute([
        $enabled ? 1 : 0,
        $id
    ]);
}

    public function deleteJob(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE
            FROM jobs
            WHERE id=?
        ");

        return $stmt->execute([$id]);
    }

    public function updateExecution(
        int $id,
        string $status
    ): void
    {
        $stmt = $this->db->prepare("
            UPDATE jobs
            SET
                last_run=?,
                last_status=?
            WHERE id=?
        ");

        $stmt->execute([
            date('Y-m-d H:i:s'),
            $status,
            $id
        ]);
    }

public function getEnabledJobs(): array
{
    $stmt = $this->db->query("

        SELECT

            j.*,

            c.name AS connection_name,

            cl.business_name AS client_name

        FROM jobs j

        LEFT JOIN connections c
            ON c.id = j.connection_id

        LEFT JOIN clients cl
            ON cl.id = c.client_id

        WHERE j.enabled = 1

        ORDER BY j.id

    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function isRunning(int $id): bool
{
    $stmt = $this->db->prepare("
        SELECT running
        FROM jobs
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    return (int)$stmt->fetchColumn() === 1;
}

public function setRunning(
    int $id,
    bool $running
): void
{
    $stmt = $this->db->prepare("
        UPDATE jobs
        SET running = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $running ? 1 : 0,
        $id
    ]);
}

public function getRunningJobs(): array
{
    $stmt = $this->db->query("
        SELECT *
        FROM jobs
        WHERE running = 1
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function executedThisMinute(int $id): bool
{
    $stmt = $this->db->prepare("
        SELECT last_run
        FROM jobs
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $lastRun = $stmt->fetchColumn();

    if (!$lastRun) {
        return false;
    }

    return date('Y-m-d H:i') === date(
        'Y-m-d H:i',
        strtotime($lastRun)
    );
}

}
