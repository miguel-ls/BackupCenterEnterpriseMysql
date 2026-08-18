<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class ConnectionRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS connections
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                host TEXT NOT NULL,
                port INTEGER NOT NULL DEFAULT 22,
                username TEXT NOT NULL,
                password TEXT NOT NULL,
                hostkey TEXT,
                protocol TEXT NOT NULL DEFAULT 'SFTP',
                client_id INTEGER,
                remote_path TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                install_token TEXT,
                agent_token_hash TEXT
            );
        ");

        $this->ensureInstallTokenColumn();
        $this->ensureAgentTokenHashColumn();
        $this->populateMissingInstallTokens();
    }

    private function ensureInstallTokenColumn(): void
    {
        $stmt = $this->db->query('PRAGMA table_info(connections)');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'install_token') {
                return;
            }
        }

        $this->db->exec('ALTER TABLE connections ADD COLUMN install_token TEXT');
    }

    private function ensureAgentTokenHashColumn(): void
    {
        $stmt = $this->db->query('PRAGMA table_info(connections)');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'agent_token_hash') {
                return;
            }
        }

        $this->db->exec('ALTER TABLE connections ADD COLUMN agent_token_hash TEXT');
    }

    private function populateMissingInstallTokens(): void
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM connections WHERE install_token IS NULL OR install_token = ""'
        );
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $token = $this->generateInstallToken();
            $update = $this->db->prepare(
                'UPDATE connections SET install_token=? WHERE id=?'
            );
            $update->execute([$token, (int)$row['id']]);
        }
    }

    private function generateInstallToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function get(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM connections
            WHERE id=?
        ");

        $stmt->execute([$id]);

        $connection = $stmt->fetch(PDO::FETCH_ASSOC);

        return $connection ?: null;
    }

    public function getAll(): array
    {
    $stmt = $this->db->query("
        SELECT
            c.id,
            c.name,
            c.host,
            c.port,
            c.username,
            c.hostkey,
            c.protocol,
            c.client_id,
            c.remote_path,
            c.created_at,
            c.install_token,
            c.installed,
            cl.business_name AS client_name
        FROM connections c
        LEFT JOIN clients cl
            ON cl.id = c.client_id
        ORDER BY c.id DESC
    ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(
            int $clientId,
            string $name,
            string $host,
            int $port,
            string $username,
            string $password,
            string $hostkey,
            string $protocol,
            string $remotePath
    ): int
    {
$installToken = $this->generateInstallToken();

        $stmt = $this->db->prepare(" 
            INSERT INTO connections
            (
            client_id,
            name,
            host,
            port,
            username,
            password,
            hostkey,
            protocol,
            remote_path,
            install_token
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->execute([
            $clientId,
            $name,
            $host,
            $port,
            $username,
            $password,
            $hostkey,
            $protocol,
            $remotePath,
            $installToken
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(
        int $id,
        int $clientId,
        string $name,
        string $host,
        int $port,
        string $username,
        string $password,
        string $hostkey,
        string $protocol,
        string $remotePath
    ): bool
    {
        $stmt = $this->db->prepare("
            UPDATE connections
            SET
                client_id=?,
                name=?,
                host=?,
                port=?,
                username=?,
                password=?,
                hostkey=?,
                protocol=?,
                remote_path=?
            WHERE id=?
        ");

        return $stmt->execute([
            $clientId,
            $name,
            $host,
            $port,
            $username,
            $password,
            $hostkey,
            $protocol,
            $remotePath,
            $id
        ]);
    }

    public function setInstalled(int $id, bool $installed): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE connections SET installed=? WHERE id=?'
        );

        return $stmt->execute([
            $installed ? 1 : 0,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE
            FROM connections
            WHERE id=?
        ");

        return $stmt->execute([$id]);
    }
}