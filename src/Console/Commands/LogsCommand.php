<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Core\LogReader;
use BackupCenter\Core\Paths;

class LogsCommand
{
    public function execute(): int
    {
        echo PHP_EOL;
        echo "Últimos registros" . PHP_EOL;
        echo "----------------------------------------" . PHP_EOL;

        $reader = new LogReader(Paths::logs());

        $lines = $reader->latest();

        if (empty($lines)) {

            echo "No existen registros." . PHP_EOL;

            return 0;
        }

        foreach ($lines as $line) {
            echo $line . PHP_EOL;
        }

        return 0;
    }
}