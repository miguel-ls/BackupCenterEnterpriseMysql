<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\ConfigurationManager;

$config = new ConfigurationManager(
    __DIR__ . '/../resources/config/config.json'
);

echo "HOST      : " . $config->get('sftp.host') . PHP_EOL;
echo "PORT      : " . $config->get('sftp.port') . PHP_EOL;
echo "USUARIO   : " . $config->get('sftp.username') . PHP_EOL;
echo "HOSTKEY   : " . $config->get('sftp.hostkey') . PHP_EOL;