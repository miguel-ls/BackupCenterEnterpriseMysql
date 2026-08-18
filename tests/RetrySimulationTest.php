<?php

require_once __DIR__ . '/FakeWinScpProvider.php';

$provider = new FakeWinScpProvider();

for ($i = 1; $i <= 3; $i++) {

    try {

        echo "Ejecutando intento {$i}" . PHP_EOL;

        $result = $provider->execute('');

        echo $result . PHP_EOL;

        break;

    } catch (Exception $e) {

        echo $e->getMessage() . PHP_EOL;
    }
}