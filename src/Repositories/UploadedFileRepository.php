<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class UploadedFileRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS uploaded_files
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                job_id INTEGER NOT NULL,
                filename TEXT NOT NULL,
                filesize INTEGER NOT NULL,
                sha256 TEXT NOT NULL,
                uploaded_at TEXT NOT NULL
            );
        ");
    }

public function exists(
    int $jobId,
    string $sha256
): bool
{
    $stmt = $this->db->prepare("
        SELECT COUNT(*)
        FROM uploaded_files
        WHERE job_id = ?
        AND sha256 = ?
    ");

    $stmt->execute([
        $jobId,
        $sha256
    ]);

    return (int)$stmt->fetchColumn() > 0;
}

public function save(
    int $jobId,
    string $filename,
    int $filesize,
    string $sha256
): void
{
    $stmt = $this->db->prepare("
        INSERT INTO uploaded_files
        (
            job_id,
            filename,
            filesize,
            sha256,
            uploaded_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $jobId,
        $filename,
        $filesize,
        $sha256,
        date('Y-m-d H:i:s')
    ]);
}

    public function calculateSha256(string $file): string
    {
        return hash_file('sha256', $file);
    }

    public function initializeExecutionHistory(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS execution_history
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                job_id INTEGER NOT NULL,
                started_at TEXT NOT NULL,
                client TEXT NOT NULL,
                files_found INTEGER NOT NULL,
                files_uploaded INTEGER NOT NULL,
                files_skipped INTEGER NOT NULL,
                errors INTEGER NOT NULL,
                duration REAL NOT NULL,
                status TEXT NOT NULL
            );
        ");
    }

    public function initializeJobs(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS jobs
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                source TEXT,
                destination TEXT,
                schedule TEXT,
                enabled INTEGER DEFAULT 1,
                last_run TEXT,
                last_status TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $this->db->exec("
            INSERT INTO jobs
            (
                name,
                source,
                destination,
                schedule,
                last_status
            )
            SELECT
                'ERP SQL',
                'B:\\Backup ERP',
                '/backups/unimarket',
                '0 */6 * * *',
                'Correcto'
            WHERE NOT EXISTS
            (
                SELECT 1 FROM jobs
            );
        ");
    }

    public function getJobs(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                source,
                destination,
                schedule,
                enabled,
                last_run,
                last_status
            FROM jobs
            ORDER BY id
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}