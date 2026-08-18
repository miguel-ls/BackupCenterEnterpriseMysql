<?php

echo PHP_EOL;
echo "===============================" . PHP_EOL;
echo " Reset Development Environment" . PHP_EOL;
echo "===============================" . PHP_EOL;

$db = __DIR__ . "/storage/database/backupcenter.db";

if (file_exists($db)) {

    if (@unlink($db)) {

        echo "Base eliminada." . PHP_EOL;

    } else {

        echo "No se pudo eliminar la base. Verifica que ningún proceso la esté utilizando." . PHP_EOL;

        exit(1);
    }
}

$php = PHP_BINARY;

passthru("\"{$php}\" install.php");

echo PHP_EOL;

passthru("\"{$php}\" seed.php");

echo PHP_EOL;
echo "Entorno listo." . PHP_EOL;