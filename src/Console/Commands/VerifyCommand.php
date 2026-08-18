<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Core\Application;
use BackupCenter\Core\Paths;

class VerifyCommand
{
    public function execute(Application $app): int
    {
        echo PHP_EOL;
        echo "Verificación del sistema" . PHP_EOL;
        echo "----------------------------------------" . PHP_EOL;

        $config = $app->config();

        $checks = [

            'Configuración' =>
                file_exists(Paths::config() . '/config.json'),

            'Carpeta backups' =>
                is_dir($config->get('backup.local_path')),

            'Carpeta logs' =>
                is_dir(Paths::logs()),

            'Base de datos' =>
                file_exists(Paths::database() . '/backupcenter.db'),

            'WinSCP' =>
                file_exists($config->get('winscp.executable')),

            'Escritura Logs' =>
                is_writable(Paths::logs()),
        ];

        foreach ($checks as $name => $result) {

            echo sprintf(
                "[%s] %s",
                $result ? "OK" : "ERROR",
                $name
            ) . PHP_EOL;
        }

        return 0;
    }
}