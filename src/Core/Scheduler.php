<?php

namespace BackupCenter\Core;

class Scheduler
{
    public function run(callable $callback, int $seconds): void
    {
        while (true) {

            $callback();

            sleep($seconds);
        }
    }
}