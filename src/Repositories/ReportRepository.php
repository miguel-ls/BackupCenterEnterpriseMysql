<?php

namespace BackupCenter\Repositories;

use BackupCenter\Core\Database;
use PDO;

class ReportRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Resumen Ejecutivo
     */
    public function summary(): array
    {
        return [

            "jobs" => (int)$this->db->query("
                SELECT COUNT(*)
                FROM jobs
            ")->fetchColumn(),

            "connections" => (int)$this->db->query("
                SELECT COUNT(*)
                FROM connections
            ")->fetchColumn(),

            "uploaded_files" => (int)$this->db->query("
                SELECT COUNT(*)
                FROM uploaded_files
            ")->fetchColumn(),

            "executions" => (int)$this->db->query("
                SELECT COUNT(*)
                FROM execution_history
            ")->fetchColumn(),

"errors" => (int)$this->db->query("
    SELECT COUNT(*)
    FROM execution_history
    WHERE files_failed > 0
")->fetchColumn()

        ];
    }

    /**
     * Backups por día
     */
    public function daily(int $days = 30): array
    {
        $stmt = $this->db->prepare("
SELECT

    DATE(started_at) AS day,

    COUNT(*) AS executions,

    SUM(files_uploaded) AS uploaded,

    SUM(files_failed) AS errors

FROM execution_history

GROUP BY DATE(started_at)

ORDER BY DATE(started_at) DESC

LIMIT :days
        ");

        $stmt->bindValue(
            ":days",
            $days,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clientes
     */
    public function clients(): array
    {
        $stmt = $this->db->query("
SELECT

    c.business_name AS client,

    COUNT(*) AS executions,

    SUM(e.files_uploaded) AS uploaded,

    SUM(e.files_failed) AS errors

FROM execution_history e

INNER JOIN jobs j
    ON j.id = e.job_id

INNER JOIN connections cn
    ON cn.id = j.connection_id

INNER JOIN clients c
    ON c.id = cn.client_id

GROUP BY c.id, c.business_name

ORDER BY executions DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Trabajos
     */
    public function jobs(): array
    {
        $stmt = $this->db->query("
            SELECT

                j.name,

COALESCE(
(
    SELECT e.status
    FROM execution_history e
    WHERE e.job_id = j.id
    ORDER BY e.id DESC
    LIMIT 1
),
j.last_status,
'Pendiente'
) AS last_status,

COALESCE(
(
    SELECT e.started_at
    FROM execution_history e
    WHERE e.job_id = j.id
    ORDER BY e.id DESC
    LIMIT 1
),
j.last_run,
'-'
) AS last_run

            FROM jobs j

            ORDER BY id
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuciones que registraron errores.
     */
    public function errors(): array
    {
        $stmt = $this->db->query("
SELECT

    e.started_at,

    c.business_name AS client,

    e.files_found,

    e.files_uploaded,

    e.files_failed,

    e.duration_seconds,

    e.status

FROM execution_history e

INNER JOIN jobs j
    ON j.id = e.job_id

INNER JOIN connections cn
    ON cn.id = j.connection_id

INNER JOIN clients c
    ON c.id = cn.client_id

WHERE e.files_failed > 0
   OR e.status = 'Con errores'

ORDER BY e.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conexiones
     */
    public function connections(): array
    {
        $stmt = $this->db->query("
            SELECT

                name,

                protocol,

                host,

                remote_path

            FROM connections

            ORDER BY name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
