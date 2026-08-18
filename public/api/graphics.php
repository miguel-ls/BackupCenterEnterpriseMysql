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
$status = trim((string)($_GET['status'] ?? ''));
$from = trim((string)($_GET['from'] ?? date('Y-m-d', strtotime('-7 days'))));
$to = trim((string)($_GET['to'] ?? date('Y-m-d')));

$dateExpression = "date(datetime(replace(substr(eh.started_at,1,19),'T',' '), '-5 hours'))";

$where = [
    "$dateExpression BETWEEN :from AND :to"
];
$params = [
    ':from' => $from,
    ':to' => $to
];

if ($clientId > 0) {
    $where[] = 'c.id = :client_id';
    $params[':client_id'] = $clientId;
}

if ($status !== '') {
    $where[] = 'LOWER(eh.status) = LOWER(:status)';
    $params[':status'] = $status;
}

$whereSql = implode(' AND ', $where);

$sql = "
SELECT
    $dateExpression AS day,
    c.id AS client_id,
    c.business_name AS client_name,
    COUNT(*) AS executions,
    COALESCE(SUM(eh.files_found), 0) AS files_found,
    COALESCE(SUM(eh.files_uploaded), 0) AS files_uploaded,
    COALESCE(SUM(eh.files_skipped), 0) AS files_skipped,
    COALESCE(SUM(eh.files_failed), 0) AS files_failed
FROM execution_history eh
INNER JOIN jobs j ON j.id = eh.job_id
INNER JOIN connections cn ON cn.id = j.connection_id
INNER JOIN clients c ON c.id = cn.client_id
WHERE (files_uploaded > 0 OR files_failed > 0) AND $whereSql
GROUP BY day, c.id, c.business_name
ORDER BY day, c.business_name
";

//echo "<pre>$sql</pre>";

$stmt = $pdo->prepare($sql);
foreach ($params as $name => $value) {
    $stmt->bindValue($name, $value, $name === ':client_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$summarySql = "
SELECT
    COALESCE(SUM(eh.files_found), 0) AS files_found,
    COALESCE(SUM(eh.files_uploaded), 0) AS files_uploaded,
    COALESCE(SUM(eh.files_skipped), 0) AS files_skipped,
    COALESCE(SUM(eh.files_failed), 0) AS files_failed
FROM execution_history eh
INNER JOIN jobs j ON j.id = eh.job_id
INNER JOIN connections cn ON cn.id = j.connection_id
INNER JOIN clients c ON c.id = cn.client_id
WHERE (files_uploaded > 0 OR files_failed > 0) AND $whereSql
";

$summaryStmt = $pdo->prepare($summarySql);
foreach ($params as $name => $value) {
    $summaryStmt->bindValue($name, $value, $name === ':client_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$summaryStmt->execute();
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'files_found' => 0,
    'files_uploaded' => 0,
    'files_skipped' => 0,
    'files_failed' => 0
];

$clients = $pdo->query("SELECT id, business_name AS name FROM clients ORDER BY business_name")
    ->fetchAll(PDO::FETCH_ASSOC);

$statuses = $pdo->query("SELECT DISTINCT status FROM execution_history WHERE status IS NOT NULL AND TRIM(status) <> '' ORDER BY status")
    ->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'success' => true,
    'data' => $rows,
    'summary' => $summary,
    'filters' => [
        'clients' => $clients,
        'statuses' => array_values($statuses)
    ]
]);
