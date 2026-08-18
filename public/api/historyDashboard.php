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

$page = max(
    1,
    (int)($_GET['page'] ?? 1)
);

$limit = (int)($_GET['limit'] ?? 10);

$clientId = (int)($_GET['client_id'] ?? 0);
$jobId = (int)($_GET['job_id'] ?? 0);
$status = trim((string)($_GET['status'] ?? ''));
$from = trim((string)($_GET['from'] ?? ''));
$to = trim((string)($_GET['to'] ?? ''));

$allowedLimits = [5,10,20,50,100,200];

if (!in_array($limit, $allowedLimits, true)) {
    $limit = 5;
}

$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if ($clientId > 0) {
    $where[] = 'c.id = ?';
    $params[] = $clientId;
}

if ($jobId > 0) {
    $where[] = 'j.id = ?';
    $params[] = $jobId;
}

if ($status !== '') {
    $where[] = 'LOWER(eh.status) = LOWER(?)';
    $params[] = $status;
}

if ($from !== '') {
    $where[] = 'date(eh.started_at) >= date(?)';
    $params[] = $from;
}

if ($to !== '') {
    $where[] = 'date(eh.started_at) <= date(?)';
    $params[] = $to;
}

$whereSql = count($where) > 0
    ? ' and ' . implode(' AND ', $where)
    : '';

$countSql = "
    SELECT COUNT(*)
    FROM execution_history eh
    INNER JOIN jobs j ON j.id = eh.job_id
    INNER JOIN connections cn ON cn.id = j.connection_id
    INNER JOIN clients c ON c.id = cn.client_id
    WHERE (files_uploaded + files_failed)   > 0  
    $whereSql
";

$stmtTotal = $pdo->prepare($countSql);

foreach ($params as $index => $value) {
    $stmtTotal->bindValue($index + 1, $value);
}

$stmtTotal->execute();
$total = (int)$stmtTotal->fetchColumn();

$sql = "
SELECT
    eh.id,
    eh.started_at,
    eh.finished_at,
    j.id AS job_id,
    j.name AS job_name,
    c.id AS client_id,
    c.business_name AS client_name,
    eh.files_found,
    eh.files_uploaded,
    eh.files_skipped,
    eh.files_failed,
    eh.duration_seconds,
    eh.status
FROM execution_history eh
INNER JOIN jobs j ON j.id = eh.job_id
INNER JOIN connections cn ON cn.id = j.connection_id
INNER JOIN clients c ON c.id = cn.client_id
WHERE (files_uploaded   > 0  or files_failed >0) 
$whereSql
ORDER BY eh.id DESC
LIMIT ?
OFFSET ?
";

$stmt = $pdo->prepare($sql);

$bindIndex = 1;
foreach ($params as $value) {
    $stmt->bindValue($bindIndex++, $value);
}

$stmt->bindValue($bindIndex++, $limit, PDO::PARAM_INT);
$stmt->bindValue($bindIndex++, $offset, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$timezone = new DateTimeZone('America/Lima');

foreach ($data as &$row) {
    foreach (['started_at', 'finished_at'] as $field) {
        if (empty($row[$field])) {
            continue;
        }

        try {
            $date = new DateTime($row[$field]);
            $date->setTimezone($timezone);
            $row[$field] = $date->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
        }
    }
}

unset($row);

$clientRows = $pdo->query("
    SELECT id, business_name AS name
    FROM clients
    ORDER BY business_name
")->fetchAll(PDO::FETCH_ASSOC);

$jobRows = $pdo->query("
    SELECT id, name
    FROM jobs
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$statusRows = $pdo->query("
    SELECT DISTINCT status
    FROM execution_history
    WHERE status IS NOT NULL AND TRIM(status) <> ''
    ORDER BY status
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'page' => $page,
    'limit' => $limit,
    'total' => $total,
    'pages' => (int)ceil($total / $limit),
    'data' => $data,
    'filters' => [
        'clients' => $clientRows,
        'jobs' => $jobRows,
        'statuses' => array_map(static function (array $row): string {
            return (string)($row['status'] ?? '');
        }, $statusRows)
    ]
]);