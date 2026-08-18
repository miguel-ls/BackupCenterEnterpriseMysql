<?php
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\JobRepository;
use BackupCenter\Repositories\JobQueueRepository;
use BackupCenter\Services\JobLogService;


Auth::require();

$app = new Application();

$db = $app->database();

$repository = new JobRepository($db);

$queue = new JobQueueRepository($db);

$queue->initialize();

$jobLogService = new JobLogService();

$resolveJobContext = static function (?array $job) use ($app): array {
    $connectionName = null;
    $clientName = null;

    if ($job && !empty($job['connection_id'])) {
        $connection = $app->connectionRepository()->get((int)$job['connection_id']);

        if ($connection) {
            $connectionName = $connection['name'] ?? null;

            if (!empty($connection['client_id'])) {
                $client = $app->clientRepository()->get((int)$connection['client_id']);
                $clientName = $client['business_name'] ?? null;
            }
        }
    }

    return [$connectionName, $clientName];
};

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
            'data' => $repository->getJobs()
        ]);

        break;

    case 'POST':

        /*
        |--------------------------------------------------------------------------
        | Ejecutar trabajo
        |--------------------------------------------------------------------------
        */

        if (($body['action'] ?? '') === 'run') {

            $job = $repository->getJob((int)$body['id']);

            if (!$job || (int)($job['enabled'] ?? 1) !== 1) {
                echo json_encode([
                    "success" => false,
                    "message" => "El trabajo está deshabilitado. Habilítelo para ejecutar una cola pendiente."
                ]);

                exit;
            }

            $ok = $queue->enqueue(
                (int)$body['id']
            );

            if ($ok) {

                [$connectionName, $clientName] = $resolveJobContext($job);

                Audit::info(
                    "JOBS",
                    "QUEUE",
                    "Trabajo: {$job['name']} agregado a la cola. | Cliente: {$clientName} | Conexión: {$connectionName} ",
                    "admin"
                );

                $jobLogService->logQueue(
                    (int)$body['id'],
                    $job['name'] ?? null,
                    $job['connection_id'] ?? null,
                    'admin',
                    $connectionName,
                    $clientName
                );

                echo json_encode([
                    "success" => true,
                    "message" => "Trabajo agregado a la cola."
                ]);

            } else {

                echo json_encode([
                    "success" => false,
                    "message" => "El trabajo ya estaba en cola o ejecutándose."
                ]);

            }

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Crear trabajo
        |--------------------------------------------------------------------------
        */

        $enabled = !empty($body['enabled']) ? 1 : 1;

        $id = $repository->createJob(

            !empty($body['connection_id'])
                ? (int)$body['connection_id']
                : null,

            $body['name'] ?? '',

            $body['source'] ?? '',

            $body['destination'] ?? '',

            $body['schedule'] ?? '',
            $enabled

        );

        Audit::info(
            "JOBS",
            "CREATE",
            "Trabajo " . ($body['name'] ?? '') . " creado",
            "admin"
        );

        if ($id > 0) {
            [$connectionName, $clientName] = $resolveJobContext([
                'connection_id' => !empty($body['connection_id']) ? (int)$body['connection_id'] : null
            ]);

            $jobLogService->logCreate(
                $body['name'] ?? '',
                $id,
                !empty($body['connection_id']) ? (int)$body['connection_id'] : null,
                null,
                'admin',
                $connectionName,
                $clientName
            );
        }

        echo json_encode([
            "success" => true,
            "id" => $id
        ]);

        break;

    case 'PUT':

        $enabled = !empty($body['enabled']) ? 1 : 1;

        $ok = $repository->updateJob(

            (int)$body['id'],

            !empty($body['connection_id'])
                ? (int)$body['connection_id']
                : null,

            $body['name'] ?? '',

            $body['source'] ?? '',

            $body['destination'] ?? '',

            $body['schedule'] ?? '',
            $enabled

        );

        Audit::info(
            "JOBS",
            "UPDATE",
            "Trabajo " . ($body['name'] ?? '') . " actualizado",
            "admin"
        );

        if ($ok) {
            [$connectionName, $clientName] = $resolveJobContext([
                'connection_id' => !empty($body['connection_id']) ? (int)$body['connection_id'] : null
            ]);

            $jobLogService->logUpdate(
                $body['name'] ?? '',
                (int)$body['id'],
                !empty($body['connection_id']) ? (int)$body['connection_id'] : null,
                null,
                'admin',
                $connectionName,
                $clientName
            );
        }

        echo json_encode([
            "success" => $ok
        ]);

        break;

    case 'DELETE':

        $ok = $repository->deleteJob(
            (int)$body['id']
        );

        Audit::info(
            "JOBS",
            "DELETE",
            "Trabajo {$body['id']} eliminado",
            "admin"
        );

        if ($ok) {
            $job = $repository->getJob((int)$body['id']);
            [$connectionName, $clientName] = $resolveJobContext($job);

            $jobLogService->logDelete(
                $job['name'] ?? (string)$body['id'],
                (int)$body['id'],
                'admin',
                $connectionName,
                $clientName
            );
        }

        echo json_encode([
            "success" => $ok
        ]);

        break;

    case 'PATCH':

        $enabled = !empty($body['enabled']);

        $ok = $repository->setEnabled(
            (int)$body['id'],
            $enabled
        );

        echo json_encode([
            "success" => $ok,
            "message" => $enabled
                ? "Trabajo habilitado."
                : "Trabajo deshabilitado."
        ]);

        break;

    default:

        http_response_code(405);

        echo json_encode([
            "success" => false,
            "message" => "Método no permitido"
        ]);
}