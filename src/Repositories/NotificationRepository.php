<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class NotificationRepository
{
    private PDO $db;

    private const MAX_NOTIFICATIONS = 200;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
    {
        $this->db->exec("
        CREATE TABLE IF NOT EXISTS notifications
        (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            level TEXT NOT NULL,
            title TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            is_read INTEGER DEFAULT 0
        );
        ");
    }

    public function add(
        string $level,
        string $title,
        string $message
    ): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications
            (
                level,
                title,
                message
            )
            VALUES
            (
                ?,?,?
            )
        ");

        $stmt->execute([
            strtoupper($level),
            $title,
            $message
        ]);

        $this->cleanup();

        return (int)$this->db->lastInsertId();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM notifications
            ORDER BY id DESC
            LIMIT 50
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnread(): array
    {
        $stmt = $this->db->query("
            SELECT *
            FROM notifications
            WHERE is_read = 0
            ORDER BY id DESC
            LIMIT 50
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread(): int
    {
        return (int)$this->db
            ->query("
                SELECT COUNT(*)
                FROM notifications
                WHERE is_read = 0
            ")
            ->fetchColumn();
    }

    public function markAsRead(int $id): void
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ?
        ");

        $stmt->execute([
            $id
        ]);
    }

    public function markAllAsRead(): void
    {
        $this->db->exec("
            UPDATE notifications
            SET is_read = 1
        ");
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("
            DELETE
            FROM notifications
            WHERE id = ?
        ");

        $stmt->execute([
            $id
        ]);
    }

    public function clear(): void
    {
        $this->db->exec("
            DELETE FROM notifications
        ");
    }

    private function cleanup(): void
    {
        $this->db->exec("
            DELETE FROM notifications
            WHERE id NOT IN
            (
                SELECT id
                FROM notifications
                ORDER BY id DESC
                LIMIT " . self::MAX_NOTIFICATIONS . "
            )
        ");
    }
}