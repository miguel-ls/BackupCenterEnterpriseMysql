<?php

namespace BackupCenter\Core;

class HostedAgent
{
    private Application $application;
    private Scheduler $scheduler;

    public function __construct(
        Application $application,
        Scheduler $scheduler
    ) {
        $this->application = $application;
        $this->scheduler = $scheduler;
    }

    /**
     * Ejecuta una sola pasada del Scheduler.
     */
public function executeOnce(): void
{
$this->application
    ->agent()
    ->run(
        $this->application->config()
    );
}

    /**
     * Ejecuta un trabajo específico.
     */
public function executeJob(JobConfiguration $configuration): bool
{
    $this->application
        ->logger()
        ->info(
            'Ejecutando trabajo: ' .
            $configuration->get('name')
        );

    $summary = $this->application
        ->agent()
        ->run($configuration);

    return $summary->errors === 0;
}

    /**
     * Servicio Windows.
     */
    public function run(): void
    {
        $interval = $this->application
            ->config()
            ->get('scheduler.interval_seconds');

        $this->scheduler->run(

            function () {

                $this->application
                    ->agent()
                    ->run(
                        $this->application->config()
                    );

            },

            $interval
        );
    }
}
