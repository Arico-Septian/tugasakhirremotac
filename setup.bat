@echo off
setlocal
chcp 65001 >nul

title SmartAC Setup - Database Migration

set "PROJECT_DIR=%~dp0"
cd /d "%PROJECT_DIR%"
set "ARTISAN=%PROJECT_DIR%artisan"
set "LOG_DIR=%PROJECT_DIR%storage\logs"

set "PHP="
for /f "delims=" %%P in ('where php 2^>nul') do (
    set "PHP=%%P"
    goto :php_found
)

if exist "C:\laragon\bin\php\php-8.4.21-Win32-vs17-x64\php.exe" (
    set "PHP=C:\laragon\bin\php\php-8.4.21-Win32-vs17-x64\php.exe"
    goto :php_found
)

:php_missing
echo PHP tidak ditemukan.
echo Pastikan Laragon sudah aktif dan PHP masuk PATH.
echo.
pause
exit /b 1

:php_found
echo ========================================
echo   SmartAC Setup - Database Migration
echo ========================================
echo Project : %PROJECT_DIR%
echo PHP     : %PHP%
echo.

if not exist artisan (
    echo File artisan tidak ditemukan. Jalankan script ini dari folder project Laravel.
    echo.
    pause
    exit /b 1
)

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

echo Menjalankan database migration...
echo.
"%PHP%" artisan migrate --force 2>>"%LOG_DIR%\setup-error.log"
if errorlevel 1 (
    echo.
    echo ❌ Migration gagal. Periksa:
    echo    - Koneksi database di .env
    echo    - Database sudah dibuat
    echo    - Cek log: storage\logs\setup-error.log
    echo.
    pause
    exit /b 1
)

echo.
echo ✅ Database migration berhasil!
echo.
echo Sekarang jalankan: run-all.bat
echo.
pause
