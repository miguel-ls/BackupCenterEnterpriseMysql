<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class RecoveryCodeRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();

        $this->initialize();
    }

    public function initialize(): void
    {
        $this->db->exec("

        CREATE TABLE IF NOT EXISTS recovery_codes
        (

            id INTEGER PRIMARY KEY AUTOINCREMENT,

            user_id INTEGER NOT NULL,

            code TEXT NOT NULL,

            used INTEGER DEFAULT 0,

            created_at TEXT DEFAULT CURRENT_TIMESTAMP

        )

        ");
    }

    public function regenerate(int $userId): array
    {
        $this->db->prepare("

        DELETE FROM recovery_codes

        WHERE user_id=?

        ")->execute([

            $userId

        ]);

        $codes=[];

        for($i=0;$i<10;$i++){

            $code=strtoupper(substr(bin2hex(random_bytes(4)),0,8));

            $code=substr($code,0,4)."-".substr($code,4,4);

            $codes[]=$code;

            $this->db->prepare("

            INSERT INTO recovery_codes
            (
                user_id,
                code
            )
            VALUES
            (
                ?,?
            )

            ")->execute([

                $userId,

                password_hash(
                    $code,
                    PASSWORD_DEFAULT
                )

            ]);

        }

        return $codes;
    }

    public function validate(
        int $userId,
        string $code
    ): bool
    {
        $stmt=$this->db->prepare("

        SELECT
            id,
            code
        FROM recovery_codes
        WHERE
            user_id=?
        AND
            used=0

        ");

        $stmt->execute([

            $userId

        ]);

        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){

            if(password_verify($code,$row["code"])){

                $this->db->prepare("

                UPDATE recovery_codes

                SET used=1

                WHERE id=?

                ")->execute([

                    $row["id"]

                ]);

                return true;

            }

        }

        return false;
    }
}