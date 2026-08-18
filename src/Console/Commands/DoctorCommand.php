<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Console\Command;
use BackupCenter\Core\Paths;

class DoctorCommand implements Command
{
    public function execute(array $arguments = []): int
    {
        echo "Diagnóstico del sistema" . PHP_EOL;
        echo "------------------------------" . PHP_EOL;

        $checks = [
            [
                'PHP',
                version_compare(PHP_VERSION, '8.2.0', '>='),
                PHP_VERSION
            ],
            [
                'SQLite',
                extension_loaded('pdo_sqlite'),
                ''
            ],
            [
                'WinSCP',
                file_exists("C:\\Program Files (x86)\\WinSCP\\WinSCP.com"),
                ''
            ],
            [
                'Config',
                file_exists(Paths::config() . '/config.json'),
                ''
            ],
            [
                'Database',
                file_exists(Paths::database() . '/backupcenter.db'),
                ''
            ],
            [
                'Logs',
                is_writable(Paths::logs()),
                ''
            ],
            [
                'Temp',
                is_writable(Paths::temp()),
                ''
            ]
        ];

        $hasErrors = false;

        foreach ($checks as $check) {

            if (!$check[1]) {
                $hasErrors = true;
            }

            echo sprintf(
                "[%s] %-12s %s",
                $check[1] ? "OK" : "ERROR",
                $check[0],
                $check[2]
            ) . PHP_EOL;
        }

        return $hasErrors ? 1 : 0;
    }
}