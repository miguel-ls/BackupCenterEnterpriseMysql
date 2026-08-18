<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\ConfigurationManager;

$config = new ConfigurationManager(
    __DIR__ . '/../resources/config/config.json'
);

$connection = $config->getConnectionConfig();

echo "HOST      : " . $connection->getHost() . PHP_EOL;
echo "PORT      : " . $connection->getPort() . PHP_EOL;
echo "USUARIO   : " . $connection->getUsername() . PHP_EOL;
echo "PASSWORD  : " . $connection->getPassword() . PHP_EOL;
echo "HOSTKEY   : " . $connection->getHostKey() . PHP_EOL;