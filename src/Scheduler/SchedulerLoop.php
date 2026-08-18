<?php

namespace BackupCenter\Scheduler;

class SchedulerLoop
{
    public function __construct(
        private SchedulerEngine $engine
    ) {
    }

    public function run(
        int $seconds = 60
    ): void
    {
        while (true) {

            echo PHP_EOL;
            echo "[" . date('Y-m-d H:i:s') . "] Scheduler" . PHP_EOL;

            $this->engine->execute();

            sleep($seconds);
        }
    }
}