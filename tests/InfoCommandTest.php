<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Console\Commands\InfoCommand;

$command = new InfoCommand();

$exitCode = $command->execute();

echo PHP_EOL;
echo "Exit Code: {$exitCode}" . PHP_EOL;