<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

$result = $app
    ->agent()
    ->run();

echo PHP_EOL;

echo "Success : " . ($result->success ? "SI" : "NO") . PHP_EOL;
echo "Found   : " . $result->found . PHP_EOL;
echo "Uploaded: " . $result->uploaded . PHP_EOL;
echo "Skipped : " . $result->skipped . PHP_EOL;
echo "Errors  : " . $result->errors . PHP_EOL;