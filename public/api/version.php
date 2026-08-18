<?php

require_once __DIR__ . '/cors.php';

echo json_encode([
    "success" => true,
    "version" => trim(file_get_contents(__DIR__ . "/../../VERSION")),
    "php" => PHP_VERSION,
    "time" => date("Y-m-d H:i:s")
]);