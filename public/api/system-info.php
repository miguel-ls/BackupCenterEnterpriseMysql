<?php

use BackupCenter\Core\ApiController;
use BackupCenter\Core\ApiResponse;
use BackupCenter\Core\Paths;
use BackupCenter\Core\ProcessRunner;
use BackupCenter\Core\Auth;

require_once __DIR__ . '/bootstrap.php';

ApiController::boot();
Auth::require();
ApiController::method('GET');

/*
|--------------------------------------------------------------------------
| Informacion del sistema (CPU, memoria, SO, uptime)
|--------------------------------------------------------------------------
|
| Antes esto hacia 6 llamadas separadas a powershell.exe (una por cada
| dato). Ahora es un solo script que trae todo de una vez, con timeout
| real y cacheado unos segundos para que varias pestanas/usuarios no
| disparen PowerShell en simultaneo.
|
*/

function getWindowsSystemInfo(): array
{
    $cacheFile = Paths::temp() . '/system_info.cache.json';
    $cacheTtlSeconds = 10;

    if (is_file($cacheFile) && (time() - (int) filemtime($cacheFile)) <= $cacheTtlSeconds) {
        $cached = json_decode((string) file_get_contents($cacheFile), true);

        if (\is_array($cached)) {
            return $cached;
        }
    }

    $script = <<<'PS'
$ErrorActionPreference = 'SilentlyContinue'
$os = Get-CimInstance Win32_OperatingSystem
$cpuObj = Get-CimInstance Win32_Processor | Select-Object -First 1
$boot = $os.LastBootUpTime
$span = New-TimeSpan -Start $boot -End (Get-Date)
$result = [ordered]@{
    os = $os.Caption
    os_version = $os.Version
    cpu = $cpuObj.LoadPercentage
    total_memory_kb = $os.TotalVisibleMemorySize
    free_memory_kb = $os.FreePhysicalMemory
    uptime_days = $span.Days
    uptime_hours = $span.Hours
    uptime_minutes = $span.Minutes
}
$result | ConvertTo-Json -Compress
PS;

    $powershell = 'C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe';

    $result = ProcessRunner::run(
        [$powershell, '-NoProfile', '-ExecutionPolicy', 'Bypass', '-Command', $script],
        5
    );

    $data = [];

    if (!$result['timedOut'] && $result['output'] !== '') {
        $decoded = json_decode($result['output'], true);

        if (\is_array($decoded)) {
            $data = $decoded;
        }
    }

    $dir = Paths::temp();

    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    @file_put_contents($cacheFile, json_encode($data));

    return $data;
}

$info = getWindowsSystemInfo();

$totalMemory = round((float) ($info['total_memory_kb'] ?? 0) / 1024 / 1024, 1);
$freeMemory = round((float) ($info['free_memory_kb'] ?? 0) / 1024 / 1024, 1);
$usedMemory = round($totalMemory - $freeMemory, 1);

$hasUptime = isset($info['uptime_days'], $info['uptime_hours'], $info['uptime_minutes']);

$uptime = $hasUptime
    ? sprintf(
        '%d dias %d horas %d min',
        (int) $info['uptime_days'],
        (int) $info['uptime_hours'],
        (int) $info['uptime_minutes']
    )
    : '-';

/*
|--------------------------------------------------------------------------
| Disco (esto no depende de PowerShell, se queda igual)
|--------------------------------------------------------------------------
*/

$diskFree = round(
    disk_free_space(dirname(__DIR__, 2)) / 1024 / 1024 / 1024,
    2
);

$diskTotal = round(
    disk_total_space(dirname(__DIR__, 2)) / 1024 / 1024 / 1024,
    2
);

ApiResponse::success([

    "hostname" => gethostname(),

    "os" => $info['os'] ?? '-',

    "os_version" => $info['os_version'] ?? '-',

    "php_version" => PHP_VERSION,

    "time" => date("Y-m-d H:i:s"),

    "cpu" => round((float) ($info['cpu'] ?? 0), 1),

    "memory_total" => $totalMemory,

    "memory_used" => $usedMemory,

    "memory_free" => $freeMemory,

    "disk_total" => $diskTotal,

    "disk_free" => $diskFree,

    "uptime" => $uptime

]);
