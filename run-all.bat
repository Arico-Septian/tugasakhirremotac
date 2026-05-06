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

if exist "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" (
    set "PHP=C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
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

echo [1/5] Menjalankan migration...
"%PHP%" artisan migrate --force 2>>"%LOG_DIR%\launcher-error.log"
if errorlevel 1 (
    echo.
    echo Migration gagal. Periksa koneksi database dan konfigurasi .env.
    echo.
    pause
    exit /b 1
)

echo.
echo [2/5] Menjalankan MQTT subscriber untuk kontrol AC
start /min "SmartAC MQTT Subscriber" cmd /c ""%PHP%" "%ARTISAN%" mqtt:subscribe >> "%LOG_DIR%\mqtt-subscriber.log" 2>&1"

timeout /t 1 /nobreak >nul

echo [3/5] Menjalankan MQTT listener untuk suhu ruangan
start /min "SmartAC Temperature Listener" cmd /c ""%PHP%" "%ARTISAN%" app:mqtt-listener >> "%LOG_DIR%\temperature-listener.log" 2>&1"

timeout /t 1 /nobreak >nul

echo [4/5] Menjalankan scheduler untuk device status dan timer AC
start /min "SmartAC Scheduler" cmd /c ""%PHP%" "%ARTISAN%" schedule:work >> "%LOG_DIR%\scheduler.log" 2>&1"

timeout /t 1 /nobreak >nul

echo.
echo ========================================
echo   SERVICES STARTED
echo ========================================
echo Buka: http://127.0.0.1:8000
echo.
echo Biarkan window ini tetap terbuka untuk Laravel server.
echo Service MQTT dan scheduler berjalan di background.
echo Log service ada di:
echo - storage\logs\mqtt-subscriber.log
echo - storage\logs\temperature-listener.log
echo - storage\logs\scheduler.log
echo.
echo [5/5] Menjalankan Laravel server di http://127.0.0.1:8000
echo Jika server berhasil, akan muncul tulisan:
echo INFO  Server running on [http://127.0.0.1:8000].
echo.
"%PHP%" artisan serve --host=127.0.0.1 --port=8000 2>>"%LOG_DIR%\server-error.log"

echo.
echo Laravel server berhenti atau gagal berjalan.
echo Kirim screenshot isi window ini kalau masih gagal.
pause
