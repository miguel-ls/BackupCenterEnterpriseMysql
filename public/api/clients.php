<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Audit;
use BackupCenter\Services\JobLogService;
use BackupCenter\Services\SftpGoService;

$app = new Application();

$repository = $app->clientRepository();
$logService = new JobLogService();

$method = $_SERVER['REQUEST_METHOD'];

$body = json_decode(
    file_get_contents('php://input'),
    true
);

if (!is_array($body)) {
    $body = [];
}

switch ($method) {

    case 'GET':

        echo json_encode([
            'success' => true,
            'data' => $repository->getAll()
        ]);

        break;

    case 'POST':

        if (($body['action'] ?? '') === 'reset-password') {

            try {

                $client = null;

                foreach ($repository->getAll() as $row) {

                    if ((int)$row['id'] === (int)$body['id']) {
                        $client = $row;
                        break;
                    }

                }

                if (!$client) {
                    throw new Exception('Cliente no encontrado.');
                }

                if (empty($client['sftp_alias'])) {
                    throw new Exception('El cliente no tiene Alias SFTP.');
                }

                $service = new SftpGoService();

                $result = $service->resetPassword(
                    $client['sftp_alias']
                );

                Audit::info(
                    'CLIENTS',
                    'RESET_PASSWORD',
                    'Contraseña SFTP restablecida para ' . $client['business_name'],
                    'admin'
                );

                echo json_encode([
                    'success' => true,
                    'username' => $result['username'],
                    'password' => $result['password']
                ]);

                break;

            } catch (Throwable $e) {

                http_response_code(400);

                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);

                break;

            }

        }

        $pdo = $app->database()->getConnection();

        try {

            $pdo->beginTransaction();

            $id = $repository->create(

                $body['code'] ?? '',

                $body['business_name'] ?? '',

                $body['trade_name'] ?? null,

                $body['sftp_alias'] ?? null,

                $body['ruc'] ?? null,

                $body['contact_name'] ?? null,

                $body['email'] ?? null,

                $body['phone'] ?? null,

                $body['address'] ?? null,

                (int)($body['status'] ?? 1),

                $body['notes'] ?? null

            );

            $sftp = null;

            $service = new SftpGoService();

            if ($service->isEnabled()) {

                if (empty($body['sftp_alias'])) {

                    throw new Exception(
                        'Debe ingresar el Alias SFTP.'
                    );

                }

                $sftp = $service->provisionClient(
                    $body['sftp_alias']
                );

            }

            Audit::info(

                'CLIENTS',

                'CREATE',

                'Cliente ' . ($body['business_name'] ?? '') . ' creado',

                'admin'

            );

            $logService->logClientEvent(
                'CREATE',
                $body['business_name'] ?? '',
                $id,
                'admin'
            );

            $pdo->commit();

            echo json_encode([

                'success' => true,

                'id' => $id,

                'sftp' => $sftp

            ]);

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();

            }

            http_response_code(400);

            echo json_encode([

                'success' => false,

                'message' => $e->getMessage()

            ]);

        }

        break;

    case 'PUT':

        try {

            $ok = $repository->update(

                (int)$body['id'],

                $body['code'] ?? '',

                $body['business_name'] ?? '',

                $body['trade_name'] ?? null,

                $body['sftp_alias'] ?? null,

                $body['ruc'] ?? null,

                $body['contact_name'] ?? null,

                $body['email'] ?? null,

                $body['phone'] ?? null,

                $body['address'] ?? null,

                (int)($body['status'] ?? 1),

                $body['notes'] ?? null

            );

            Audit::info(

                'CLIENTS',

                'UPDATE',

                'Cliente ' . ($body['business_name'] ?? '') . ' actualizado',

                'admin'

            );

            $logService->logClientEvent(
                'UPDATE',
                $body['business_name'] ?? '',
                (int)$body['id'],
                'admin'
            );

            echo json_encode([
                'success' => $ok
            ]);

        } catch (PDOException $e) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => str_contains($e->getMessage(), 'clients.sftp_alias')
                    ? 'El Alias SFTP ya existe.'
                    : 'Error al actualizar el cliente.'
            ]);

        }

        break;

    case 'DELETE':




        try {

            $client = null;

            foreach ($repository->getAll() as $row) {

                if ((int)$row['id'] === (int)$body['id']) {

                    $client = $row;

                    break;

                }

            }

            if (!$client) {

                throw new Exception('Cliente no encontrado.');

            }

            $service = new SftpGoService();

            $sftpDeleted = false;
            $folderDeleted = false;

            if (
                $service->isEnabled() &&
                !empty($client['sftp_alias'])
            ) {

                $sftpDeleted = $service->deleteUser(
                    $client['sftp_alias']
                );



                $folderDeleted = $service->deleteClientFolder(
                    $client['sftp_alias']
                );

            }

            $ok = $repository->delete(
                (int)$body['id']
            );

            Audit::info(

                'CLIENTS',

                'DELETE',

                'Cliente ID ' . ($body['id'] ?? 0) . ' eliminado',

                'admin'

            );

            $logService->logClientEvent(
                'DELETE',
                $client['business_name'] ?? 'Cliente',
                (int)$body['id'],
                'admin'
            );

            echo json_encode([
                'success' => $ok,
                'sftp_deleted' => $sftpDeleted,
                'folder_deleted' => $folderDeleted
            ]);

        } catch (Throwable $e) {

            http_response_code(400);

            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);

        }

        break;   

    default:

        http_response_code(405);

        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);

}