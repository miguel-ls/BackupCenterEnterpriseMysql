<?php

namespace BackupCenter\Services;

use BackupCenter\Contracts\IConfiguration;
use BackupCenter\Builders\ScriptBuilder;
use BackupCenter\Core\WinScpProvider;
use BackupCenter\Models\BackupFile;
use BackupCenter\Core\RetryPolicy;

class UploadManager
{
    private WinScpProvider $provider;
    private RetryPolicy $retryPolicy;
    private ScriptBuilder $builder;

    public function __construct(
        WinScpProvider $provider,
        ScriptBuilder $builder,
        RetryPolicy $retryPolicy
    ) {
        $this->provider = $provider;
        $this->builder = $builder;
        $this->retryPolicy = $retryPolicy;
    }

    /**
     * Sube un archivo usando la conexion del trabajo que se esta
     * ejecutando en este momento.
     *
     * IMPORTANTE: $configuration debe ser la configuracion de ESE job
     * (JobConfiguration, construida con los datos de la tabla
     * `connections`), no la configuracion global de config.json. Antes
     * UploadManager recibia su IConfiguration una sola vez en el
     * constructor y la reutilizaba para todos los jobs, por lo que
     * siempre terminaba usando la conexion de config.json sin importar
     * que trabajo se estuviera ejecutando.
     */
    public function upload(BackupFile $file, IConfiguration $configuration): string
    {
        $connection = $configuration->getConnectionConfig();

        $remotePath = $connection->getRemotePath();

        $lastException = null;

        for (
            $attempt = 1;
            $attempt <= $this->retryPolicy->getAttempts();
            $attempt++
        ) {

            try {

                $script = $this->builder
                    ->batchAbort()
                    ->confirmOff()
                    ->open($connection)
                    ->put($file->getPath(), $remotePath !== '' ? $remotePath : null)
                    ->exit()
                    ->build();

                return $this->provider->execute($script);

            } catch (\Throwable $e) {

                $lastException = $e;

                echo "Intento {$attempt} falló: {$e->getMessage()}" . PHP_EOL;

                if (!$this->retryPolicy->shouldRetry($attempt)) {
                    break;
                }

                echo "Reintentando en "
                    . $this->retryPolicy->getDelay()
                    . " segundos..."
                    . PHP_EOL;

                sleep($this->retryPolicy->getDelay());
            }
        }

        throw $lastException;
    }
}
