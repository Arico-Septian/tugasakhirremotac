@echo off
setlocal
chcp 65001 >nul

title SmartAC Services Launcher

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
echo   SmartAC Laravel Services Launcher
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

echo [1/4] Menjalankan migration...
"%PHP%" artisan migrate --force 2>>"%LOG_DIR%\launcher-error.log"
if errorlevel 1 (
    echo.
    echo Migration gagal. Periksa koneksi database dan konfigurasi .env.
    echo.
    pause
    exit /b 1
)

echo.
echo [2/4] Menjalankan MQTT subscriber (AC control + device status + suhu raspi)...
start /min "SmartAC MQTT Subscriber" cmd /c ""%PHP%" "%ARTISAN%" mqtt:subscribe >> "%LOG_DIR%\mqtt-subscriber.log" 2>&1"

timeout /t 1 /nobreak >nul

echo [3/4] Menjalankan MQTT listener (suhu ruangan)...
start /min "SmartAC Temperature Listener" cmd /c ""%PHP%" "%ARTISAN%" app:mqtt-listener >> "%LOG_DIR%\temperature-listener.log" 2>&1"

timeout /t 1 /nobreak >nul

echo [4/4] Menjalankan queue worker dan scheduler...
start /min "SmartAC Queue Worker" cmd /c ""%PHP%" "%ARTISAN%" queue:work --sleep=3 --tries=3 >> "%LOG_DIR%\queue.log" 2>&1"
start /min "SmartAC Scheduler" cmd /c ""%PHP%" "%ARTISAN%" schedule:work >> "%LOG_DIR%\scheduler.log" 2>&1"

echo.
echo ========================================
echo   SEMUA SERVICE BERJALAN
echo ========================================
echo Buka: http://127.0.0.1:8000
echo Log service :
echo   - storage\logs\mqtt-subscriber.log
echo   - storage\logs\temperature-listener.log
echo   - storage\logs\queue.log
echo   - storage\logs\scheduler.log
echo.

timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000

echo [5/4] Menjalankan Laravel web server di http://127.0.0.1:8000
echo Biarkan window ini tetap terbuka.
echo.
"%PHP%" artisan serve --host=127.0.0.1 --port=8000
