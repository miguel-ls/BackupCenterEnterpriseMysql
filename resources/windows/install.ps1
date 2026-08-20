param(
    [string]$InstallToken
)

$ErrorActionPreference = "Stop"

Write-Host "============================================"
Write-Host " BackupCenter Agent - Instalacion"
Write-Host "============================================"
Write-Host ""

# ============================================================
# VALIDAR INSTALL TOKEN
# ============================================================

if ([string]::IsNullOrWhiteSpace($InstallToken)) {

    Write-Host "ERROR: InstallToken no proporcionado."
    exit 1
}

$apiUrl = "https://backup.codesicorp.net/api/validate-token.php"

try {

    $body = @{
        InstallToken = $InstallToken
    } | ConvertTo-Json -Compress

    $response = Invoke-RestMethod `
        -Uri $apiUrl `
        -Method Post `
        -Body $body `
        -ContentType "application/json"

    if (-not $response.valid) {

        Write-Host "ERROR: InstallToken invalido o ya utilizado."
        exit 1
    }

    Write-Host "InstallToken validado correctamente."
}
catch {

    Write-Host "ERROR: No fue posible validar el InstallToken."
    Write-Host $_.Exception.Message
    exit 1
}

# ============================================================
# CONFIGURAR AGENT (ProgramData)
# ============================================================

$programData = "$env:ProgramData\BackupCenter"
$configPath = Join-Path $programData "config.json"

# Crear carpeta
if (-not (Test-Path $programData)) {
    New-Item -ItemType Directory -Path $programData -Force | Out-Null
}

$config = @{
    Server = "https://backup.codesicorp.net"
    InstallToken = $InstallToken
    Backup = @{
        Extensions = @("zip","rar","bak")
    }
}

$config |
    ConvertTo-Json -Depth 10 |
    Out-File -FilePath $configPath -Encoding UTF8 -Force

Write-Host "Config creada en ProgramData correctamente."

# ============================================================
# MARCAR INSTALADO
# ============================================================

$markUrl = "https://backup.codesicorp.net/api/mark-installed.php"

try {

    $body = @{
        InstallToken = $InstallToken
    } | ConvertTo-Json -Compress

    Invoke-RestMethod `
        -Uri $markUrl `
        -Method Post `
        -Body $body `
        -ContentType "application/json"

    Write-Host "Instalacion registrada en Backup Center."
}
catch {

    Write-Host "ERROR: No fue posible registrar la instalacion."
    Write-Host $_.Exception.Message
    exit 1
}

# ============================================================
# FINALIZAR
# ============================================================

Write-Host ""
Write-Host "============================================"
Write-Host " Instalacion completada correctamente."
Write-Host "============================================"

exit 0