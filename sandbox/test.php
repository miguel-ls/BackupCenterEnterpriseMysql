<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\ConfigurationManager;
use BackupCenter\Core\Logger;

try {

    $config = new ConfigurationManager(
        __DIR__ . '/../resources/config/config.json'
    );

    $logger = new Logger(
        __DIR__ . '/../resources/logs'
    );

    $logger->info('Backup Center Enterprise iniciado.');
    $logger->info('Cliente: ' . $config->get('client.code'));
    $logger->success('Logger funcionando correctamente.');

    echo "Prueba finalizada correctamente." . PHP_EOL;

} catch (Exception $e) {

    echo $e->getMessage();

}