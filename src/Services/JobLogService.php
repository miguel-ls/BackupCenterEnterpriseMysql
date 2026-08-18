<?php

namespace BackupCenter\Services;

use BackupCenter\Core\SystemLog;

class JobLogService
{
    private function buildJobMessage(
        string $action,
        string $jobName,
        ?string $connectionName = null,
        ?string $clientName = null
    ): string {
        $message = "Trabajo {$jobName} {$action}";

        if (!empty($connectionName) || !empty($clientName)) {
            $parts = [];

            if (!empty($connectionName)) {
                $parts[] = "conexión {$connectionName}";
            }

            if (!empty($clientName)) {
                $parts[] = "cliente {$clientName}";
            }

            $message .= " (" . implode(' | ', $parts) . ")";
        }

        return $message;
    }

    public function logCreate(
        string $jobName,
        ?int $jobId = null,
        ?int $connectionId = null,
        ?int $clientId = null,
        ?string $username = null,
        ?string $connectionName = null,
        ?string $clientName = null
    ): void {
        SystemLog::info(
            'JOBS',
            'CREATE',
            $this->buildJobMessage('creado', $jobName, $connectionName, $clientName),
            [
                'job_id' => $jobId,
                'connection_id' => $connectionId,
                'client_id' => $clientId,
            ],
            $username
        );
    }

    public function logUpdate(
        string $jobName,
        ?int $jobId = null,
        ?int $connectionId = null,
        ?int $clientId = null,
        ?string $username = null,
        ?string $connectionName = null,
        ?string $clientName = null
    ): void {
        SystemLog::info(
            'JOBS',
            'UPDATE',
            $this->buildJobMessage('actualizado', $jobName, $connectionName, $clientName),
            [
                'job_id' => $jobId,
                'connection_id' => $connectionId,
                'client_id' => $clientId,
            ],
            $username
        );
    }

    public function logDelete(
        string $jobName,
        ?int $jobId = null,
        ?string $username = null,
        ?string $connectionName = null,
        ?string $clientName = null
    ): void {
        SystemLog::info(
            'JOBS',
            'DELETE',
            $this->buildJobMessage('eliminado', $jobName, $connectionName, $clientName),
            [
                'job_id' => $jobId,
            ],
            $username
        );
    }

    public function logQueue(
        ?int $jobId = null,
        ?string $jobName = null,
        ?int $connectionId = null,
        ?string $username = null,
        ?string $connectionName = null,
        ?string $clientName = null
    ): void {
        $message = $this->buildJobMessage('agregado a la cola', $jobName ?? '', $connectionName, $clientName);

        if (!empty($jobName)) {
            $message = "Tarea {$jobName} agregada a la cola";

            if (!empty($connectionName)) {
                $message .= " para la conexión {$connectionName}";
            }

            if (!empty($clientName)) {
                $message .= " del cliente {$clientName}";
            }
        }

        SystemLog::info(
            'JOBS',
            'QUEUE',
            $message,
            [
                'job_id' => $jobId,
                'connection_id' => $connectionId,
            ],
            $username
        );
    }

    public function logClientEvent(
        string $action,
        string $clientName,
        ?int $clientId = null,
        ?string $username = null
    ): void {
        $message = "Cliente {$clientName} " . strtolower($action);

        if ($action === 'CREATE') {
            $message = "Cliente {$clientName} creado";
        } elseif ($action === 'UPDATE') {
            $message = "Cliente {$clientName} actualizado";
        } elseif ($action === 'DELETE') {
            $message = "Cliente {$clientName} eliminado";
        }

        SystemLog::info(
            'CLIENTS',
            $action,
            $message,
            [
                'client_id' => $clientId,
            ],
            $username
        );
    }

    public function logConnectionEvent(
        string $action,
        string $connectionName,
        ?int $connectionId = null,
        ?string $clientName = null,
        ?int $clientId = null,
        ?string $username = null
    ): void {
        $message = "Conexión {$connectionName} " . strtolower($action);

        if ($action === 'CREATE') {
            $message = "Conexión {$connectionName} creada";
        } elseif ($action === 'UPDATE') {
            $message = "Conexión {$connectionName} actualizada";
        } elseif ($action === 'DELETE') {
            $message = "Conexión {$connectionName} eliminada";
        }

        if (!empty($clientName)) {
            $message .= " para el cliente {$clientName}";
        }

        SystemLog::info(
            'CONNECTIONS',
            $action,
            $message,
            [
                'connection_id' => $connectionId,
                'client_id' => $clientId,
            ],
            $username
        );
    }

    public function logUserEvent(
        string $action,
        string $usernameValue,
        ?int $userId = null,
        ?string $actor = null
    ): void {
        $message = "Usuario {$usernameValue} " . strtolower($action);

        if ($action === 'CREATE') {
            $message = "Usuario {$usernameValue} creado";
        } elseif ($action === 'UPDATE') {
            $message = "Usuario {$usernameValue} actualizado";
        } elseif ($action === 'DELETE') {
            $message = "Usuario {$usernameValue} eliminado";
        }

        SystemLog::info(
            'USERS',
            $action,
            $message,
            [
                'user_id' => $userId,
            ],
            $actor
        );
    }

    public function logSettingsEvent(
        string $action,
        string $message,
        ?int $settingsId = null,
        ?string $actor = null
    ): void {
        SystemLog::info(
            'SETTINGS',
            $action,
            $message,
            [
                'settings_id' => $settingsId,
            ],
            $actor
        );
    }
}
