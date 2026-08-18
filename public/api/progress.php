<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Core\Auth;

header('Content-Type: application/json');

try {

    Auth::require();

    $database = new Database(
        Paths::database() . '/backupcenter.db'
    );

    $pdo = $database->getConnection();

    $status = $_GET['status'] ?? 'all';
    $date   = $_GET['date'] ?? 'all';

    /*
     * ============================================================
     * WHERE
     * ============================================================
     */

    $where = "WHERE 1 = 1";

    $params = [];

    /*
     * Filtro por estado
     */

    if ($status !== 'all' && $status !== '') {

        $where .= "
            AND tp.status = :status
        ";

        $params[':status'] = $status;
    }

    /*
     * Filtro por fecha
     */

    if ($date === 'today') {

        $where .= "
            AND date(tp.updated_at) =
                date('now', 'localtime')
        ";

    } elseif ($date === 'yesterday') {

        $where .= "
            AND date(tp.updated_at) =
                date('now', 'localtime', '-1 day')
        ";

    } elseif ($date === '7days') {

        $where .= "
            AND datetime(tp.updated_at) >=
                datetime('now', 'localtime', '-7 days')
        ";

    } elseif ($date === '30days') {

        $where .= "
            AND datetime(tp.updated_at) >=
                datetime('now', 'localtime', '-30 days')
        ";
    }


    /*
     * ============================================================
     * TRANSFERENCIAS
     * ============================================================
     */

    $sql = "
        SELECT
            tp.id,
            tp.job_id,
            tp.file_name,
            tp.total_bytes,
            tp.uploaded_bytes,
            tp.speed,
            tp.status,
            tp.updated_at,

            j.name AS job_name,

            COALESCE(
                cl.business_name,
                '-'
            ) AS client_name

        FROM transfer_progress tp

        LEFT JOIN jobs j
            ON j.id = tp.job_id

        LEFT JOIN connections c
            ON c.id = j.connection_id

        LEFT JOIN clients cl
            ON cl.id = c.client_id

        {$where}

        ORDER BY tp.updated_at DESC
    ";

    $statement = $pdo->prepare($sql);

    $statement->execute($params);

    $items = $statement->fetchAll(PDO::FETCH_ASSOC);

    $client = $_GET['client'] ?? 'all';

    if ($client !== 'all') {
        $items = array_values(array_filter($items, function ($row) use ($client) {
            return isset($row['client_name']) && $row['client_name'] === $client;
        }));
    }

    /*
     * ============================================================
     * RESUMEN
     * ============================================================
     */

    $summarySql = "
        SELECT
            COUNT(*) AS total,

            SUM(
                CASE
                    WHEN tp.status = 'uploading'
                    THEN 1
                    ELSE 0
                END
            ) AS uploading,

            SUM(
                CASE
                    WHEN tp.status = 'completed'
                    THEN 1
                    ELSE 0
                END
            ) AS completed,

            SUM(
                CASE
                    WHEN tp.status = 'failed'
                    THEN 1
                    ELSE 0
                END
            ) AS failed

        FROM transfer_progress tp

        {$where}
    ";

    $summaryStatement = $pdo->prepare($summarySql);

    $summaryStatement->execute($params);

    $summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);


    /*
     * ============================================================
     * RESPUESTA
     * ============================================================
     */

    echo json_encode([
        'success' => true,

        'summary' => [
            'total'     => (int) ($summary['total'] ?? 0),
            'uploading' => (int) ($summary['uploading'] ?? 0),
            'completed' => (int) ($summary['completed'] ?? 0),
            'failed'    => (int) ($summary['failed'] ?? 0)
        ],

        'data' => $items
    ]);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}