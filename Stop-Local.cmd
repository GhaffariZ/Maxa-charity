@echo off
set "MACSA_LOCAL_PS=%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe"
if exist "%SystemRoot%\Sysnative\WindowsPowerShell\v1.0\powershell.exe" set "MACSA_LOCAL_PS=%SystemRoot%\Sysnative\WindowsPowerShell\v1.0\powershell.exe"
"%MACSA_LOCAL_PS%" -NoProfile -ExecutionPolicy Bypass -File "%~dp0tools\local.ps1" -Action stop
if errorlevel 1 pause
