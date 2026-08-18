<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Console\Command;
use BackupCenter\Core\ConfigurationManager;
use BackupCenter\Core\Paths;

class InfoCommand implements Command
{
    public function execute(array $arguments = []): int
    {
        $config = new ConfigurationManager(
            Paths::config() . '/config.json'
        );

        echo "Información del proyecto" . PHP_EOL;
        echo "------------------------------" . PHP_EOL;

        echo "Versión        : "
            . trim(file_get_contents(Paths::root() . '/VERSION'))
            . PHP_EOL;

        echo "Cliente        : "
            . $config->get('client.name')
            . PHP_EOL;

        echo "PHP            : "
            . PHP_VERSION
            . PHP_EOL;

        echo "Ruta backups   : "
            . $config->get('backup.local_path')
            . PHP_EOL;

        echo "Extensiones    : "
            . implode(', ', $config->get('backup.extensions', []))
            . PHP_EOL;

        echo "Logs           : "
            . Paths::logs()
            . PHP_EOL;

        echo "Base de datos  : "
            . Paths::database() . '/backupcenter.db'
            . PHP_EOL;

        return 0;
    }
}