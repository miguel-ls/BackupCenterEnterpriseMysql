@echo off
title BackupCenter Enterprise Agent Installer
color 0A

echo.
echo ====================================================
echo      BackupCenter Enterprise Agent Installer
echo ====================================================
echo.

:: Verificar permisos de administrador
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Este instalador debe ejecutarse como Administrador.
    echo.
    pause
    exit /b 1
)

:: Verificar config.json
if not exist "%~dp0config.json" (
    echo.
    echo ERROR: No se encontro config.json
    pause
    exit /b 1
)

:: Verificar install.ps1
if not exist "%~dp0windows\install.ps1" (
    echo.
    echo ERROR: No se encontro windows\install.ps1
    pause
    exit /b 1
)


echo Iniciando instalacion...
echo.

powershell.exe -ExecutionPolicy Bypass -File "%~dp0windows\install.ps1"


echo.
echo ====================================================
echo Instalacion finalizada.
echo ====================================================
echo.
pause