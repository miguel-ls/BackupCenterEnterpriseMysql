<?php

namespace BackupCenter\Console\Commands;

use BackupCenter\Core\Application;

class HistoryCommand
{
    public function execute(Application $app): int
    {
        echo PHP_EOL;
        echo "Últimas ejecuciones" . PHP_EOL;
        echo "----------------------------------------" . PHP_EOL;

        $rows = $app
            ->executionHistoryRepository()
            ->latest();

        if (empty($rows)) {
            echo "No existen registros." . PHP_EOL;
            return 0;
        }

        foreach ($rows as $row) {

            echo "ID          : {$row['id']}" . PHP_EOL;
            echo "Fecha       : {$row['started_at']}" . PHP_EOL;
            echo "Cliente     : {$row['client']}" . PHP_EOL;
            echo "Encontrados : {$row['files_found']}" . PHP_EOL;
            echo "Subidos     : {$row['files_uploaded']}" . PHP_EOL;
            echo "Omitidos    : {$row['files_skipped']}" . PHP_EOL;
            echo "Errores     : {$row['errors']}" . PHP_EOL;
            echo "Duración    : {$row['duration']} s" . PHP_EOL;
            echo "Estado      : {$row['status']}" . PHP_EOL;
            echo "----------------------------------------" . PHP_EOL;
        }

        return 0;
    }
}