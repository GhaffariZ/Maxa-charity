@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\local.ps1" -Action stop
if errorlevel 1 pause
