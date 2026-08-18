<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Console\Command;
use BackupCenter\Core\Paths;

class StatusCommand implements Command
{
    public function execute(array $arguments = []): int
    {
        echo "Estado del sistema" . PHP_EOL;
        echo "------------------------------" . PHP_EOL;

        echo "PHP          : " . PHP_VERSION . PHP_EOL;

        echo "SQLite       : "
            . (extension_loaded('pdo_sqlite') ? "OK" : "ERROR")
            . PHP_EOL;

        echo "WinSCP       : "
            . (file_exists("C:\\Program Files (x86)\\WinSCP\\WinSCP.com") ? "OK" : "ERROR")
            . PHP_EOL;

        echo "Config       : "
            . (file_exists(Paths::config() . '/config.json') ? "OK" : "ERROR")
            . PHP_EOL;

        echo "Database     : "
            . (file_exists(Paths::database() . '/backupcenter.db') ? "OK" : "ERROR")
            . PHP_EOL;

        echo "Logs         : "
            . (is_dir(Paths::logs()) ? "OK" : "ERROR")
            . PHP_EOL;

        return 0;
    }
}