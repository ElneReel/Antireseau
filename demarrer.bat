@echo off
title Insociable - Serveur Local
cd /d "%~dp0"
set "PATH=C:\Users\AllZ\.tools\php;C:\Users\AllZ\.tools;%PATH%"

echo ========================================================
echo    Insociable : Personne a suivre. Tout le monde ici.
echo ========================================================
echo.
echo Verification du serveur...
echo Lancement sur http://127.0.0.1:8000
echo.

start http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
pause
