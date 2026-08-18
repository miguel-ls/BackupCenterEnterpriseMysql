<?php

namespace BackupCenter\Services;

use Cron\CronExpression;
use DateTime;

class CronService
{
    public function nextRun(string $expression): ?string
    {
        try {

            $cron = new CronExpression($expression);

            return $cron
                ->getNextRunDate()
                ->format('Y-m-d H:i:s');

        } catch (\Throwable $e) {

            return null;

        }
    }

    public function previousRun(string $expression): ?string
    {
        try {

            $cron = new CronExpression($expression);

            return $cron
                ->getPreviousRunDate()
                ->format('Y-m-d H:i:s');

        } catch (\Throwable $e) {

            return null;

        }
    }

    public function isDue(string $expression): bool
    {
        try {

            $cron = new CronExpression($expression);

            return $cron->isDue();

        } catch (\Throwable $e) {

            return false;

        }
    }

    public function remaining(string $expression): string
    {
        $next = $this->nextRun($expression);

        if (!$next) {

            return "-";

        }

        $seconds = strtotime($next) - time();

        if ($seconds < 60) {

            return $seconds . " seg";

        }

        if ($seconds < 3600) {

            return floor($seconds / 60) . " min";

        }

        if ($seconds < 86400) {

            return floor($seconds / 3600) . " h";

        }

        return floor($seconds / 86400) . " días";
    }
}