<?php

namespace BackupCenter\Scheduler;

use Cron\CronExpression;
use InvalidArgumentException;

class CronEvaluator
{
    public function isDue(string $expression): bool
    {
        $expression = trim($expression);

        if ($expression === '') {
            return false;
        }

        try {
            return (new CronExpression($expression))->isDue();
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}