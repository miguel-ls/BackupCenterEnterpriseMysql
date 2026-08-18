<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

$db = $app->database()->getConnection();

echo "<h2>Base de datos</h2>";

echo $db->query("PRAGMA database_list")->fetchAll(PDO::FETCH_ASSOC)[0]['file'];

echo "<hr>";

echo "<h2>Insertando...</h2>";

$db->exec("
INSERT INTO job_queue
(
    job_id,
    status
)
VALUES
(
    999,
    'Pending'
)
");

echo "INSERT OK";

echo "<hr>";

$stmt = $db->query("SELECT * FROM job_queue");

echo "<pre>";

print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "</pre>";