<?php

namespace BackupCenter\Workers;

class WorkerLoop
{
    private bool $running = true;

    public function __construct(
        private BackupWorker $worker
    ) {
    }

    /**
     * Inicia el Worker en modo continuo.
     */
    public function run(
        int $seconds = 1
    ): void
    {
        echo PHP_EOL;
        echo "======================================" . PHP_EOL;
        echo " BackupCenter Worker iniciado" . PHP_EOL;
        echo "======================================" . PHP_EOL;
        echo PHP_EOL;

        while ($this->running) {

            try {

                $this->worker->process();

            } catch (\Throwable $e) {

                echo "[" . date('Y-m-d H:i:s') . "] ";

                echo "ERROR: ";

                echo $e->getMessage();

                echo PHP_EOL;

            }

            sleep($seconds);

        }
    }

    /**
     * Detiene el Worker.
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * Indica si el Worker continúa activo.
     */
    public function isRunning(): bool
    {
        return $this->running;
    }
}