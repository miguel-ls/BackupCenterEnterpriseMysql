<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use BackupCenter\Services\SftpGoService;

try {

    $service = new SftpGoService();

    $password = $service->generatePassword();

    $result = $service->createUser(

        "prueba_api",

        $password

    );

    echo json_encode([

        "success" => true,

        "password" => $password,

        "result" => $result

    ]);

} catch (Throwable $e) {

    echo json_encode([

        "success" => false,

        "message" => $e->getMessage()

    ]);

}