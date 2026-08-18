<?php

declare(strict_types=1);

namespace BackupCenter\Core;

use BackupCenter\Repositories\AgentRepository;

class AgentAuth
{
    public static function validateAgentToken(AgentRepository $repository): array
    {
        $token = ApiController::bearerToken();

        if ($token === null || $token === '') {
            ApiResponse::error('Agent authentication required', 401);
        }

        $connection = $repository->findConnectionByAgentTokenHash(
            hash('sha256', $token)
        );

        if ($connection === null) {
            ApiResponse::error('Invalid Agent Token', 401);
        }

        return [
            'connection_id' => (int)$connection['connection_id']
        ];
    }
}
