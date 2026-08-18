<?php

require_once __DIR__ . '/cors.php';

require_once __DIR__ . '/bootstrap.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Paths;
use BackupCenter\Repositories\ExecutionHistoryRepository;
use BackupCenter\Core\Auth;


Auth::require();

$app = new Application();



$history = new ExecutionHistoryRepository(
    $app->database()
);

$db = Paths::database() . '/backupcenter.db';

$internet = @fsockopen("8.8.8.8",53,$errno,$errstr,2);

$data=[

    "status"=>[

        [
            "name"=>"API",
            "ok"=>true
        ],

        [
            "name"=>"SQLite",
            "ok"=>file_exists($db)
        ],

        [
            "name"=>"Internet",
            "ok"=>$internet!==false
        ]

    ],

    "events"=>[],

    "alerts"=>[]

];

if($internet){
    fclose($internet);
}

$last=$history->latest(6);

foreach ($last as $row) {

    $data["events"][] = [

        "title"  => "Backup " . $row["client_name"],

        "status" => $row["status"],

        "date"   => $row["started_at"]

    ];

}

if(!file_exists($db)){
    $data["alerts"][] = "Base de datos SQLite no encontrada";
}

if($internet === false){
    $data["alerts"][] = "Sin conexión a Internet";
}

echo json_encode([

    "success"=>true,

    "data"=>$data

]);