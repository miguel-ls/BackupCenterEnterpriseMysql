<?php

namespace BackupCenter\Services;

use BackupCenter\Contracts\IConfiguration;
use BackupCenter\Builders\ScriptBuilder;
use BackupCenter\Core\RetryPolicy;
use BackupCenter\Core\WinScpProvider;

class UploadManagerFactory
{
    public function create(
        IConfiguration $configuration,
        RetryPolicy $retryPolicy
    ): UploadManager {

        // Nota: $configuration ya no se inyecta en el constructor de
        // UploadManager, ahora se pasa por parametro en cada upload().
        // Esta factory no forma parte del flujo activo (ver documento
        // de arquitectura, "Componentes heredados o desconectados").
        return new UploadManager(
            new WinScpProvider(),
            new ScriptBuilder(),
            $retryPolicy
        );
    }
}