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
if /i "%~1"=="service" goto :run_service

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

echo [1/4] Menjalankan MQTT subscriber (AC control + device status + suhu raspi)...
call :start_service "SmartAC MQTT Subscriber" "mqtt"

timeout /t 1 /nobreak >nul

echo [2/4] Menjalankan Vite dev server (hot reload CSS/JS)...
call :start_service "SmartAC Vite Dev" "vite"

timeout /t 1 /nobreak >nul

echo [3/4] Menjalankan queue worker dan scheduler...
call :start_service "SmartAC Queue Worker" "queue"
call :start_service "SmartAC Scheduler" "scheduler"

echo.
echo [4/4] Memulai Laravel web server...
echo.
echo ========================================
echo   SEMUA SERVICE BERJALAN
echo ========================================
echo Log service :
echo   - storage\logs\mqtt-subscriber.log
echo   - storage\logs\vite-dev.log
echo   - storage\logs\queue.log
echo   - storage\logs\scheduler.log
echo.

timeout /t 2 /nobreak >nul
start http://127.0.0.1:8000

echo.
echo Menjalankan Laravel web server...
echo.
echo ========================================
echo URL AKSES:
echo   Localhost: http://127.0.0.1:8000
echo   Network  : http://[CARI IP DI IPCONFIG]:8000
echo ========================================
echo.
echo Cari IP di command: ipconfig
echo Cari "IPv4 Address" (contoh: 192.168.1.x)
echo.
"%PHP%" artisan serve --host=0.0.0.0 --port=8000

exit /b %ERRORLEVEL%

:start_service
start "%~1" "%COMSPEC%" /k ""%~f0" service "%~2""
exit /b

:run_service
set "SERVICE=%~2"

if not exist "%ARTISAN%" (
    echo File artisan tidak ditemukan: %ARTISAN%
    echo.
    pause
    exit /b 1
)

if not exist "%LOG_DIR%" mkdir "%LOG_DIR%"

if /i "%SERVICE%"=="mqtt" (
    title SmartAC MQTT Subscriber
    set "SERVICE_NAME=MQTT Subscriber"
    set "SERVICE_LOG=%LOG_DIR%\mqtt-subscriber.log"
    set "SERVICE_COMMAND=%PHP% %ARTISAN% mqtt:subscribe"
    goto :run_mqtt
)

if /i "%SERVICE%"=="vite" (
    title SmartAC Vite Dev
    set "SERVICE_NAME=Vite Dev Server"
    set "SERVICE_LOG=%LOG_DIR%\vite-dev.log"
    set "SERVICE_COMMAND=npm run dev"
    goto :run_vite
)

if /i "%SERVICE%"=="queue" (
    title SmartAC Queue Worker
    set "SERVICE_NAME=Queue Worker"
    set "SERVICE_LOG=%LOG_DIR%\queue.log"
    set "SERVICE_COMMAND=%PHP% %ARTISAN% queue:work --sleep=3 --tries=3"
    goto :run_queue
)

if /i "%SERVICE%"=="scheduler" (
    title SmartAC Scheduler
    set "SERVICE_NAME=Scheduler"
    set "SERVICE_LOG=%LOG_DIR%\scheduler.log"
    set "SERVICE_COMMAND=%PHP% %ARTISAN% schedule:work"
    goto :run_scheduler
)

echo Service "%SERVICE%" tidak dikenal.
pause
exit /b 1

:print_service_header
echo ========================================
echo   SmartAC %SERVICE_NAME%
echo ========================================
echo Project : %PROJECT_DIR%
echo Command : %SERVICE_COMMAND%
echo Log     : %SERVICE_LOG%
echo.
echo Output service akan tampil di sini dan disimpan ke log.
echo Tekan Ctrl+C untuk menghentikan service ini.
echo ========================================
echo.
exit /b

:run_mqtt
call :print_service_header
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { & '%PHP%' '%ARTISAN%' mqtt:subscribe 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath '%SERVICE_LOG%' -Value $_ -Encoding UTF8 } }"
goto :service_stopped

:run_vite
call :print_service_header
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { npm run dev 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath '%SERVICE_LOG%' -Value $_ -Encoding UTF8 } }"
goto :service_stopped

:run_queue
call :print_service_header
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { & '%PHP%' '%ARTISAN%' queue:work --sleep=3 --tries=3 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath '%SERVICE_LOG%' -Value $_ -Encoding UTF8 } }"
goto :service_stopped

:run_scheduler
call :print_service_header
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { & '%PHP%' '%ARTISAN%' schedule:work 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath '%SERVICE_LOG%' -Value $_ -Encoding UTF8 } }"
goto :service_stopped

:service_stopped
echo.
echo Service berhenti. Cek log: %SERVICE_LOG%
echo.
pause
exit /b %ERRORLEVEL%
