<?php

namespace BackupCenter\Core;

class WinScpProvider
{
    private string $winScp;
    private int $timeoutSeconds;

    public function __construct(
        string $winScp = 'C:\Program Files (x86)\WinSCP\WinSCP.com',
        int $timeoutSeconds = 120
    ) {
        $this->winScp = $winScp;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function execute(string $script): string
    {
        $dir = Paths::temp();

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $tempFile = $dir . '/winscp_' . uniqid('', true) . '.txt';

        file_put_contents($tempFile, $script);

        try {
            $result = ProcessRunner::run(
                [$this->winScp, '/script=' . $tempFile],
                $this->timeoutSeconds
            );

            if ($result['timedOut']) {
                throw new \RuntimeException(
                    "WinSCP no respondio en {$this->timeoutSeconds} segundos y el proceso fue cancelado."
                );
            }

            if ($result['exitCode'] !== 0) {
                throw new \RuntimeException(
                    'WinSCP termino con error (codigo ' . $result['exitCode'] . '): '
                    . ($result['error'] !== '' ? $result['error'] : $result['output'])
                );
            }

            return $result['output'];
        } finally {
            @unlink($tempFile);
        }
    }
}
