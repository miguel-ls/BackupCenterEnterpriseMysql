@echo off
title BackupCenter Enterprise Agent Uninstaller
color 0C

echo.
echo ====================================================
echo     BackupCenter Enterprise Agent Uninstaller
echo ====================================================
echo.

:: Verificar permisos de administrador
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Este desinstalador debe ejecutarse como Administrador.
    echo.
    pause
    exit /b 1
)

:: Verificar uninstall.ps1
if not exist "%~dp0windows\uninstall.ps1" (
    echo.
    echo ERROR: No se encontro windows\uninstall.ps1
    pause
    exit /b 1
)

echo Iniciando desinstalacion...
echo.

powershell.exe -ExecutionPolicy Bypass -File "%~dp0windows\uninstall.ps1"

echo.
echo ====================================================
echo Desinstalacion finalizada.
echo ====================================================
echo.

pause