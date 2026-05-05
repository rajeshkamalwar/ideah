@echo off
cd /d "%~dp0"
echo Creating CA bundle (needed for HTTPS / license check)...
if not exist "C:\xampp\php\php.exe" (
  echo Run with full path to php.exe if XAMPP is elsewhere.
  php install-cacert.php
) else (
  "C:\xampp\php\php.exe" install-cacert.php
)
if errorlevel 1 (
  echo.
  echo If this failed, open install-cacert.php in an editor and run it from a terminal.
  pause
  exit /b 1
)
echo.
echo php.ini should point to: %cd%\storage\certs\cacert.pem
echo Restart your PHP server, then retry the installer license step.
pause
