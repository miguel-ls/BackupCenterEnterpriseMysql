<?php

namespace BackupCenter\Services;

use BackupCenter\Core\Database;
use BackupCenter\Core\Paths;


class SftpGoService
{
    private array $settings;

    public function __construct()
    {
        $db = new Database(
            Paths::database() . '/backupcenter.db'
        );

        $pdo = $db->getConnection();

        $this->settings = $pdo->query("
            SELECT *
            FROM settings
            WHERE id=1
        ")->fetch(\PDO::FETCH_ASSOC);
    }

    public function isEnabled(): bool
    {
        return (int)($this->settings['sftpgo_enabled'] ?? 0) === 1;
    }

    private function token(): string
    {
        $url = "http://{$this->settings['sftpgo_host']}:{$this->settings['sftpgo_port']}/api/v2/token";

        $ch = curl_init($url);

        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPGET => true,

            CURLOPT_USERPWD =>
                $this->settings["sftpgo_username"] .
                ":" .
                $this->settings["sftpgo_password"],

            CURLOPT_HTTPAUTH => CURLAUTH_BASIC

        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        }

        curl_close($ch);

        $json = json_decode($response, true);

        if (empty($json["access_token"])) {

            throw new \Exception("No se pudo obtener el token.");

        }

        return $json["access_token"];
    }

    public function getToken(): string
    {
        return $this->token();
    }

    public function generatePassword(int $length = 16): string
    {
        $characters =
            'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*';

        $password = '';

        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {

            $password .= $characters[random_int(0, $max)];

        }

        return $password;
    }    

    public function createUser(
        string $username,
        string $password
    ): array
    {
        $token = $this->getToken();

        $home = rtrim(
            $this->settings["sftpgo_base_path"],
            "/"
        ) . "/" . $username;

        $data = [

            "status" => 1,

            "username" => $username,

            "password" => $password,

            "home_dir" => $home,

            "permissions" => [

                "/" => ["*"]

            ]

        ];

        $url =
            "http://{$this->settings['sftpgo_host']}:{$this->settings['sftpgo_port']}/api/v2/users";

        $ch = curl_init($url);

        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => json_encode($data),

            CURLOPT_HTTPHEADER => [

                "Authorization: Bearer {$token}",

                "Content-Type: application/json"

            ]

        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [

            "status" => $status,

            "response" => json_decode($response, true),

            "raw" => $response

        ];
    }    

    private function fixPermissions(string $directory): void
    {
        if (!file_exists($directory)) {
            return;
        }

        chown($directory, 'apps');
        chgrp($directory, 'apps');

        $items = array_diff(scandir($directory), ['.', '..']);

        foreach ($items as $item) {

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            chown($path, 'apps');
            chgrp($path, 'apps');

            if (is_dir($path)) {
                $this->fixPermissions($path);
            }

        }
    }



    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = array_diff(scandir($directory), ['.', '..']);

        foreach ($items as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

public function deleteUser(string $username): bool
{
    $username = trim($username);

    $token = $this->getToken();

    $url = sprintf(
        "http://%s:%d/api/v2/users/%s",
        trim($this->settings['sftpgo_host']),
        (int)$this->settings['sftpgo_port'],
        rawurlencode($username)
    );

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => "DELETE",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}",
            "Accept: application/json"
        ],
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new \Exception($error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($status === 404) {
        // Si ya no existe el usuario, lo consideramos eliminado.
        return true;
    }

    if (!in_array($status, [200, 204], true)) {
        throw new \Exception(
            "No se pudo eliminar el usuario SFTPGo. HTTP {$status}. {$response}"
        );
    }

    return true;
}

public function getUser(string $username): array
{
    $token = $this->getToken();

    $url = sprintf(
        "http://%s:%d/api/v2/users/%s",
        trim($this->settings['sftpgo_host']),
        (int)$this->settings['sftpgo_port'],
        rawurlencode(trim($username))
    );

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}",
            "Accept: application/json"
        ]
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new \Exception($error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($status !== 200) {
        throw new \Exception(
            "No se pudo obtener el usuario SFTPGo. HTTP {$status}"
        );
    }

    $user = json_decode($response, true);

    if (!is_array($user)) {
        throw new \Exception('Respuesta JSON inválida de SFTPGo.');
    }

    return $user;
}

