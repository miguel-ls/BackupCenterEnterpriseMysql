<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class SessionRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();

        $this->initialize();
    }

    private function initialize(): void
    {
        $this->db->exec("

        CREATE TABLE IF NOT EXISTS user_sessions
        (

            id INTEGER PRIMARY KEY AUTOINCREMENT,

            user_id INTEGER NOT NULL,

            token TEXT NOT NULL,

            device TEXT,

            ip TEXT,

            created_at TEXT DEFAULT CURRENT_TIMESTAMP,

            last_activity TEXT DEFAULT CURRENT_TIMESTAMP,

            expires_at TEXT,

            active INTEGER DEFAULT 1

        )

        ");
    }

    public function create(
        int $userId,
        string $token,
        string $device,
        string $ip,
        string $expires
    ): void
    {
        $stmt=$this->db->prepare("

        INSERT INTO user_sessions
        (
            user_id,
            token,
            device,
            ip,
            expires_at
        )
        VALUES
        (
            ?,?,?,?,?
        )

        ");

        $stmt->execute([

            $userId,

            password_hash($token,PASSWORD_DEFAULT),

            $device,

            $ip,

            $expires

        ]);
    }

    public function validate(string $token): ?array
    {
        $stmt=$this->db->query("

        SELECT

            *

        FROM user_sessions

        WHERE

            active=1

        ");

        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){

            if(password_verify($token,$row["token"])){

                if(
                    strtotime($row["expires_at"])<time()
                ){

                    return null;

                }

                $this->db->prepare("

                UPDATE user_sessions

                SET last_activity=datetime('now')

                WHERE id=?

                ")->execute([

                    $row["id"]

                ]);

                return $row;

            }

        }

        return null;
    }

    public function logout(string $token): void
    {
        $stmt=$this->db->query("

        SELECT

            id,

            token

        FROM user_sessions

        WHERE active=1

        ");

        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){

            if(password_verify($token,$row["token"])){

                $this->db->prepare("

                UPDATE user_sessions

                SET active=0

                WHERE id=?

                ")->execute([

                    $row["id"]

                ]);

                return;

            }

        }
    }

    public function getByUser(int $userId): array
    {
        $stmt=$this->db->prepare("

        SELECT

            id,

            device,

            ip,

            created_at,

            last_activity,

            expires_at,

            active

        FROM user_sessions

        WHERE user_id=?

        ORDER BY last_activity DESC

        ");

        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function close(int $id): void
    {
        $this->db->prepare("

        UPDATE user_sessions

        SET active=0

        WHERE id=?

        ")->execute([$id]);
    }
}