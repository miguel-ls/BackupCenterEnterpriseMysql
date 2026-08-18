<?php

namespace BackupCenter\Core;

/**
 * Ejecuta comandos externos (PowerShell, WinSCP, etc.) con un timeout real.
 *
 * shell_exec() no tiene forma de limitarse en el tiempo: si el proceso
 * externo se cuelga o tarda demasiado, PHP se queda esperando para
 * siempre. Como el servidor de PHP normalmente atiende una petición a
 * la vez, un solo proceso colgado bloquea toda la aplicación (Dashboard,
 * API, todo queda "Pending").
 *
 * Esta clase usa proc_open + polling para poder matar el proceso (y sus
 * hijos) si se pasa del tiempo límite.
 */
class ProcessRunner
{
    /**
     * @param array<int, string> $command Comando y argumentos, como array
     *                                     (no se pasa por el shell, evita
     *                                     problemas de escaping y permite
     *                                     matar el proceso real en Windows).
     *
     * @return array{output:string, error:string, timedOut:bool, exitCode:?int}
     */
    public static function run(array $command, int $timeoutSeconds = 10): array
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $options = [];

        if (self::isWindows()) {
            // Evita que proc_open envuelva el comando en cmd.exe; así el
            // pid que obtenemos es el del proceso real (powershell.exe,
            // WinSCP.com) y podemos matarlo de forma confiable.
            $options['bypass_shell'] = true;
        }

        $process = @proc_open($command, $descriptors, $pipes, null, null, $options);

        if (!\is_resource($process)) {
            return [
                'output' => '',
                'error' => 'No se pudo iniciar el proceso: ' . implode(' ', $command),
                'timedOut' => false,
                'exitCode' => null,
            ];
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $error = '';
        $start = microtime(true);
        $timedOut = false;

        while (true) {
            $status = proc_get_status($process);

            $output .= (string) stream_get_contents($pipes[1]);
            $error .= (string) stream_get_contents($pipes[2]);

            if (!$status['running']) {
                break;
            }

            if ((microtime(true) - $start) >= $timeoutSeconds) {
                $timedOut = true;
                self::kill($status['pid']);
                break;
            }

            usleep(50000); // 50ms, evita busy-loop consumiendo CPU
        }

        // Última lectura por si quedó algo en el buffer.
        $output .= (string) stream_get_contents($pipes[1]);
        $error .= (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = null;

        if (!$timedOut) {
            $exitCode = proc_close($process);
        } else {
            @proc_close($process);
        }

        return [
            'output' => trim($output),
            'error' => trim($error),
            'timedOut' => $timedOut,
            'exitCode' => $exitCode,
        ];
    }

    private static function kill(int $pid): void
    {
        if ($pid <= 0) {
            return;
        }

        if (self::isWindows()) {
            // /T mata también los procesos hijos (ej. powershell.exe
            // puede dejar procesos hijos colgando).
            @shell_exec(sprintf('taskkill /F /T /PID %d 2>NUL', $pid));
        } else {
            @shell_exec(sprintf('kill -9 %d 2>/dev/null', $pid));
        }
    }

    private static function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}
