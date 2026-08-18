<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\ReportRepository;
use BackupCenter\Services\ExportExcelService;
use BackupCenter\Services\ExportPdfService;

Auth::require();

$app = new Application();

$repository = new ReportRepository(
    $app->database()
);

$type = $_GET['type'] ?? 'excel';

$action = $_GET['action'] ?? 'clients';

switch ($action) {

    case 'clients':

        $rows = $repository->clients();

        $headers = [

            'Cliente',
            'Ejecuciones',
            'Archivos Subidos',
            'Errores'

        ];

        $data = [];

        foreach ($rows as $row) {

            $data[] = [

                $row['client'],
                $row['executions'],
                $row['uploaded'],
                $row['errors']

            ];

        }

        $title = 'Reporte de Clientes';

        break;

    case 'jobs':

        $rows = $repository->jobs();

        $headers = [

            'Trabajo',
            'Estado',
            'Última ejecución'

        ];

        $data = [];

        foreach ($rows as $row) {

            $data[] = [

                $row['name'],
                $row['last_status'],
                $row['last_run']

            ];

        }

        $title = 'Reporte de Trabajos';

        break;

    case 'connections':

        $rows = $repository->connections();

        $headers = [

            'Nombre',
            'Protocolo',
            'Host',
            'Ruta Remota'

        ];

        $data = [];

        foreach ($rows as $row) {

            $data[] = [

                $row['name'],
                $row['protocol'],
                $row['host'],
                $row['remote_path']

            ];

        }

        $title = 'Reporte de Conexiones';

        break;

    case 'daily':

        $rows = $repository->daily(30);

        $headers = [

            'Fecha',
            'Ejecuciones',
            'Archivos Subidos',
            'Errores'

        ];

        $data = [];

        foreach ($rows as $row) {

            $data[] = [

                $row['day'],
                $row['executions'],
                $row['uploaded'],
                $row['errors']

            ];

        }

        $title = 'Reporte de Actividad Diaria';

        break;

    case 'errors':

        $rows = $repository->errors();

        $headers = [

            'Fecha',
            'Cliente',
            'Archivos Encontrados',
            'Archivos Subidos',
            'Errores',
            'DuraciÃ³n (s)',
            'Estado'

        ];

        $data = [];

        foreach ($rows as $row) {

            $data[] = [

                $row['started_at'],
                $row['client'],
                $row['files_found'],
                $row['files_uploaded'],
                $row['errors'],
                $row['duration'],
                $row['status']

            ];

        }

        $title = 'Reporte de Errores';

        break;

    case 'errors':

        $rows = $repository->errors();

        $headers = [

            'Fecha',
            'Cliente',
            'Archivos Encontrados',
            'Archivos Subidos',
            'Errores',
            'DuraciÃ³n (s)',
            'Estado'

        ];

        $data = [];

        foreach ($rows as $row) {

            $data[] = [

                $row['started_at'],
                $row['client'],
                $row['files_found'],
                $row['files_uploaded'],
                $row['errors'],
                $row['duration'],
                $row['status']

            ];

        }

        $title = 'Reporte de Errores';

        break;

    default:

        http_response_code(404);

        echo json_encode([

            "success" => false,

            "message" => "Acción no válida."

        ]);

        exit;

}

if ($type === 'pdf') {

    $pdf = new ExportPdfService();

    $pdf->download(

        $title,

        $headers,

        $data,

        str_replace(' ', '_', $title) . "_" . date("Ymd_His")

    );

}

$excel = new ExportExcelService();

$excel->download(

    $title,

    $headers,

    $data,

    str_replace(' ', '_', $title) . "_" . date("Ymd_His")

);
