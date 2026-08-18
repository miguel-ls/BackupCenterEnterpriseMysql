<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use BackupCenter\Models\ExecutionSummary;

class ExecutionHistoryRepository
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function save(
        ExecutionSummary $summary,
        int $jobId,
        string $client
    ): void
    {
        $sql = "
        INSERT INTO execution_history
        (
            job_id,
            started_at,
            client,
            files_found,
            files_uploaded,
            files_skipped,
            errors,
            duration,
            status
        )
        VALUES
        (
            :job_id,
            :started_at,
            :client,
            :files_found,
            :files_uploaded,
            :files_skipped,
            :errors,
            :duration,
            :status
        )
        ";

        $stmt = $this->database
            ->getConnection()
            ->prepare($sql);

        $stmt->execute([
            ':job_id'         => $jobId,
            ':started_at'    => date('Y-m-d H:i:s'),
            ':client'         => $client,
            ':files_found'    => $summary->found,
            ':files_uploaded' => $summary->uploaded,
            ':files_skipped'  => $summary->skipped,
            ':errors'         => $summary->errors,
            ':duration'       => $summary->getDuration(),
            ':status'         => $summary->isSuccess() ? 'OK' : 'ERROR'
        ]);
    }

    public function latest(int $limit = 10): array
    {
        $stmt = $this->database
            ->getConnection()
            ->prepare("
            SELECT
                eh.id,
                eh.job_id,
                eh.started_at,
                eh.finished_at,
                c.business_name AS client_name,
                j.name AS job_name,
                eh.files_found,
                eh.files_uploaded,
                eh.files_skipped,
                eh.files_failed,
                eh.duration_seconds,
                eh.status
                FROM execution_history eh

                LEFT JOIN jobs j
                    ON j.id = eh.job_id

                LEFT JOIN connections cn
                    ON cn.id = j.connection_id

                LEFT JOIN clients c
                    ON c.id = cn.client_id

                ORDER BY eh.id DESC
                LIMIT :limit
            ");

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function statistics(): array
    {
        $sql = "
            SELECT

                COUNT(*) total_runs,

                SUM(
                    CASE
                        WHEN status='Correcto'
                        THEN 1
                        ELSE 0
                    END
                ) success_runs,

                SUM(
                    CASE
                        WHEN status='Con errores'
                        THEN 1
                        ELSE 0
                    END
                ) error_runs,

                SUM(files_uploaded) uploaded,

                SUM(files_skipped) skipped,

                ROUND(AVG(duration_seconds),2) average_duration

            FROM execution_history
        ";

        $stmt = $this->database
            ->getConnection()
            ->query($sql);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}