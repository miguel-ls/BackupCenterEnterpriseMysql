<?php

class FakeWinScpProvider
{
    private int $attempt = 0;

    public function execute(string $script): string
    {
        $this->attempt++;

        echo "Intento {$this->attempt}" . PHP_EOL;

        if ($this->attempt < 3) {
            throw new Exception("Error de conexión SFTP");
        }

        return "Archivo subido correctamente.";
    }
}