<?php

namespace BackupCenter\Services;

use BackupCenter\Core\Paths;
use PDO;

/**
 * Escribe el log de actividad en tiempo real (transferencias y cambios de
 * estado de cola) en un archivo diario de texto plano, tipo tail -f.
 */
class MonitorLogService
{
    // Fija la zona horaria en cada operación: los escritores (worker, cola,
    // transferencias) y los lectores (monitor.php) deben coincidir siempre,
    // sin depender del timezone por defecto del servidor (que puede ser UTC).
    private const TIMEZONE = 'America/Lima';

    private static function today(): string
    {
        date_default_timezone_set(self::TIMEZONE);

        return date('Y-m-d');
    }

    private static function now(): string
    {
        date_default_timezone_set(self::TIMEZONE);

        return date('Y-m-d H:i:s');
    }

    public static function directory(): string
    {
        return Paths::logs() . '/monitor';
    }

    public static function fileForDate(string $date): string
    {
        return self::directory() . "/monitor-{$date}.log";
    }

    public static function todayFile(): string
    {
        return self::fileForDate(self::today());
    }

    /**
     * Contexto común (job, cliente, conexión) para enriquecer cualquier línea del monitor.
     */
    public static function contextForJob(PDO $pdo, int $jobId): array
    {
        $stmt = $pdo->prepare("
            SELECT
                j.id AS job_id,
                j.name AS job_name,
                c.id AS connection_id,
                c.name AS connection_name,
                COALESCE(cl.business_name, '-') AS client_name
            FROM jobs j
            LEFT JOIN connections c ON c.id = j.connection_id
            LEFT JOIN clients cl ON cl.id = c.client_id
            WHERE j.id = ?
        ");

        $stmt->execute([$jobId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['job_id' => $jobId];
    }

    public static function contextForConnection(PDO $pdo, int $connectionId): array
    {
        $stmt = $pdo->prepare("
            SELECT
                c.id AS connection_id,
                c.name AS connection_name,
                COALESCE(cl.business_name, '-') AS client_name
            FROM connections c
            LEFT JOIN clients cl ON cl.id = c.client_id
            WHERE c.id = ?
        ");

        $stmt->execute([$connectionId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['connection_id' => $connectionId];
    }

    public static function log(
        string $type,
        string $message,
        array $context = []
    ): void
    {
        $dir = self::directory();

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        // Orden fijo de columnas para mantener una lectura uniforme del log.
        $orderedKeys = [
            'client_name',
            'connection_id',
            'connection_name',
            'job_id',
            'job_name'
        ];

        $ordered = [];

        foreach ($orderedKeys as $key) {
            $ordered[$key] = $context[$key] ?? null;
            unset($context[$key]);
        }

        $context = $ordered + $context;

        $parts = [];

        foreach ($context as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $parts[] = "{$key}=\"{$value}\"";
        }

        $line = sprintf(
            '%s | %-9s | %s | %s' . PHP_EOL,
            self::now(),
            strtoupper($type),
            implode(' ', $parts),
            $message
        );

        $file = self::todayFile();
        $handle = @fopen($file, 'a');

        if ($handle === false) {
            // No se pudo abrir el archivo (permisos, disco, etc.): se deja constancia
            // en el log de errores de PHP en vez de perder el evento en silencio.
            error_log("MonitorLogService: no se pudo escribir en {$file} (revisar permisos/propietario del archivo o carpeta)");
            return;
        }

        if (flock($handle, LOCK_EX)) {
            fwrite($handle, $line);
            flock($handle, LOCK_UN);
        }

        fclose($handle);
    }
}
