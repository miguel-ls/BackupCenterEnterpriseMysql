<?php

namespace BackupCenter\Core;

class ServiceHost
{
    private Application $app;
    private Scheduler $scheduler;

    public function __construct(
        Application $app,
        Scheduler $scheduler
    ) {
        $this->app = $app;
        $this->scheduler = $scheduler;
    }

    public function run(): void
    {
        $interval = $this->app
            ->config()
            ->get('scheduler.interval_seconds');

        $this->scheduler->run(

            fn() => $this->app
                ->agent()
                ->run(
                    $this->app->config()
                ),

            $interval
        );
    }
}
