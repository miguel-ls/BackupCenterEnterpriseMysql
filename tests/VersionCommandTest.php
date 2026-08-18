<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Console\Commands\VersionCommand;

$command = new VersionCommand();

$exitCode = $command->execute();

echo "Exit Code: {$exitCode}" . PHP_EOL;