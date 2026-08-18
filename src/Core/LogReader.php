<?php

namespace BackupCenter\Core;

class LogReader
{
    private string $logDirectory;

    public function __construct(string $logDirectory)
    {
        $this->logDirectory = rtrim($logDirectory, '/\\');
    }

    public function latest(int $lines = 20): array
    {
        $files = glob($this->logDirectory . DIRECTORY_SEPARATOR . '*.log');

        if (empty($files)) {
            return [];
        }

        rsort($files);

        $logFile = $files[0];

        $content = file($logFile, FILE_IGNORE_NEW_LINES);

        if ($content === false) {
            return [];
        }

        return array_slice($content, -$lines);
    }
}