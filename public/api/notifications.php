<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use BackupCenter\Repositories\NotificationRepository;

Auth::require();

$db = new Database(
    Paths::database() . '/backupcenter.db'
);

$repository = new NotificationRepository($db);

switch ($_SERVER['REQUEST_METHOD']) {

case 'GET':

    echo json_encode([

        'success' => true,

        'data' => $repository->getAll(),

        'unread' => $repository->countUnread()

    ]);

break;

case 'PUT':

    $repository->markAllAsRead();

    Audit::info(

        "NOTIFICATIONS",

        "MARK_ALL_READ",

        "Todas las notificaciones fueron marcadas como leídas",

        "admin"

    );

    echo json_encode([

        'success'=>true

    ]);

break;

case 'DELETE':

    $repository->clear();

    Audit::info(

        "NOTIFICATIONS",

        "CLEAR",

        "Se eliminaron todas las notificaciones",

        "admin"

    );

    echo json_encode([

        'success'=>true

    ]);

break;

default:

    http_response_code(405);

    echo json_encode([

        'success'=>false,

        'message'=>'Método no permitido.'

    ]);

}