<?php

require_once __DIR__ . '/vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

$summary = $app->agent()->run(
    $app->config()
);

if (
    in_array('--json', $argv)
) {

    header('Content-Type: application/json');

    echo json_encode(
        $summary,
        JSON_PRETTY_PRINT
    );

    exit(0);
}
