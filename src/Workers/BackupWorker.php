<?php

namespace BackupCenter\Workers;

use BackupCenter\Core\Audit;
use BackupCenter\Repositories\JobQueueRepository;
use BackupCenter\Repositories\NotificationRepository;
use BackupCenter\Services\BackupService;

class BackupWorker
{
    public function __construct(
        private JobQueueRepository $queue,
        private BackupService $backupService,
        private NotificationRepository $notifications
    ) {
    }

    public function process(): void
    {
        $item = $this->queue->getNext();

        if (!$item) {

            echo "[" . date('H:i:s') . "] Esperando trabajos..." . PHP_EOL;

            return;
        }

        $this->queue->start(

            (int)$item["id"],

            gethostname()

        );

        Audit::info(

            "WORKER",

            "START",

            "Inicio Job {$item['job_id']}",

            "SYSTEM"

        );

        $start = microtime(true);

        try {

            echo "Worker ejecutando Job {$item['job_id']}" . PHP_EOL;

            $ok = $this->backupService->run(

                (int)$item["job_id"]

            );

            if ($ok) {

                $this->queue->finish(

                    (int)$item["id"]

                );

                $seconds = round(

                    microtime(true) - $start,

                    2

                );

                $this->notifications->add(

                    "INFO",

                    "Backup completado",

                    "El Job {$item['job_id']} finalizó correctamente."

                );

                Audit::info(

                    "WORKER",

                    "SUCCESS",

                    "Job {$item['job_id']} completado en {$seconds} segundos.",

                    "SYSTEM"

                );

            } else {

                $this->queue->fail(

                    (int)$item["id"],

                    "Backup finalizó con errores."

                );

                $this->notifications->add(

                    "ERROR",

                    "Backup fallido",

                    "El Job {$item['job_id']} terminó con errores."

                );

            }

        } catch (\Throwable $e) {

            $this->queue->fail(

                (int)$item["id"],

                $e->getMessage()

            );

            $this->notifications->add(

                "ERROR",

                "Backup fallido",

                "El Job {$item['job_id']} falló: {$e->getMessage()}"

            );

            Audit::error(

                "WORKER",

                "FAILED",

                "Job {$item['job_id']} : {$e->getMessage()}",

                "SYSTEM"

            );

        }
    }
}