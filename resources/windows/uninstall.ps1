# ============================================================
# BackupCenter Agent Uninstaller
# ============================================================

$ErrorActionPreference = "Stop"

$ServiceName = "BackupCenterAgent"
$InstallDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$AgentDir = Join-Path $InstallDir "agent"

# Log
$LogPath = Join-Path $InstallDir "uninstall.log"
Start-Transcript -Path $LogPath -Append

Write-Host "==== Desinstalando BackupCenter Agent ===="

# Verificar servicio
$service = Get-Service -Name $ServiceName -ErrorAction SilentlyContinue

if ($service) {
    Write-Host "Deteniendo servicio..."

    if ($service.Status -ne "Stopped") {
        Stop-Service $ServiceName -Force
        Start-Sleep -Seconds 2
    }

    Write-Host "Eliminando servicio..."
    sc.exe delete $ServiceName | Out-Null
    Start-Sleep -Seconds 2
} else {
    Write-Host "Servicio no existe"
}

# Eliminar archivos del agente
if (Test-Path $AgentDir) {
    Write-Host "Eliminando archivos del agente..."
    Remove-Item $AgentDir -Recurse -Force
} else {
    Write-Host "Directorio del agente no encontrado"
}

# ⚠️ NO eliminamos config en ProgramData (correcto para persistencia)
$ConfigDir = "$env:ProgramData\BackupCenter"

if (Test-Path $ConfigDir) {
    Write-Host "Config preservado en: $ConfigDir"
}

Write-Host "Desinstalación completada correctamente"

Stop-Transcript
exit 0