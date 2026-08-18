<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BackupCenter\Core\ConfigurationManager;
use BackupCenter\Core\Paths;
use BackupCenter\Core\RetryPolicy;

$config = new ConfigurationManager(
    Paths::config() . '/config.json'
);

$policy = new RetryPolicy(
    $config->get('sftp.retry_attempts'),
    $config->get('sftp.retry_delay')
);

echo "Intentos : " . $policy->getAttempts() . PHP_EOL;
echo "Espera   : " . $policy->getDelay() . " segundos" . PHP_EOL;

for ($i = 1; $i <= 4; $i++) {

    echo "Intento {$i}: ";

    if ($policy->shouldRetry($i)) {
        echo "REINTENTAR";
    } else {
        echo "FINALIZAR";
    }

    echo PHP_EOL;
}