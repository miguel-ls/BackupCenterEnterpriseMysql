<?php

header('Content-Type: application/json');


require_once __DIR__ . '/bootstrap.php';

use BackupCenter\Core\Application;
use BackupCenter\Services\WindowsServiceMonitor;
use BackupCenter\Core\Paths;

$app = new Application();

$monitor = new WindowsServiceMonitor();

$checks = [];

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
*/

$checks[] = [
    "name" => "Scheduler",
    "ok" => $monitor->getStatus("BackupCenterScheduler") === "Activo"
];

/*
|--------------------------------------------------------------------------
| Worker
|--------------------------------------------------------------------------
*/

$checks[] = [
    "name" => "Worker",
    "ok" => $monitor->getStatus("BackupCenterWorker") === "Activo"
];

/*
|--------------------------------------------------------------------------
| SQLite
|--------------------------------------------------------------------------
*/


$db = Paths::database() . '/backupcenter.db';

$checks[] = [
    "name"=>"SQLite",
    "ok"=>file_exists($db)
];

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

$checks[] = [
    "name"=>"API",
    "ok"=>true
];

/*
|--------------------------------------------------------------------------
| Repositorio
|--------------------------------------------------------------------------
*/

$repo = dirname(__DIR__,2)."/storage";

$checks[] = [
    "name"=>"Repositorio",
    "ok"=>is_dir($repo)
];

/*
|--------------------------------------------------------------------------
| Internet
|--------------------------------------------------------------------------
*/

$internet = @fsockopen("8.8.8.8",53,$errno,$errstr,2);

$checks[] = [
    "name"=>"Internet",
    "ok"=>$internet!==false
];

if($internet){
    fclose($internet);
}

$total = count($checks);

$ok = count(array_filter($checks,function($x){

    return $x["ok"];

}));

echo json_encode([

    "success"=>true,

    "data"=>[

        "items"=>$checks,

        "score"=>round($ok/$total*100)

    ]

]);