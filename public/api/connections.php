<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\ConnectionRepository;
use BackupCenter\Repositories\NotificationRepository;
use BackupCenter\Services\JobLogService;

Auth::require();

try {
    $app = new Application();
    $db = $app->database();

    $repository = new ConnectionRepository($db);
    $notifications = new NotificationRepository($db);
    $logService = new JobLogService();

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {

case 'GET':

    echo json_encode([
        'success' => true,
        'data' => $repository->getAll()
    ]);

break;

case 'POST':

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (($data['action'] ?? '') === 'install-package') {
        $connection = $repository->get((int)$data['id']);

        if (!$connection) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Conexión no encontrada'
            ]);
            break;
        }

        $payload = [
            'server' => 'https://backup.codesicorp.net',
            'installToken' => $connection['install_token']
        ];

        $tempDir = sys_get_temp_dir() . '/backupcenter-install-' . uniqid('', true);
        $packageDir = $tempDir . '/package';
        if (!is_dir($packageDir) && !mkdir($packageDir, 0777, true) && !is_dir($packageDir)) {
            throw new RuntimeException('No se pudo crear el directorio temporal del paquete');
        }

        $configPath = $packageDir . '/config.json';
        file_put_contents($configPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $projectRoot = dirname(__DIR__, 2);
        $scriptPath = $projectRoot . '/resources/windows/install.ps1';
        $uninstallScriptPath = $projectRoot . '/resources/windows/uninstall.ps1';

        $installerCmdPath = $projectRoot . '/resources/windows/Instalar BackupCenter.cmd';
        $uninstallerCmdPath = $projectRoot . '/resources/windows/Desinstalar BackupCenter.cmd';
        $workerScriptPath = $projectRoot . '/resources/windows/worker-test.cmd';
        $serviceScriptPath = $projectRoot . '/resources/windows/service-install.cmd';
        $serviceRemoveScriptPath = $projectRoot . '/resources/windows/service-remove.cmd';
        $checkConfigPath = $projectRoot . '/resources/windows/check-config.php';
        $authGuidePath = $projectRoot . '/resources/windows/AUTH.md';
        $notificationsGuidePath = $projectRoot . '/resources/windows/NOTIFICATIONS.md';
        $readmePath = $projectRoot . '/resources/windows/README.md';

        $zipPath = $tempDir . '/backupcenter-install-' . (int)$connection['id'] . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo ZIP');
        }

        $zip->addFile($configPath, 'config.json');
        $zip->addFile($scriptPath, 'windows/install.ps1');
        $zip->addFile($uninstallScriptPath, 'windows/uninstall.ps1');
        $zip->addFile($installerCmdPath, 'Instalar BackupCenter.cmd');
        $zip->addFile($uninstallerCmdPath, 'Desinstalar BackupCenter.cmd');
        
        $zip->addFile($workerScriptPath, 'windows/worker-test.cmd');
        $zip->addFile($serviceScriptPath, 'windows/service-install.cmd');
        $zip->addFile($serviceRemoveScriptPath, 'windows/service-remove.cmd');
        $zip->addFile($checkConfigPath, 'windows/check-config.php');
        $zip->addFile($authGuidePath, 'windows/AUTH.md');
        $zip->addFile($notificationsGuidePath, 'windows/NOTIFICATIONS.md');
        $zip->addFile($readmePath, 'windows/README.md');

        $agentPublishPath = $projectRoot . '/resources/windows/agent';

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($agentPublishPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $localName = 'agent/' . substr($file->getPathname(), strlen($agentPublishPath) + 1);
                $zip->addFile($file->getPathname(), str_replace('\\', '/', $localName));
            }
        }

        $zip->close();

        $notifications->add(
            'info',
            'Instalación generada',
            'Se generó un paquete de instalación para la conexión "' . $connection['name'] . '".'
        );

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="backupcenter-install-' . (int)$connection['id'] . '.zip"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);

        @unlink($configPath);
        @unlink($zipPath);
        @rmdir($packageDir);
        @rmdir($tempDir);
        exit;
    }

    if (($data['action'] ?? '') === 'set-installed') {
        $connection = $repository->get((int)($data['id'] ?? 0));

        if (!$connection) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Conexión no encontrada'
            ]);
            break;
        }

        $installed = !empty($data['installed']);
        $repository->setInstalled((int)$connection['id'], $installed);

        Audit::info(
            'CONNECTIONS',
            'UPDATE_INSTALLATION_STATUS',
            'Estado de instalación de la conexión ' . $connection['name'] . ' actualizado',
            'admin'
        );

        echo json_encode([
            'success' => true,
            'installed' => $installed ? 1 : 0
        ]);
        break;
    }

    $id = $repository->create(

        (int)$data['client_id'],

        $data['name'],

        $data['host'],

        (int)$data['port'],

        $data['username'],

        $data['password'],

        $data['hostkey'] ?? '',

        $data['protocol'] ?? 'SFTP',

        $data['remote_path']

    );

    Audit::info(

        "CONNECTIONS",

        "CREATE",

        "Conexión ".$data['name']." creada",

        "admin"

    );

    $clientName = null;
    if (!empty($data['client_id'])) {
        $clientRepository = new \BackupCenter\Repositories\ClientRepository($db);
        $client = $clientRepository->get((int)$data['client_id']);
        $clientName = $client['business_name'] ?? null;
    }

    $logService->logConnectionEvent(
        'CREATE',
        $data['name'] ?? '',
        $id,
        $clientName,
        !empty($data['client_id']) ? (int)$data['client_id'] : null,
        'admin'
    );

    echo json_encode([

        'success'=>true,

        'id'=>$id

    ]);

