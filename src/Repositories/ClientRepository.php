<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class ClientRepository
{
    private PDO $db;
    private ?string $primaryKey = null;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
    {
        // $this->db->exec("
        //     CREATE TABLE IF NOT EXISTS clients
        //     (
        //         id INTEGER PRIMARY KEY AUTOINCREMENT,
        //         code TEXT NOT NULL UNIQUE,
        //         business_name TEXT NOT NULL,
        //         trade_name TEXT,
        //         sftp_alias TEXT,
        //         ruc TEXT,
        //         contact_name TEXT,
        //         email TEXT,
        //         phone TEXT,
        //         address TEXT,
        //         status INTEGER DEFAULT 1,
        //         notes TEXT,
        //         created_at TEXT DEFAULT CURRENT_TIMESTAMP
        //     );
        // ");
    }

    public function getAll(): array
    {
        $primaryKey = $this->primaryKey();
        $idAlias = $primaryKey === 'id' ? '' : ', client_id AS id';

        $stmt = $this->db->query("
            SELECT clients.*{$idAlias}
            FROM clients
            ORDER BY business_name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id): ?array
    {
        $primaryKey = $this->primaryKey();
        $idAlias = $primaryKey === 'id' ? '' : ', client_id AS id';

        $stmt = $this->db->prepare("
            SELECT clients.*{$idAlias}
            FROM clients
            WHERE {$primaryKey}=?
        ");

        $stmt->execute([$id]);

        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        return $client ?: null;
    }

    public function create(
        string $code,
        string $businessName,
        ?string $tradeName,
        ?string $sftpAlias,     
        ?string $ruc,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        int $status,
        ?string $notes
    ): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO clients
            (
                code,
                business_name,
                trade_name,
                sftp_alias,
                ruc,
                contact_name,
                email,
                phone,
                address,
                status,
                notes
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?,?
            )
        ");

        $stmt->execute([
            $code,
            $businessName,
            $tradeName,
            $sftpAlias,
            $ruc,
            $contactName,
            $email,
            $phone,
            $address,
            $status,
            $notes
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(
        int $id,
        string $code,
        string $businessName,
        ?string $tradeName,
        ?string $sftpAlias,
        ?string $ruc,
        ?string $contactName,
        ?string $email,
        ?string $phone,
        ?string $address,
        int $status,
        ?string $notes
    ): bool
    {
        $primaryKey = $this->primaryKey();

        $stmt = $this->db->prepare("
            UPDATE clients
            SET
                code=?,
                business_name=?,
                trade_name=?,
                sftp_alias=?,
                ruc=?,
                contact_name=?,
                email=?,
                phone=?,
                address=?,
                status=?,
                notes=?
            WHERE {$primaryKey}=?
        ");

        return $stmt->execute([
            $code,
            $businessName,
            $tradeName,
            $sftpAlias,
            $ruc,
            $contactName,
            $email,
            $phone,
            $address,
            $status,
            $notes,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $primaryKey = $this->primaryKey();

        $stmt = $this->db->prepare("
            DELETE
            FROM clients
            WHERE {$primaryKey}=?
        ");

        return $stmt->execute([$id]);
    }

    private function primaryKey(): string
    {
        if ($this->primaryKey !== null) {
            return $this->primaryKey;
        }

        $columns = $this->db->query('SHOW COLUMNS FROM clients')
            ->fetchAll(PDO::FETCH_COLUMN);

        $this->primaryKey = in_array('client_id', $columns, true)
            ? 'client_id'
            : 'id';

        return $this->primaryKey;
    }
}
