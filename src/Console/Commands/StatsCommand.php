<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Core\Application;

class StatsCommand
{
    public function execute(Application $app): int
    {
        $stats = $app
            ->executionHistoryRepository()
            ->statistics();

        echo PHP_EOL;
        echo "Backup Center Statistics" . PHP_EOL;
        echo "----------------------------------------" . PHP_EOL;

        echo "Total ejecuciones : {$stats['total_runs']}" . PHP_EOL;
        echo "OK                : {$stats['success_runs']}" . PHP_EOL;
        echo "ERROR             : {$stats['error_runs']}" . PHP_EOL;
        echo "Archivos subidos  : {$stats['uploaded']}" . PHP_EOL;
        echo "Archivos omitidos : {$stats['skipped']}" . PHP_EOL;
        echo "Tiempo promedio   : {$stats['average_duration']} s" . PHP_EOL;

        return 0;
    }
}