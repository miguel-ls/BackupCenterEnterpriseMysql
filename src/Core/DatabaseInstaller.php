<?php

namespace BackupCenter\Core;

use PDO;

class DatabaseInstaller
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function install(): void
    {
        $this->createUsers();

        $this->createSettings();

        // Próximamente
        // $this->createConnections();
        // $this->createJobs();
        // $this->createJobQueue();
        // $this->createNotifications();
        // $this->createUploadedFiles();
        // $this->createExecutionHistory();
    }

    private function createUsers(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                fullname TEXT NOT NULL,
                role TEXT NOT NULL,
                enabled INTEGER DEFAULT 1,
                last_login TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $count = (int)$this->db
            ->query("SELECT COUNT(*) FROM users")
            ->fetchColumn();

        if ($count === 0) {

            $stmt = $this->db->prepare("
                INSERT INTO users
                (
                    username,
                    password,
                    fullname,
                    role
                )
                VALUES
                (
                    ?,?,?,?
                )
            ");

            $stmt->execute([
                "admin",
                password_hash(
                    "admin123",
                    PASSWORD_DEFAULT
                ),
                "Administrador",
                "ADMIN"
            ]);
        }
    }

    private function createSettings(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS settings
            (
                id INTEGER PRIMARY KEY CHECK(id=1),

                scheduler_interval INTEGER DEFAULT 60,

                max_threads INTEGER DEFAULT 4,

                retry_count INTEGER DEFAULT 3,

                retention_days INTEGER DEFAULT 30,

                compression INTEGER DEFAULT 1,

                log_level TEXT DEFAULT 'INFO',

                log_path TEXT DEFAULT 'resources/logs',

                connection_timeout INTEGER DEFAULT 30
            )
        ");

        $count = (int)$this->db
            ->query("SELECT COUNT(*) FROM settings")
            ->fetchColumn();

        if ($count === 0) {

            $this->db->exec("
                INSERT INTO settings(id)
                VALUES(1)
            ");

        }
    }
}