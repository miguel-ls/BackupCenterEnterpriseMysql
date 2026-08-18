<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class SchedulerRepository
{
    public function __construct(
        private Database $database
    ) {
    }

    public function getEnabledJobs(): array
    {
        $stmt = $this->database
            ->getConnection()
            ->query("
                SELECT
                    id,
                    name,
                    schedule,
                    enabled,
                    running
                FROM jobs
                WHERE enabled = 1
                ORDER BY id
            ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}