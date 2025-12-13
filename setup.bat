@echo off
REM Windows Batch Script untuk setup helpdesk
REM Run dengan Admin privileges

setlocal enabledelayedexpansion

echo.
echo ============================================================
echo  HELPDESK SETUP SCRIPT - MTsN 11 Majalengka
echo ============================================================
echo.

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Harap jalankan sebagai Administrator
    pause
    exit /b 1
)

REM Get current directory
set SCRIPT_DIR=%~dp0
cd /d "%SCRIPT_DIR%"

echo [1/4] Checking if .env file exists...
if not exist ".env" (
    echo Creating .env from .env.example...
    copy ".env.example" ".env"
    echo ✓ .env created. Silakan edit dengan credentials database Anda.
) else (
    echo ✓ .env file sudah ada
)

echo.
echo [2/4] Setting folder permissions...
if exist "public\uploads" (
    echo ✓ Folder public\uploads sudah ada
) else (
    echo Creating public\uploads folder...
    mkdir "public\uploads"
    echo ✓ Folder created
)

if exist "logs" (
    echo ✓ Folder logs sudah ada
) else (
    echo Creating logs folder...
    mkdir "logs"
    echo ✓ Folder created
)

echo.
echo [3/4] Checking requirements...
echo.

REM Check PHP
for /f "tokens=*" %%i in ('php -v 2^>nul ^| findstr /R "^PHP [0-9]"') do (
    echo ✓ %%i
    set PHP_FOUND=1
)
if not defined PHP_FOUND (
    echo ✗ PHP tidak ditemukan. Silakan install PHP 7.4+
)

REM Check MySQL
for /f "tokens=*" %%i in ('mysql --version 2^>nul') do (
    echo ✓ %%i
    set MYSQL_FOUND=1
)
if not defined MYSQL_FOUND (
    echo ✗ MySQL tidak ditemukan. Silakan install MySQL 5.7+
)

echo.
echo [4/4] Setup Summary
echo ============================================================
echo.
echo Project: Helpdesk MTsN 11 Majalengka
echo Directory: %SCRIPT_DIR%
echo.
echo Files created:
echo   ✓ .env - Environment configuration
echo   ✓ public/uploads - File uploads folder
echo   ✓ logs - Application logs folder
echo.
echo NEXT STEPS:
echo   1. Edit .env dengan database credentials Anda
echo   2. Import database.sql ke MySQL:
echo      mysql -u root -p mtsnmaja_helpdesk < database.sql
echo   3. Akses http://localhost/helpdesk/
echo   4. Login dengan admin / admin123
echo   5. Ganti password admin
echo.
echo URL Penting:
echo   Landing:     http://localhost/helpdesk/
echo   Login:       http://localhost/helpdesk/login.php
echo   Dashboard:   http://localhost/helpdesk/src/admin/dashboard.php
echo.
echo ============================================================

pause
