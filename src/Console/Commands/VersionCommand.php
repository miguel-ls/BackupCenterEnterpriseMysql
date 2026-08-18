<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Console\Command;

class VersionCommand implements Command
{
    public function execute(array $arguments = []): int
    {
        echo "Version: "
            . trim(file_get_contents(dirname(__DIR__, 3) . '/VERSION'))
            . PHP_EOL;

        return 0;
    }
}