<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

echo PHP_EOL;
echo "==========================================" . PHP_EOL;
echo " Backup Worker" . PHP_EOL;
echo "==========================================" . PHP_EOL;

while (true) {

    $app
        ->backupWorker()
        ->process();

    sleep(2);
}