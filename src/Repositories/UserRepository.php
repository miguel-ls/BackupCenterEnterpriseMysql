<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function initialize(): void
    {
        $this->db->exec("

        CREATE TABLE IF NOT EXISTS users
        (

            id INTEGER PRIMARY KEY AUTOINCREMENT,

            username TEXT UNIQUE,

            password TEXT,

            fullname TEXT,

            role TEXT,

            enabled INTEGER DEFAULT 1,

            twofactor_enabled INTEGER DEFAULT 0,

            twofactor_secret TEXT,

            last_login TEXT,

            created_at TEXT DEFAULT CURRENT_TIMESTAMP

        )

        ");

        $columns=$this->db
            ->query("PRAGMA table_info(users)")
            ->fetchAll(PDO::FETCH_ASSOC);

        $existing=array_column($columns,'name');

        if(!in_array('twofactor_enabled',$existing)){

            $this->db->exec("
            ALTER TABLE users
            ADD COLUMN twofactor_enabled INTEGER DEFAULT 0
            ");

        }

        if(!in_array('twofactor_secret',$existing)){

            $this->db->exec("
            ALTER TABLE users
            ADD COLUMN twofactor_secret TEXT
            ");

        }

    }

}