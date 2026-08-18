<?php

declare(strict_types=1);

namespace BackupCenter\Services;

use BackupCenter\Repositories\AgentRepository;
use BackupCenter\Core\ApiResponse;
use PDO;

class AgentService
{
    private AgentRepository $repository;

    public function __construct(PDO $pdo)
    {
        $this->repository = new AgentRepository($pdo);
    }

private function authorizeJob(
    int $jobId,
    int $authenticatedConnectionId
): void
{
    $jobConnectionId = $this->repository->findJobConnectionId($jobId);

    if ($jobConnectionId === null) {
        ApiResponse::error('Job not found', 404);
    }

    if ($jobConnectionId !== $authenticatedConnectionId) {
        ApiResponse::error('Forbidden', 403);
    }
}

public function exists(?int $authenticatedConnectionId = null): void
{
    try
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $jobId = (int)$data['jobId'];

        if ($authenticatedConnectionId !== null) {
            $this->authorizeJob($jobId, $authenticatedConnectionId);
        }

        $exists = $this->repository->fileExists(
            $jobId,
            $data['sha256']
        );

        ApiResponse::success([
            'exists' => $exists
        ]);
    }
    catch (\Throwable $e)
    {
        http_response_code(500);

        echo json_encode([
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ]);
    }
}

public function registerFile(?int $authenticatedConnectionId = null): void
{
    try
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $jobId = (int)$data['jobId'];

        if ($authenticatedConnectionId !== null) {
            $this->authorizeJob($jobId, $authenticatedConnectionId);
        }

        $this->repository->saveFile(
            $jobId,
            $data['fileName'],
            (int)$data['fileSize'],
            $data['sha256']
        );

        ApiResponse::success([
            'registered' => true
        ]);
    }
    catch (\Throwable $e)
    {
        http_response_code(500);

        echo json_encode([
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ]);
        
    }
}

public function register(): void
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['installToken'])) {
        ApiResponse::error('Install Token is required');
        return;
    }

    $connection = $this->repository->findByInstallToken(
        $input['installToken']
    );

    if ($connection === null) {
        ApiResponse::error('Invalid Install Token');
        return;
    }

    $agentToken = bin2hex(random_bytes(32));
    $agentTokenHash = hash('sha256', $agentToken);

    if (!$this->repository->saveAgentTokenHash(
        (int)$connection['id'],
        $agentTokenHash
    )) {
        ApiResponse::error('No fue posible generar las credenciales del agente');
        return;
    }

    $jobs = $this->repository->getEnabledJobs(
        (int)$connection['id']
    );

    $queue = $this->repository->getQueuedJobs(
        (int)$connection['id']
    );

    $response = [

        'connection' => [

            'id'         => (int)$connection['id'],
            'clientId'   => (int)$connection['client_id'],
            'name'       => $connection['name'],

            'host'       => $connection['host'],
            'port'       => (int)$connection['port'],
            'protocol'   => $connection['protocol'],

            'username'   => $connection['username'],
            'password'   => $connection['password'],
            'hostKey'    => $connection['hostkey'],

            'remotePath' => $connection['remote_path']

        ],

        'settings' => [

            'pollInterval' => 60

        ],

        'jobs' => $jobs,

        'queue' => $queue
    ];

    if ($agentToken !== null) {
        $response['agentToken'] = $agentToken;
    }

    ApiResponse::success($response);
}

public function executionHistory(?int $authenticatedConnectionId = null): void
{
    try
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if ($authenticatedConnectionId !== null) {
            $jobId = (int)$data['jobId'];

            if (array_key_exists('queueId', $data) && $data['queueId'] !== null) {
                $queueJobId = $this->repository->findQueueJobId(
                    (int)$data['queueId']
                );

                if ($queueJobId === null) {
                    ApiResponse::error('Queue not found', 404);
                }

                if ($queueJobId !== $jobId) {
                    ApiResponse::error('Forbidden', 403);
                }
            }

            $this->authorizeJob($jobId, $authenticatedConnectionId);
        }

        $this->repository->saveExecutionHistory($data);

        ApiResponse::success([
            'saved' => true
        ]);
    }
    catch (\Throwable $e)
    {
        http_response_code(500);

        echo json_encode([
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString()
        ]);
    }
}    
}