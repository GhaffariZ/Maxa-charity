@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\local.ps1" -Action start
if errorlevel 1 (
    pause
    exit /b 1
)
start "" "http://localhost:8080/home"
