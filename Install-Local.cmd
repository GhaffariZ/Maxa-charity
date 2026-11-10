@echo off
set "MACSA_LOCAL_PS=%SystemRoot%\System32\WindowsPowerShell\v1.0\powershell.exe"
if exist "%SystemRoot%\Sysnative\WindowsPowerShell\v1.0\powershell.exe" set "MACSA_LOCAL_PS=%SystemRoot%\Sysnative\WindowsPowerShell\v1.0\powershell.exe"
"%MACSA_LOCAL_PS%" -NoProfile -STA -ExecutionPolicy Bypass -File "%~dp0tools\local.ps1" -Action setup -Dump "%~1"
if errorlevel 1 (
    echo.
    echo Installation did not complete. Read the error above, then run this file again.
    pause
    exit /b 1
)
echo.
echo Installation completed. Use Start-Local.cmd and Stop-Local.cmd next time.
start "" "http://localhost:8080/home"
pause