break;

case 'PUT':

    $data=json_decode(
        file_get_contents('php://input'),
        true
    );

    $password = trim((string)($data['password'] ?? ''));

    if ($password === '') {
        $existingConnection = $repository->get((int)$data['id']);
        $password = $existingConnection['password'] ?? '';
    }

    $repository->update(

        (int)$data['id'],

        (int)$data['client_id'],

        $data['name'],

        $data['host'],

        (int)$data['port'],

        $data['username'],

        $password,

        $data['hostkey'] ?? '',

        $data['protocol'] ?? 'SFTP',

        $data['remote_path']

    );

    Audit::info(

        "CONNECTIONS",

        "UPDATE",

        "Conexión ".$data['name']." actualizada",

        "admin"

    );

    $clientName = null;
    if (!empty($data['client_id'])) {
        $clientRepository = new \BackupCenter\Repositories\ClientRepository($db);
        $client = $clientRepository->get((int)$data['client_id']);
        $clientName = $client['business_name'] ?? null;
    }

    $logService->logConnectionEvent(
        'UPDATE',
        $data['name'] ?? '',
        (int)$data['id'],
        $clientName,
        !empty($data['client_id']) ? (int)$data['client_id'] : null,
        'admin'
    );

    echo json_encode([

        'success'=>true

    ]);

break;

case 'DELETE':

    $data=json_decode(
        file_get_contents('php://input'),
        true
    );

    $connection = $repository->get((int)$data['id']);

    $repository->delete(

        (int)$data['id']

    );

    Audit::info(

        "CONNECTIONS",

        "DELETE",

        "Conexión ID ".$data['id']." eliminada",

        "admin"

    );

    $clientName = null;
    if ($connection && !empty($connection['client_id'])) {
        $clientRepository = new \BackupCenter\Repositories\ClientRepository($db);
        $client = $clientRepository->get((int)$connection['client_id']);
        $clientName = $client['business_name'] ?? null;
    }

    $logService->logConnectionEvent(
        'DELETE',
        $connection['name'] ?? 'Conexión',
        (int)$data['id'],
        $clientName,
        $connection['client_id'] ?? null,
        'admin'
    );

    echo json_encode([

        'success'=>true

    ]);

break;

default:

    http_response_code(405);

    echo json_encode([

        'success'=>false,

        'message'=>'Método no permitido'

    ]);
}
} catch (Throwable $ex) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $ex->getMessage(),
        'trace' => $ex->getTraceAsString()
    ]);
}
