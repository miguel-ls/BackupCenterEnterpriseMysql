<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;
use BackupCenter\Core\Auth;

Auth::require();

$db = new Database(
    Paths::database() . '/backupcenter.db'
);

$pdo = $db->getConnection();

$clientId = (int)($_GET['client_id'] ?? 0);

$endDate = $_GET['end_date'] ?? date('Y-m-d');

$startDate = $_GET['start_date']
    ?? date('Y-m-d', strtotime($endDate . ' -7 days'));

$sql = "
SELECT
    date(datetime(replace(substr(eh.started_at,1,19),'T',' '), '-5 hours')) AS day,
    COUNT(*) AS executions,
    COALESCE(SUM(eh.files_uploaded),0) AS uploaded
FROM execution_history eh

INNER JOIN jobs j
    ON j.id = eh.job_id

INNER JOIN connections cn
    ON cn.id = j.connection_id

INNER JOIN clients c
    ON c.id = cn.client_id
WHERE
    date(datetime(replace(substr(eh.started_at,1,19),'T',' '), '-5 hours'))
        BETWEEN :start_date AND :end_date
";

if ($clientId > 0) {
    $sql .= " AND c.id = :client_id";
}

$sql .= "
GROUP BY
    date(datetime(replace(substr(eh.started_at,1,19),'T',' '), '-5 hours'))
ORDER BY
    day
";

$stmt = $pdo->prepare($sql);

$stmt->bindValue(':start_date', $startDate);
$stmt->bindValue(':end_date', $endDate);

if ($clientId > 0) {
    $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
}

$stmt->execute();

echo json_encode([
    'success' => true,
    'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
]);