<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

$db = $app->database()->getConnection();

$sql = "
SELECT
    id,
    started_at,
    client,
    files_found,
    files_uploaded,
    files_skipped,
    errors,
    duration,
    status
FROM execution_history
ORDER BY id DESC
LIMIT 10
";

$stmt = $db->query($sql);

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

    echo "--------------------------------" . PHP_EOL;
    echo "ID        : {$row['id']}" . PHP_EOL;
    echo "Fecha     : {$row['started_at']}" . PHP_EOL;
    echo "Cliente   : {$row['client']}" . PHP_EOL;
    echo "Encontró  : {$row['files_found']}" . PHP_EOL;
    echo "Subidos   : {$row['files_uploaded']}" . PHP_EOL;
    echo "Omitidos  : {$row['files_skipped']}" . PHP_EOL;
    echo "Errores   : {$row['errors']}" . PHP_EOL;
    echo "Duración  : {$row['duration']} s" . PHP_EOL;
    echo "Estado    : {$row['status']}" . PHP_EOL;
}