<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\Application;

$app = new Application();

echo "Timezone: " . date_default_timezone_get() . "<br>";
echo "Hora PHP: " . date('Y-m-d H:i:s');