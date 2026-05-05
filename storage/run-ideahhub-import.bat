@echo off
cd /d "d:\ROCRE\Websites\IDEA\idea"
echo Running ideahhub import into DB ideah (legacy: ideahub)...
php artisan config:clear
php artisan ideah:import-from-ideahhub --media-source="d:\ROCRE\Websites\IDEA\idea\ideahhub\public"
echo Exit code: %ERRORLEVEL%
pause
