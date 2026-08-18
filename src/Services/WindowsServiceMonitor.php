<?php

namespace BackupCenter\Services;

use BackupCenter\Core\Paths;
use BackupCenter\Core\ProcessRunner;

class WindowsServiceMonitor
{
    private string $powershell;
    private int $timeoutSeconds;
    private int $cacheTtlSeconds;

    public function __construct(
        int $timeoutSeconds = 5,
        int $cacheTtlSeconds = 4
    ) {
        $this->powershell =
            'C:\\Windows\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';

        $this->timeoutSeconds = $timeoutSeconds;
        $this->cacheTtlSeconds = $cacheTtlSeconds;
    }

    /**
     * Antes esto hacia 5 llamadas a powershell.exe por separado
     * (status, pid, memory, cpu, started_at). Ahora es un solo script
     * que trae todo de una vez, y el resultado se cachea un par de
     * segundos para que el polling del Dashboard cada 5s no dispare
     * un powershell.exe nuevo en cada peticion.
     */
    public function getServiceInfo(string $service): array
    {
        $cached = $this->readCache($service);

        if ($cached !== null) {
            return $cached;
        }

        $result = ProcessRunner::run(
            [
                $this->powershell,
                '-NoProfile',
                '-ExecutionPolicy',
                'Bypass',
                '-Command',
                $this->buildInfoScript($service),
            ],
            $this->timeoutSeconds
        );

        $info = $this->parseInfo($service, $result);

        $this->writeCache($service, $info);

        return $info;
    }

    public function getStatus(string $service): string
    {
        return $this->getServiceInfo($service)['status'];
    }

    public function exists(string $service): bool
    {
        return $this->getServiceInfo($service)['exists'];
    }

    private function buildInfoScript(string $service): string
    {
        $safeName = str_replace("'", "''", $service);

        return <<<PS
\$ErrorActionPreference = 'SilentlyContinue'
\$svc = Get-Service -Name '{$safeName}' -ErrorAction SilentlyContinue
\$result = [ordered]@{
    exists = [bool]\$svc
    status = if (\$svc) { \$svc.Status.ToString() } else { 'Unknown' }
    pid = \$null
    memory = \$null
    cpu = \$null
    started_at = \$null
}
if (\$svc) {
    \$cim = Get-CimInstance Win32_Service -Filter "Name='{$safeName}'" -ErrorAction SilentlyContinue
    if (\$cim -and \$cim.ProcessId -gt 0) {
        \$result.pid = \$cim.ProcessId
        \$p = Get-Process -Id \$cim.ProcessId -ErrorAction SilentlyContinue
        if (\$p) {
            \$result.memory = [math]::Round(\$p.WorkingSet64/1MB,2)
            \$result.cpu = [math]::Round(\$p.CPU,2)
            \$result.started_at = \$p.StartTime.ToString('yyyy-MM-dd HH:mm:ss')
        }
    }
}
\$result | ConvertTo-Json -Compress
PS;
    }

    private function parseInfo(string $service, array $result): array
    {
        $default = [
            'name' => $service,
            'exists' => false,
            'status' => 'Desconocido',
            'pid' => null,
            'memory' => '-',
            'cpu' => '-',
            'started_at' => '-',
        ];

        if ($result['timedOut'] || $result['output'] === '') {
            return $default;
        }

        $data = json_decode($result['output'], true);

        if (!\is_array($data)) {
            return $default;
        }

        $statusMap = [
            'RUNNING' => 'Activo',
            'STOPPED' => 'Inactivo',
            'PAUSED' => 'Pausado',
        ];

        $status = strtoupper((string) ($data['status'] ?? ''));

        return [
            'name' => $service,
            'exists' => (bool) ($data['exists'] ?? false),
            'status' => $statusMap[$status] ?? 'Desconocido',
            'pid' => isset($data['pid']) && $data['pid'] !== null ? (int) $data['pid'] : null,
            'memory' => isset($data['memory']) && $data['memory'] !== null
                ? round((float) $data['memory'], 2) . ' MB'
                : '-',
            'cpu' => isset($data['cpu']) && $data['cpu'] !== null
                ? round((float) $data['cpu'], 2) . ' s'
                : '-',
            'started_at' => $data['started_at'] ?? '-',
        ];
    }

    private function cacheFile(string $service): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', $service);

        return Paths::temp() . '/service_' . $safe . '.cache.json';
    }

    private function readCache(string $service): ?array
    {
        $file = $this->cacheFile($service);

        if (!is_file($file)) {
            return null;
        }

        if ((time() - (int) filemtime($file)) > $this->cacheTtlSeconds) {
            return null;
        }

        $data = json_decode((string) file_get_contents($file), true);

        return \is_array($data) ? $data : null;
    }

    private function writeCache(string $service, array $info): void
    {
        $dir = Paths::temp();

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        @file_put_contents($this->cacheFile($service), json_encode($info));
    }
}
