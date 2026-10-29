@echo off
setlocal enabledelayedexpansion
title ApexPlanet Task-3 - User Management Server
cls

echo ========================================================
echo       ApexPlanet Task-3 - User Management Server
echo ========================================================
echo.

echo [1/2] Checking MySQL / MariaDB Server...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if !ERRORLEVEL!==0 (
    echo [OK] MySQL / MariaDB is already running on port 3306.
) else (
    echo Starting MySQL / MariaDB server...
    if exist "C:\Program Files\MariaDB 13.0\bin\mysqld.exe" (
        start "MariaDB Server" /min "C:\Program Files\MariaDB 13.0\bin\mysqld.exe" --defaults-file="C:\Program Files\MariaDB 13.0\data\my.ini" --console
    ) else if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "MySQL Server" /min "C:\xampp\mysql\bin\mysqld.exe" --console
    ) else (
        start "MySQL Server" /min mysqld --console
    )

    echo Waiting for MariaDB to start...
    ping 127.0.0.1 -n 5 >NUL

    tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
    if !ERRORLEVEL!==0 (
        echo [OK] MySQL server started successfully on port 3306.
    ) else (
        echo [WARNING] MySQL did not respond immediately. Please verify MariaDB service.
    )
)

echo.
echo [2/2] Starting PHP server at http://localhost:8000 ...
echo.
echo ========================================================
echo  Server is running at: http://localhost:8000
echo  Press Ctrl+C to stop the PHP server.
echo ========================================================
echo.

start "" cmd /c "ping 127.0.0.1 -n 2 >NUL & start http://localhost:8000"

php -S localhost:8000
