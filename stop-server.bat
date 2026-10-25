@echo off
title Stop ApexPlanet Task-3 Servers
cls

echo ========================================================
echo       Stopping ApexPlanet Task-3 Servers
echo ========================================================
echo.

echo Stopping PHP server...
taskkill /F /IM php.exe >NUL 2>&1
if "%ERRORLEVEL%"=="0" (
    echo [OK] PHP server stopped.
) else (
    echo [INFO] PHP server was not running.
)

echo.
echo Stopping MySQL / MariaDB server...
taskkill /F /IM mysqld.exe >NUL 2>&1
if "%ERRORLEVEL%"=="0" (
    echo [OK] MariaDB / MySQL server stopped.
) else (
    echo [INFO] MariaDB / MySQL server was not running.
)

echo.
echo ========================================================
echo  All services have been stopped.
echo ========================================================
pause
