<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class AuditRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        date_default_timezone_set('America/Lima');

        $this->db = $database->getConnection();

        $this->initialize();
    }

    public function initialize(): void
    {
        $this->db->exec("

        CREATE TABLE IF NOT EXISTS audit_log
        (

            id INTEGER PRIMARY KEY AUTOINCREMENT,

            created_at TEXT DEFAULT CURRENT_TIMESTAMP,

            username TEXT,

            module TEXT,

            action TEXT,

            description TEXT,

            ip TEXT,

            hostname TEXT,

            success INTEGER DEFAULT 1,

            duration REAL DEFAULT 0,

            metadata TEXT

        )

        ");

        $columns = $this->db->query("PRAGMA table_info(audit_log)")
            ->fetchAll(PDO::FETCH_ASSOC);

        $existing = array_column($columns, 'name');

        if (!in_array('hostname', $existing)) {
            $this->db->exec("ALTER TABLE audit_log ADD COLUMN hostname TEXT");
        }

        if (!in_array('duration', $existing)) {
            $this->db->exec("ALTER TABLE audit_log ADD COLUMN duration REAL DEFAULT 0");
        }

        if (!in_array('metadata', $existing)) {
            $this->db->exec("ALTER TABLE audit_log ADD COLUMN metadata TEXT");
        }
    }

    public function add(
        ?string $username,
        string $module,
        string $action,
        string $description = '',
        bool $success = true,
        float $duration = 0,
        array $metadata = []
    ): void
    {
        $stmt = $this->db->prepare("

            INSERT INTO audit_log
            (

                created_at,

                username,

                module,

                action,

                description,

                ip,

                hostname,

                success,

                duration,

                metadata

            )

            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?
            )

        ");

        $stmt->execute([

            date('Y-m-d H:i:s'),

            $username,

            $module,

            $action,

            $description,

            $_SERVER['REMOTE_ADDR'] ?? 'LOCAL',

            gethostname(),

            $success ? 1 : 0,

            $duration,

            json_encode(
                $metadata,
                JSON_UNESCAPED_UNICODE
            )

        ]);
    }

    public function all(
        int $limit = 500
    ): array
    {
        $stmt = $this->db->prepare("

            SELECT
                *
            FROM audit_log
            ORDER BY id DESC
            LIMIT ?

        ");

        $stmt->bindValue(
            1,
            $limit,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    public function clear(): void
    {
        $this->db->exec("
            DELETE FROM audit_log
        ");
    }

    public function count(): int
    {
        return (int)$this->db
            ->query("
                SELECT COUNT(*)
                FROM audit_log
            ")
            ->fetchColumn();
    }
}