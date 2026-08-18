<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/bootstrap.php';

use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\AgentAuth;
use BackupCenter\Core\ApiController;
use BackupCenter\Repositories\AgentRepository;
use BackupCenter\Services\AgentService;

$service = new AgentService($pdo);

$action = $_GET['action'] ?? '';
$authenticatedConnectionId = null;

if (
    in_array($action, ['exists', 'register-file', 'execution-history'], true)
    && array_key_exists('HTTP_AUTHORIZATION', $_SERVER)
) {
    $agentIdentity = AgentAuth::validateAgentToken(new AgentRepository($pdo));
    $authenticatedConnectionId = $agentIdentity['connection_id'];
}

switch ($action) {

    case 'register':
        $service->register();
        break;

    case 'exists':
        $service->exists($authenticatedConnectionId);
        break;

    case 'register-file':
        $service->registerFile($authenticatedConnectionId);
        break;
                
    case 'execution-history':
        $service->executionHistory($authenticatedConnectionId);
        break;

    default:
        ApiResponse::error('Invalid action');
}