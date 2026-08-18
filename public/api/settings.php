<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;
use BackupCenter\Core\Audit;
use BackupCenter\Core\Auth;
use BackupCenter\Services\JobLogService;

Auth::require();

$app = new Application();
$db = $app->database();

$pdo = $db->getConnection();
$logService = new JobLogService();

/*==================================================
ACTUALIZAR ESTRUCTURA SETTINGS
==================================================*/

$columns = [];

$stmt = $pdo->query("PRAGMA table_info(settings)");

if ($stmt) {

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $column) {
        $columns[$column['name']] = true;
    }

}

$newColumns = [

    "sftpgo_enabled"   => "ALTER TABLE settings ADD COLUMN sftpgo_enabled INTEGER DEFAULT 0",

    "sftpgo_protocol"  => "ALTER TABLE settings ADD COLUMN sftpgo_protocol TEXT DEFAULT 'http'",

    "sftpgo_host"      => "ALTER TABLE settings ADD COLUMN sftpgo_host TEXT",

    "sftpgo_port"      => "ALTER TABLE settings ADD COLUMN sftpgo_port INTEGER DEFAULT 8088",

    "sftpgo_username"   => "ALTER TABLE settings ADD COLUMN sftpgo_username TEXT",

    "sftpgo_password"   => "ALTER TABLE settings ADD COLUMN sftpgo_password TEXT",

    "sftpgo_base_path" => "ALTER TABLE settings ADD COLUMN sftpgo_base_path TEXT DEFAULT '/mnt/Interno1TB/BackupsSQL'"

];

foreach ($newColumns as $column => $sql) {

    if (!isset($columns[$column])) {
        $pdo->exec($sql);
    }

}

/*==================================================
TABLA
==================================================*/

$pdo->exec("
CREATE TABLE IF NOT EXISTS settings(

    id INTEGER PRIMARY KEY CHECK(id=1),

    scheduler_interval INTEGER DEFAULT 60,

    max_threads INTEGER DEFAULT 4,

    retry_count INTEGER DEFAULT 3,

    retention_days INTEGER DEFAULT 30,

    compression INTEGER DEFAULT 1,

    log_level TEXT DEFAULT 'INFO',

    log_path TEXT DEFAULT 'resources/logs',

    connection_timeout INTEGER DEFAULT 30

)
");

/*==================================================
REGISTRO INICIAL
==================================================*/

$count=$pdo->query("
SELECT COUNT(*)
FROM settings
")->fetchColumn();

if($count==0){

    $pdo->exec("
    INSERT INTO settings(id)
    VALUES(1)
    ");

}

/*==================================================
GET
==================================================*/

if($_SERVER['REQUEST_METHOD']=="GET"){

    $stmt=$pdo->query("
    SELECT *
    FROM settings
    WHERE id=1
    ");

    echo json_encode([

        "success"=>true,

        "data"=>$stmt->fetch(PDO::FETCH_ASSOC)

    ]);

    exit;

}

/*==================================================
PUT
==================================================*/

if($_SERVER['REQUEST_METHOD']=="PUT"){

    $data=json_decode(
        file_get_contents("php://input"),
        true
    );

    $stmt=$pdo->prepare("

    UPDATE settings
    SET
        scheduler_interval=?,
        max_threads=?,
        retry_count=?,
        retention_days=?,
        compression=?,
        log_level=?,
        log_path=?,
        connection_timeout=?,

        sftpgo_enabled=?,
        sftpgo_protocol=?,
        sftpgo_host=?,
        sftpgo_port=?,
        sftpgo_username=?,
        sftpgo_password=?,
        sftpgo_base_path=?

    WHERE id=1

    ");

    $stmt->execute([

        $data["scheduler_interval"],
        $data["max_threads"],
        $data["retry_count"],
        $data["retention_days"],
        $data["compression"],
        $data["log_level"],
        $data["log_path"],
        $data["connection_timeout"],

        $data["sftpgo_enabled"],
        $data["sftpgo_protocol"],
        $data["sftpgo_host"],
        $data["sftpgo_port"],
        $data["sftpgo_username"],
        $data["sftpgo_password"],
        $data["sftpgo_base_path"]        

    ]);

    Audit::info(

        "SETTINGS",

        "UPDATE",

        "Configuración modificada",

        "admin"

    );

    $logService->logSettingsEvent(
        'UPDATE',
        'Configuración modificada',
        1,
        'admin'
    );

    echo json_encode([

        "success"=>true,

        "message"=>"Configuración actualizada."

    ]);

    exit;

}

echo json_encode([

    "success"=>false,

    "message"=>"Método no permitido"

]);