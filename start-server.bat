@echo off
title ApexPlanet Task-3 - User Management Server
echo ====================================================
echo Starting MySQL / MariaDB Server...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL is already running.
) else (
    start "" /B "C:\Program Files\MariaDB 13.0\bin\mysqld.exe"
    echo [OK] MySQL server started on port 3306.
)
echo ====================================================
echo Starting PHP server at http://localhost:8000
echo Open http://localhost:8000 in your browser.
echo Press Ctrl+C to stop the server.
echo ====================================================
php -S localhost:8000
