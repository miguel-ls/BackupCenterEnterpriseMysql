<?php

namespace BackupCenter\Services;

use BackupCenter\Core\Audit;
use BackupCenter\Core\SystemLog;
use BackupCenter\Core\HostedAgent;
use BackupCenter\Core\JobConfiguration;
use BackupCenter\Repositories\ConnectionRepository;
use BackupCenter\Repositories\JobRepository;

class BackupService
{
    private HostedAgent $agent;
    private JobRepository $jobRepository;
    private ConnectionRepository $connectionRepository;

    public function __construct(
        HostedAgent $agent,
        JobRepository $jobRepository,
        ConnectionRepository $connectionRepository
    ) {
        $this->agent = $agent;
        $this->jobRepository = $jobRepository;
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Ejecuta un trabajo manualmente.
     */
    public function run(int $jobId): bool
    {
        Audit::info(
            "BACKUP",
            "START",
            "Inicio Job {$jobId}",
            "SYSTEM"
        );

        SystemLog::info(
            'BACKUP',
            'START',
            "Inicio Job {$jobId}",
            [
                'job_id' => $jobId
            ]
        ); 

        $job = $this->jobRepository->getJob($jobId);

        if (!$job) {

            Audit::error(
                "BACKUP",
                "JOB_NOT_FOUND",
                "Job {$jobId} no existe.",
                "SYSTEM"
            );

            return false;
        }

        // Esta informaciÃ³n alimenta las columnas Estado y Ãšltima ejecuciÃ³n
        // de la grilla de Trabajos, independientemente de si se ejecuta
        // desde la cola o mediante el programador.
        $this->jobRepository->updateExecution(
            $jobId,
            'En ejecuciÃ³n'
        );

        $connection = null;

        if (!empty($job['connection_id'])) {

            $connection = $this->connectionRepository->get(
                (int)$job['connection_id']
            );

        }

        
        if (!$connection) {

            $this->jobRepository->updateExecution(
                $jobId,
                'Error'
            );

            Audit::error(
                "BACKUP",
                "CONNECTION_NOT_FOUND",
                "No existe conexión para Job {$jobId}.",
                "SYSTEM"
            );

            return false;
        }

        $configuration = new JobConfiguration(
            $job + $connection
        );

        $start = microtime(true);

        try {

            $ok = $this->agent->executeJob(
                $configuration
            );

            $seconds = round(
                microtime(true) - $start,
                2
            );

            if($ok){

                $this->jobRepository->updateExecution(
                    $jobId,
                    'Correcto'
                );

                Audit::info(
                    "BACKUP",
                    "SUCCESS",
                    "Job {$job['name']} completado en {$seconds} segundos.",
                    "SYSTEM"
                );

                SystemLog::success(
                    'BACKUP',
                    'SUCCESS',
                    "Job {$job['name']} completado en {$seconds} segundos.",
                    [
                        'job_id' => $jobId,
                        'connection_id' => $job['connection_id'] ?? null
                    ]
                );                

            }else{

                $this->jobRepository->updateExecution(
                    $jobId,
                    'Error'
                );

                Audit::error(
                    "BACKUP",
                    "FAILED",
                    "Job {$job['name']} terminó con errores.",
                    "SYSTEM"
                );

                SystemLog::error(
                    'BACKUP',
                    'FAILED',
                    "Job {$job['name']} terminó con errores.",
                    [
                        'job_id' => $jobId,
                        'connection_id' => $job['connection_id'] ?? null
                    ]
                );                

            }

            return $ok;

        } catch (\Throwable $e) {

            $this->jobRepository->updateExecution(
                $jobId,
                'Error'
            );

            Audit::error(
                "BACKUP",
                "EXCEPTION",
                $e->getMessage(),
                "SYSTEM"
            );

            SystemLog::error(
                'BACKUP',
                'EXCEPTION',
                $e->getMessage(),
                [
                    'job_id' => $jobId,
                    'exception' => get_class($e)
                ]
            );            

            throw $e;
        }
    }
}
