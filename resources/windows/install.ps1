param(
    [string]$InstallToken
)

$ErrorActionPreference = "Stop"

# ============================================================
# CONFIGURACIÓN
# ============================================================

$ServiceName = "BackupCenterAgent"

$InstallDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$AgentDir = Join-Path $InstallDir "agent"
$AgentExe = Join-Path $AgentDir "BackupCenterAgent.exe"
$ConfigPath = Join-Path $AgentDir "config.json"

Write-Host "======================================="
Write-Host "   BackupCenter Agent Installer"
Write-Host "======================================="

Write-Host "DEBUG: SCRIPT INICIADO"
Write-Host "TOKEN:"
Write-Host $InstallToken

# ============================================================
# 1. VALIDAR TOKEN
# ============================================================

Write-Host ""
Write-Host "Validando token..."

$apiUrl = "https://backup.codesicorp.net/api/validate-token.php"

try {

    $body = @{
        InstallToken = $InstallToken
    } | ConvertTo-Json

    $response = Invoke-RestMethod `
        -Uri $apiUrl `
        -Method Post `
        -Body $body `
        -ContentType "application/json"

    if (-not $response.valid) {

        Write-Host "TOKEN INVALIDO O YA INSTALADO"
        exit 1
    }

    Write-Host "TOKEN OK"

}
catch {

    Write-Host "ERROR VALIDANDO TOKEN:"
    Write-Host $_.Exception.Message
    exit 1
}

# ============================================================
# 2. VERIFICAR EJECUTABLE
# ============================================================

Write-Host ""
Write-Host "Verificando BackupCenterAgent.exe..."

if (!(Test-Path $AgentExe)) {

    Write-Host "ERROR: No se encontró el ejecutable:"
    Write-Host $AgentExe

    exit 1
}

Write-Host "Ejecutable encontrado."

# ============================================================
# 3. GENERAR CONFIG.JSON
# ============================================================

Write-Host ""
Write-Host "Generando config.json..."

try {

    $config = @{
        Server = "https://backup.codesicorp.net"

        InstallToken = $InstallToken

        Backup = @{
            Extensions = @(
                "zip",
                "rar",
                "bak"
            )
        }
    }

    $configJson = $config | ConvertTo-Json -Depth 5

    $configJson | Set-Content `
        -Path $ConfigPath `
        -Encoding UTF8

    Write-Host "config.json generado correctamente:"
    Write-Host $ConfigPath

}
catch {

    Write-Host "ERROR GENERANDO CONFIG.JSON:"
    Write-Host $_.Exception.Message

    exit 1
}

# ============================================================
# 4. CREAR / ACTUALIZAR SERVICIO
# ============================================================

Write-Host ""
Write-Host "Configurando servicio: $ServiceName"

try {

    $service = Get-Service `
        -Name $ServiceName `
        -ErrorAction SilentlyContinue

    if ($service) {

        Write-Host "El servicio ya existe."

        if ($service.Status -ne "Stopped") {

            Write-Host "Deteniendo servicio..."

            Stop-Service `
                -Name $ServiceName `
                -Force

            Start-Sleep -Seconds 2
        }

        Write-Host "Eliminando servicio anterior..."

        sc.exe delete $ServiceName | Out-Null

        Start-Sleep -Seconds 2
    }

    Write-Host "Creando servicio..."

    New-Service `
        -Name $ServiceName `
        -BinaryPathName "`"$AgentExe`"" `
        -DisplayName "Backup Center Agent" `
        -Description "Backup Center Agent Service" `
        -StartupType Automatic

    Write-Host "Servicio creado correctamente."

}
catch {

    Write-Host "ERROR CREANDO EL SERVICIO:"
    Write-Host $_.Exception.Message

    exit 1
}

# ============================================================
# 5. INICIAR SERVICIO
# ============================================================

Write-Host ""
Write-Host "Iniciando servicio..."

try {

    Start-Service `
        -Name $ServiceName

    Start-Sleep -Seconds 2

    $service = Get-Service `
        -Name $ServiceName

    if ($service.Status -ne "Running") {

        Write-Host "ERROR: El servicio no quedó en ejecución."

        exit 1
    }

    Write-Host "Servicio ejecutándose correctamente."

}
catch {

    Write-Host "ERROR INICIANDO EL SERVICIO:"
    Write-Host $_.Exception.Message

    exit 1
}

# ============================================================
# 6. MARCAR TOKEN COMO INSTALADO
# ============================================================

Write-Host ""
Write-Host "Servicio instalado correctamente."
Write-Host "Marcando token como instalado..."

$markUrl = "https://backup.codesicorp.net/api/mark-installed.php"

try {

    $body = @{
        InstallToken = $InstallToken
    } | ConvertTo-Json

    $response = Invoke-RestMethod `
        -Uri $markUrl `
        -Method Post `
        -Body $body `
        -ContentType "application/json"

    Write-Host "RESPUESTA:"
    $response | ConvertTo-Json

    if (-not $response.success) {

        Write-Host "ERROR: El servidor no confirmó la instalación."

        exit 1
    }

    Write-Host "TOKEN MARCADO COMO INSTALADO"

}
catch {

    Write-Host "ERROR MARCANDO TOKEN COMO INSTALADO:"
    Write-Host $_.Exception.Message

    exit 1
}

# ============================================================
# FINAL
# ============================================================

Write-Host ""
Write-Host "======================================="
Write-Host " INSTALACION COMPLETADA CORRECTAMENTE"
Write-Host "======================================="

Write-Host "FIN"

exit 0