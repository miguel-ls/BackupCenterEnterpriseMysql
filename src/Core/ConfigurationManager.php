<?php

namespace BackupCenter\Core;

use BackupCenter\Contracts\IConfiguration;
use BackupCenter\Models\ConnectionConfig;

class ConfigurationManager implements IConfiguration
{
    private array $config = [];

    public function __construct(string $configFile)
    {
        if (!file_exists($configFile)) {
            throw new \Exception("Configuration file not found: {$configFile}");
        }

        $json = file_get_contents($configFile);

        $this->config = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON configuration.");
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        $value = $this->config;

        foreach ($keys as $item) {

            if (!isset($value[$item])) {
                return $default;
            }

            $value = $value[$item];
        }

        return $value;
    }

    /**
     * @deprecated Las credenciales SFTP ya no viven en config.json.
     * Los trabajos operativos usan la conexion de la tabla `connections`
     * a traves de JobConfiguration::getConnectionConfig(). Este metodo
     * solo existia para el agente directo legado (agent.php,
     * ServiceHost) y ahora lanza una excepcion clara en vez de conectar
     * con credenciales vacias si alguien lo llega a invocar.
     */
    public function getConnectionConfig(): ConnectionConfig
    {
        throw new \RuntimeException(
            'config.json ya no contiene credenciales SFTP. '
            . 'Este modo (agente directo) esta descontinuado; '
            . 'los backups se ejecutan por trabajo desde la tabla connections.'
        );
    }

    public function all(): array
    {
        return $this->config;
    }
}