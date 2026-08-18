<?php

namespace BackupCenter\Scheduler;

use BackupCenter\Services\BackupService;

class JobRunner
{
    public function __construct(
        private BackupService $backupService
    ) {
    }

    public function run(int $jobId): void
    {
        $this->backupService->run($jobId);
    }
}