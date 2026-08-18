<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class SettingsRepository
{
    private Database $database;

    private PDO $connection;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->connection = $database->getConnection();
    }

    public function initialize(): void
    {
        $this->connection->exec("
            CREATE TABLE IF NOT EXISTS settings
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,

                category TEXT NOT NULL,

                key TEXT NOT NULL,

                value TEXT,

                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

                UNIQUE(category, key)
            )
        ");
    }

    public function get(string $category, string $key, ?string $default = null): ?string
    {
        $statement = $this->connection->prepare("
            SELECT value
            FROM settings
            WHERE category = :category
              AND key = :key
            LIMIT 1
        ");

        $statement->execute([
            ':category' => $category,
            ':key' => $key
        ]);

        $value = $statement->fetchColumn();

        return $value !== false ? $value : $default;
    }

    public function set(string $category, string $key, string $value): void
    {
        $statement = $this->connection->prepare("
            INSERT INTO settings(category, key, value)
            VALUES(:category, :key, :value)
            ON CONFLICT(category, key)
            DO UPDATE SET
                value = excluded.value,
                updated_at = CURRENT_TIMESTAMP
        ");

        $statement->execute([
            ':category' => $category,
            ':key' => $key,
            ':value' => $value
        ]);
    }

    public function all(string $category): array
    {
        $statement = $this->connection->prepare("
            SELECT key, value
            FROM settings
            WHERE category = :category
            ORDER BY key
        ");

        $statement->execute([
            ':category' => $category
        ]);

        $settings = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }
}