public function resetPassword(string $username): array
{
    $current = $this->getUser($username);

    $password = $this->generatePassword();

    $user = [
        "status" => $current["status"] ?? 1,
        "username" => $current["username"],
        "password" => $password,
        "home_dir" => $current["home_dir"],
        "uid" => $current["uid"] ?? 0,
        "gid" => $current["gid"] ?? 0,
        "max_sessions" => $current["max_sessions"] ?? 0,
        "quota_size" => $current["quota_size"] ?? 0,
        "quota_files" => $current["quota_files"] ?? 0,
        "permissions" => $current["permissions"],
        "upload_data_transfer" => $current["upload_data_transfer"] ?? 0,
        "download_data_transfer" => $current["download_data_transfer"] ?? 0,
        "total_data_transfer" => $current["total_data_transfer"] ?? 0,

        // Requerido por SFTPGo 2.7.x.
        // Los objetos vacíos deben serializarse como {} (stdClass) para que el PUT sea aceptado.
        "filesystem" => [
            "provider" => 0,
            "osconfig" => new \stdClass(),
            "s3config" => new \stdClass(),
            "gcsconfig" => new \stdClass(),
            "azblobconfig" => new \stdClass(),
            "cryptconfig" => new \stdClass(),
            "sftpconfig" => new \stdClass(),
            "httpconfig" => new \stdClass()
        ]
    ];

    $payload = json_encode(
        $user,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );

    if ($payload === false) {
        throw new \Exception('Error generando JSON: ' . json_last_error_msg());
    }

    $token = $this->getToken();

    $url = sprintf(
        "http://%s:%d/api/v2/users/%s",
        trim($this->settings['sftpgo_host']),
        (int)$this->settings['sftpgo_port'],
        rawurlencode(trim($username))
    );

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}",
            "Content-Type: application/json",
            "Accept: application/json"
        ]
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new \Exception($error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if (!in_array($status, [200, 202], true)) {
        throw new \Exception(sprintf(
            'SFTPGo devolvió HTTP %d%s',
            $status,
            $response ? ': ' . $response : ''
        ));
    }

    // Sincronizar la nueva contraseña en la tabla connections
    $db = new Database(
        Paths::database() . '/backupcenter.db'
    );

    $pdo = $db->getConnection();

    $stmt = $pdo->prepare("
        UPDATE connections
        SET password = ?
        WHERE username = ?
    ");

    $stmt->execute([
        $password,
        $username
    ]);

    return [
        "username" => $username,
        "password" => $password
    ];
}

    public function deleteClientFolder(string $alias): bool
    {
        $folder = rtrim(
            $this->settings["sftpgo_base_path"],
            "/\\"
        ) . DIRECTORY_SEPARATOR . $alias;

        if (!is_dir($folder)) {
            return true;
        }

        $items = array_diff(scandir($folder), ['.', '..']);

        if (count($items) > 0) {
            return false;
        }

        return rmdir($folder);
    }

    private function cloneTemplate(string $template, string $home): void
    {
        $command = sprintf(
            'cp -a %s %s 2>&1',
            escapeshellarg($template),
            escapeshellarg($home)
        );

        exec($command, $output, $result);

        if ($result !== 0) {
            throw new \Exception(
                "No se pudo crear la carpeta del cliente.\n" .
                implode("\n", $output)
            );
        }
    }
    
    public function provisionClient(string $alias): array
    {
        $basePath = rtrim(
            $this->settings["sftpgo_base_path"],
            "/\\"
        );

        $template = $basePath . DIRECTORY_SEPARATOR . "_template";
        $home = $basePath . DIRECTORY_SEPARATOR . $alias;

        if (is_dir($home)) {

            throw new \Exception(
                "La carpeta del cliente ya existe."
            );

        }

        if (!is_dir($template)) {

            throw new \Exception(
                "No existe la carpeta _template."
            );

        }

        try {

            // Clonar el template preservando propietario, grupo y permisos
            $command = sprintf(
                'cp -a %s %s 2>&1',
                escapeshellarg($template),
                escapeshellarg($home)
            );

            exec($command, $output, $result);

            if ($result !== 0) {

                throw new \Exception(
                    "No se pudo crear la carpeta del cliente.\n" .
                    implode("\n", $output)
                );

            }

            // Generar contraseña
            $password = $this->generatePassword();

            // Crear usuario en SFTPGo
            $result = $this->createUser(
                $alias,
                $password
            );

            if ($result["status"] != 201) {

                throw new \Exception(
                    "No fue posible crear el usuario en SFTPGo."
                );

            }

            return [

                "username" => $alias,

                "password" => $password,

                "home_dir" => $home

            ];

        } catch (\Throwable $e) {

            // Si ocurre un error, eliminar la carpeta creada
            $this->deleteDirectory($home);

            throw $e;

        }
    }
}