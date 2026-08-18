<?php

namespace BackupCenter\Services;

use BackupCenter\Repositories\JobRepository;
use BackupCenter\Core\JobConfiguration;

class JobService
{
    private JobRepository $repository;

    public function __construct(JobRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtiene un trabajo por ID.
     */
    public function get(int $id): ?array
    {
        return $this->repository->getJob($id);
    }

    /**
     * Devuelve la configuración del trabajo.
     */
    public function configuration(int $id): ?JobConfiguration
    {
        $job = $this->repository->getJob($id);

        if (!$job) {
            return null;
        }

        return new JobConfiguration($job);
    }

    /**
     * Marca el trabajo como ejecutándose.
     */
    public function start(int $id): void
    {
        $this->repository->updateExecution(
            $id,
            'En ejecución'
        );
    }

    /**
     * Marca el trabajo como correcto.
     */
    public function success(int $id): void
    {
        $this->repository->updateExecution(
            $id,
            'Correcto'
        );
    }

    /**
     * Marca el trabajo como error.
     */
    public function error(int $id): void
    {
        $this->repository->updateExecution(
            $id,
            'Error'
        );
    }
}