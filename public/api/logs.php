<?php

require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use BackupCenter\Core\Application;


// Los registros se escriben con la hora de Lima. Mantener la misma zona
// horaria en esta respuesta evita que la fecha de respaldo aparezca en UTC.
date_default_timezone_set('America/Lima');

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(10, (int)($_GET['limit'] ?? 50));

$app = new Application();

$rows = $app
    ->database()
    ->getConnection()
    ->query("
        SELECT
            created_at AS date,
            level,
            module,
            action,
            message
        FROM system_logs
        ORDER BY id DESC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);

$total = count($rows);

$pages = max(
    1,
    (int)ceil($total / $limit)
);

$offset = ($page - 1) * $limit;

$data = array_slice(
    $rows,
    $offset,
    $limit
);

echo json_encode([

    'success' => true,

    'page' => $page,

    'limit' => $limit,

    'total' => $total,

    'pages' => $pages,

    'data' => array_values($data)

]);
