<?php

namespace BackupCenter\Core;

class RetryPolicy
{
    private int $attempts;
    private int $delay;

    public function __construct(int $attempts, int $delay)
    {
        $this->attempts = $attempts;
        $this->delay = $delay;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function shouldRetry(int $attempt): bool
    {
        return $attempt < $this->attempts;
    }
}