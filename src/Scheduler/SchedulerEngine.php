<?php

namespace BackupCenter\Scheduler;

use BackupCenter\Core\Audit;
use BackupCenter\Repositories\JobRepository;
use BackupCenter\Repositories\JobQueueRepository;



class SchedulerEngine
{
    public function __construct(
        private JobRepository $repository,
        private CronEvaluator $cron,
        private JobRunner $runner,
        private JobQueueRepository $queue
    ) {
    }

    public function execute(bool $force = false): void
    {
        $jobs = $this->repository->getEnabledJobs();

        // Audit::info(

        //     "SCHEDULER",

        //     "START",

        //     "Scheduler iniciado.",

        //     "SYSTEM"

        // );

        foreach ($jobs as $job) {

            echo "Evaluando: {$job['name']}" . PHP_EOL;

            if ($this->repository->isRunning((int)$job['id'])) {

                Audit::info(

                    "SCHEDULER",

                    "SKIP",

                    sprintf(

                        "Trabajo: %s | Cliente: %s | Conexión: %s | Ya está ejecutándose.",

                        $job["name"],

                        $job["client_name"],

                        $job["connection_name"]

                    ),

                    "SYSTEM"
                    
                );

                continue;
            }

            if (
                !$force &&
                $this->repository->executedThisMinute((int)$job['id'])
            ) {

                Audit::info(

                    "SCHEDULER",

                    "SKIP",

                    sprintf(

                        "Trabajo: %s | Cliente: %s | Conexión: %s | Ya fue ejecutado este minuto.",

                        $job["name"],

                        $job["client_name"],

                        $job["connection_name"]

                    ),

                    "SYSTEM"

                );

                continue;
            }

            if (
                !$force &&
                !$this->cron->isDue($job['schedule'])
            ) {

                continue;
            }

            $this->queue->enqueue(
                (int)$job['id']
            );

            Audit::info(

                "SCHEDULER",

                "QUEUE",

                sprintf(

                    "Trabajo: %s | Cliente: %s | Conexión: %s",

                    $job["name"],

                    $job["client_name"],

                    $job["connection_name"]

                ),

                "SYSTEM"

            );

            echo "Encolando Job {$job['id']}" . PHP_EOL;
        }

        // Audit::info(

        //     "SCHEDULER",

        //     "END",

        //     "Scheduler finalizado.",

        //     "SYSTEM"

        // );
    }
}