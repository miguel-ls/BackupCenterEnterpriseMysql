<?php

namespace BackupCenter\Queue;

class JobQueue
{
    private array $queue = [];

    public function enqueue(int $jobId): void
    {
        if (!in_array($jobId, $this->queue, true)) {
            $this->queue[] = $jobId;
        }
    }

    public function dequeue(): ?int
    {
        if (empty($this->queue)) {
            return null;
        }

        return array_shift($this->queue);
    }

    public function isEmpty(): bool
    {
        return empty($this->queue);
    }

    public function count(): int
    {
        return count($this->queue);
    }

    public function all(): array
    {
        return $this->queue;
    }
}