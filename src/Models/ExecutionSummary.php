<?php

namespace BackupCenter\Models;

class ExecutionSummary
{
    public bool $success = true;

    public string $message = '';
        
    public int $found = 0;
    public int $uploaded = 0;
    public int $skipped = 0;
    public int $errors = 0;

    public string $startedAt;
    public ?string $finishedAt = null;

    private float $startTime;

    
    public function __construct()
    {
        $this->startedAt = date('Y-m-d H:i:s');
        $this->startTime = microtime(true);
    }

    public function finish(): void
    {
        $this->finishedAt = date('Y-m-d H:i:s');
    }

    public function getDuration(): float
    {
        return round(microtime(true) - $this->startTime, 2);
    }

    public function isSuccess(): bool
    {
        return $this->errors === 0;
    }
}