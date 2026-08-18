<?php

declare(strict_types=1);

namespace BackupCenter\Repositories;

use PDO;

class AgentRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function findByInstallToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                client_id,
                name,
                host,
                port,
                username,
                password,
                hostkey,
                protocol,
                remote_path,
                agent_token_hash
            FROM connections
            WHERE install_token = ?
            LIMIT 1
        ");

        $stmt->execute([$token]);

        $connection = $stmt->fetch(PDO::FETCH_ASSOC);

        return $connection ?: null;
    }

    public function saveAgentTokenHash(
        int $connectionId,
        string $agentTokenHash
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE connections
            SET agent_token_hash = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $agentTokenHash,
            $connectionId
        ]);
    }

    public function findConnectionByAgentTokenHash(string $hash): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, agent_token_hash
            FROM connections
            WHERE agent_token_hash = ?
            LIMIT 1
        ");

        $stmt->execute([$hash]);

        $connection = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$connection) {
            return null;
        }

        return [
            'connection_id' => (int)$connection['id']
        ];
    }

    public function findJobConnectionId(int $jobId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT connection_id
            FROM jobs
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$jobId]);

        $connectionId = $stmt->fetchColumn();

        return $connectionId === false ? null : (int)$connectionId;
    }

    public function findQueueJobId(int $queueId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT job_id
            FROM job_queue
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$queueId]);

        $jobId = $stmt->fetchColumn();

        return $jobId === false ? null : (int)$jobId;
    }

    public function getEnabledJobs(int $connectionId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                name,
                source,
                destination,
                schedule
            FROM jobs
            WHERE connection_id = ?
            AND enabled = 1
            ORDER BY id
        ");

        $stmt->execute([$connectionId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } 
    
    public function fileExists(
        int $jobId,
        string $sha256
    ): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM uploaded_files
            WHERE job_id = ?
            AND sha256 = ?
        ");

        $stmt->execute([
            $jobId,
            $sha256
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }  
    
public function saveFile(
    int $jobId,
    string $filename,
    int $filesize,
    string $sha256
): void
{
    $stmt = $this->pdo->prepare("
        INSERT INTO uploaded_files
        (
            job_id,
            filename,
            filesize,
            sha256,
            uploaded_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $jobId,
        $filename,
        $filesize,
        $sha256,
        date('Y-m-d H:i:s')
    ]);
}

public function saveExecutionHistory(array $data): void
{
    $this->pdo->beginTransaction();

    try
    {
        //  SOLO controlar el INSERT 
        if ((int)$data['filesFound'] !== (int)$data['filesSkipped']) {

            $stmt = $this->pdo->prepare("
                INSERT INTO execution_history
                (
                    job_id,
                    started_at,
                    finished_at,
                    files_found,
                    files_uploaded,
                    files_skipped,
                    files_failed,
                    duration_seconds,
                    status,
                    created_at
                )
                VALUES
                (
                    :job_id,
                    :started_at,
                    :finished_at,
                    :files_found,
                    :files_uploaded,
                    :files_skipped,
                    :files_failed,
                    :duration_seconds,
                    :status,
                    :created_at
                )
            ");

            $stmt->execute([
                ':job_id' => $data['jobId'],
                ':started_at' => $data['startedAt'],
                ':finished_at' => $data['finishedAt'],
                ':files_found' => $data['filesFound'],
                ':files_uploaded' => $data['filesUploaded'],
                ':files_skipped' => $data['filesSkipped'],
                ':files_failed' => $data['filesFailed'],
                ':duration_seconds' => $data['durationSeconds'],
                ':status' => $data['status'],
                ':created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $stmt = $this->pdo->prepare("
            UPDATE jobs
            SET
                last_run = :last_run,
                last_status = :last_status,
                running = 0
            WHERE id = :job_id
        ");

        $stmt->execute([
            ':last_run' => $data['finishedAt'],
            ':last_status' => $data['status'],
            ':job_id' => $data['jobId']
        ]);

        /*
        |--------------------------------------------------------------------------
        | Si este Job estaba en la cola, finalizarlo
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('queueId', $data) && $data['queueId'] !== null) {

            $stmt = $this->pdo->prepare("
                UPDATE job_queue
                SET
                    status = 'Completed',
                    started_at = COALESCE(started_at, :started_at),
                    finished_at = :finished_at
                WHERE id = :queue_id
            ");

            $stmt->execute([
                ':started_at' => $data['startedAt'],
                ':finished_at' => $data['finishedAt'],
                ':queue_id' => $data['queueId']
            ]);
        }

        $this->pdo->commit();
    }
    catch (\Throwable $e)
    {
        $this->pdo->rollBack();
        throw $e;
    }
}

public function getQueuedJobs(int $connectionId): array
{
    $stmt = $this->pdo->prepare("
        SELECT
            q.id AS queueId,
            j.id,
            j.name,
            j.source,
            j.destination,
            j.schedule
        FROM job_queue q
        INNER JOIN jobs j
            ON j.id = q.job_id
        WHERE q.status = 'Pending'
          AND j.connection_id = ?
          AND j.enabled = 1
        ORDER BY q.id
    ");

    $stmt->execute([$connectionId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}