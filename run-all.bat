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

echo [0/4] Membersihkan cache konfigurasi (config + cache)...
"%PHP%" "%ARTISAN%" config:clear >nul 2>&1
"%PHP%" "%ARTISAN%" cache:clear >nul 2>&1
"%PHP%" "%ARTISAN%" route:clear >nul 2>&1
"%PHP%" "%ARTISAN%" view:clear >nul 2>&1
echo Cache cleared.
echo.

if not exist "%PROJECT_DIR%public\build\manifest.json" (
    echo Asset Vite belum di-build. Menjalankan "npm run build" pertama kali...
    call npm run build
    echo.
)

echo [1/4] Menjalankan MQTT subscriber (AC control + device status + suhu raspi)...
call :start_service "SmartAC MQTT Subscriber" "mqtt"

timeout /t 1 /nobreak >nul

echo [2/4] Menjalankan Scheduler (AC timer otomatis tiap menit)...
call :start_service "SmartAC Scheduler" "scheduler"

echo.
echo [3/4] Menjalankan Reverb WebSocket server (real-time push)...
call :start_service "SmartAC Reverb Server" "reverb"

timeout /t 1 /nobreak >nul

echo.
echo [4/4] Menjalankan Vite dev server (hot reload JS/CSS)...
call :start_service "SmartAC Vite Dev" "vite"

timeout /t 1 /nobreak >nul

echo.
echo [5/5] Memulai Laravel web server...
echo.
echo ========================================
echo   SEMUA SERVICE BERJALAN
echo ========================================
echo Log service :
echo   - storage\logs\mqtt-subscriber.log
echo   - storage\logs\scheduler.log
echo   - storage\logs\reverb.log
echo   - storage\logs\vite.log
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
    set "SERVICE_LOG=%LOG_DIR%\vite.log"
    set "SERVICE_COMMAND=npm run dev"
    goto :run_vite
)

if /i "%SERVICE%"=="scheduler" (
    title SmartAC Scheduler
    set "SERVICE_NAME=Scheduler"
    set "SERVICE_LOG=%LOG_DIR%\scheduler.log"
    set "SERVICE_COMMAND=%PHP% %ARTISAN% schedule:work"
    goto :run_scheduler
)

if /i "%SERVICE%"=="reverb" (
    title SmartAC Reverb Server
    set "SERVICE_NAME=Reverb Server"
    set "SERVICE_LOG=%LOG_DIR%\reverb.log"
    set "SERVICE_COMMAND=%PHP% %ARTISAN% reverb:start"
    goto :run_reverb
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
echo MQTT subscriber akan otomatis restart kalau crash (delay 5 detik).
echo Tekan Ctrl+C dua kali untuk benar-benar berhenti.
echo.
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { $php='%PHP%'; $artisan='%ARTISAN%'; $log='%SERVICE_LOG%'; while ($true) { $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $startMsg = \"[$ts] === MQTT subscriber start ===\"; Write-Host $startMsg -ForegroundColor Cyan; Add-Content -LiteralPath $log -Value $startMsg -Encoding UTF8; try { & $php $artisan 'mqtt:subscribe' 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath $log -Value $_ -Encoding UTF8 } } catch { $errMsg = \"[$ts] EXCEPTION: $_\"; Write-Host $errMsg -ForegroundColor Red; Add-Content -LiteralPath $log -Value $errMsg -Encoding UTF8 } $exitTs = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $exitMsg = \"[$exitTs] !!! MQTT subscriber exited (code $LASTEXITCODE) - restart dalam 5 detik...\"; Write-Host $exitMsg -ForegroundColor Yellow; Add-Content -LiteralPath $log -Value $exitMsg -Encoding UTF8; Start-Sleep -Seconds 5 } }"
goto :service_stopped

:run_vite
call :print_service_header
echo Vite dev server - hot reload otomatis saat blade/JS/CSS berubah.
echo Auto-restart kalau crash (delay 5 detik). Ctrl+C 2x untuk berhenti.
echo.
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { $log='%SERVICE_LOG%'; while ($true) { $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $startMsg = \"[$ts] === Vite dev start ===\"; Write-Host $startMsg -ForegroundColor Cyan; Add-Content -LiteralPath $log -Value $startMsg -Encoding UTF8; try { & cmd /c 'npm run dev' 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath $log -Value $_ -Encoding UTF8 } } catch { $errMsg = \"[$ts] EXCEPTION: $_\"; Write-Host $errMsg -ForegroundColor Red; Add-Content -LiteralPath $log -Value $errMsg -Encoding UTF8 } $exitTs = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $exitMsg = \"[$exitTs] !!! Vite dev exited (code $LASTEXITCODE) - restart dalam 5 detik...\"; Write-Host $exitMsg -ForegroundColor Yellow; Add-Content -LiteralPath $log -Value $exitMsg -Encoding UTF8; Start-Sleep -Seconds 5 } }"
goto :service_stopped

:run_scheduler
call :print_service_header
echo Scheduler akan otomatis restart kalau crash (delay 5 detik).
echo Tekan Ctrl+C dua kali untuk benar-benar berhenti.
echo.
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { $php='%PHP%'; $artisan='%ARTISAN%'; $log='%SERVICE_LOG%'; while ($true) { $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $startMsg = \"[$ts] === Scheduler start ===\"; Write-Host $startMsg -ForegroundColor Cyan; Add-Content -LiteralPath $log -Value $startMsg -Encoding UTF8; try { & $php $artisan 'schedule:work' 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath $log -Value $_ -Encoding UTF8 } } catch { $errMsg = \"[$ts] EXCEPTION: $_\"; Write-Host $errMsg -ForegroundColor Red; Add-Content -LiteralPath $log -Value $errMsg -Encoding UTF8 } $exitTs = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $exitMsg = \"[$exitTs] !!! Scheduler exited (code $LASTEXITCODE) - restart dalam 5 detik...\"; Write-Host $exitMsg -ForegroundColor Yellow; Add-Content -LiteralPath $log -Value $exitMsg -Encoding UTF8; Start-Sleep -Seconds 5 } }"
goto :service_stopped

:run_reverb
call :print_service_header
echo Reverb WebSocket server (port 8080) - real-time push ke browser.
echo Auto-restart kalau crash (delay 5 detik). Ctrl+C 2x untuk berhenti.
echo.
powershell -NoProfile -ExecutionPolicy Bypass -Command "& { $php='%PHP%'; $artisan='%ARTISAN%'; $log='%SERVICE_LOG%'; while ($true) { $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $startMsg = \"[$ts] === Reverb server start ===\"; Write-Host $startMsg -ForegroundColor Cyan; Add-Content -LiteralPath $log -Value $startMsg -Encoding UTF8; try { & $php $artisan 'reverb:start' 2>&1 | ForEach-Object { $_; Add-Content -LiteralPath $log -Value $_ -Encoding UTF8 } } catch { $errMsg = \"[$ts] EXCEPTION: $_\"; Write-Host $errMsg -ForegroundColor Red; Add-Content -LiteralPath $log -Value $errMsg -Encoding UTF8 } $exitTs = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'; $exitMsg = \"[$exitTs] !!! Reverb server exited (code $LASTEXITCODE) - restart dalam 5 detik...\"; Write-Host $exitMsg -ForegroundColor Yellow; Add-Content -LiteralPath $log -Value $exitMsg -Encoding UTF8; Start-Sleep -Seconds 5 } }"
goto :service_stopped

:service_stopped
echo.
echo Service berhenti. Cek log: %SERVICE_LOG%
echo.
pause
exit /b %ERRORLEVEL%
