@echo off
setlocal
cd /d "%~dp0"

title Laravel - installer server

set "PHP_EXE=C:\xampp\php\php.exe"
if not exist "%PHP_EXE%" set "PHP_EXE=php"

echo.
echo  Checking PHP...
"%PHP_EXE%" -v
if errorlevel 1 (
  echo ERROR: PHP not found. Install XAMPP or set PHP_EXE in this .bat file.
  pause
  exit /b 1
)

echo.
echo  Starting built-in server (same as php artisan serve)
echo  URL:  http://127.0.0.1:8000/install
echo  Leave this window OPEN while you install. Close it to stop the server.
echo.

rem Laravel runs PHP -S with cwd = public\ and router = project\server.php
cd /d "%~dp0public"
"%PHP_EXE%" -S 127.0.0.1:8000 "%~dp0server.php"

echo.
echo Server stopped (exit %ERRORLEVEL%).
pause
