@echo off
title Laravel MQTT Services Launcher
echo ========================================
echo   MQTT Services for ESP32 Controller
echo ========================================
echo.

cd /d C:\xampp\htdocs\tugasakhirremotac

echo [1/5] Starting Laravel Development Server...
start "Laravel Server" cmd /k "C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 /nobreak > nul

echo [2/5] Starting Device Status Checker...
start "Device Status" cmd /k "C:\xampp\php\php.exe artisan device:check-status"
timeout /t 1 /nobreak > nul

echo [3/5] Starting MQTT Subscriber...
start "MQTT Subscriber" cmd /k "C:\xampp\php\php.exe artisan mqtt:subscribe"
timeout /t 1 /nobreak > nul

echo [4/5] Starting Schedule Worker...
start "Schedule Worker" cmd /k "C:\xampp\php\php.exe artisan schedule:work"
timeout /t 1 /nobreak > nul

echo [5/5] Starting MQTT Listener...
start "MQTT Listener" cmd /k "C:\xampp\php\php.exe artisan app:mqtt-listener"

echo.
echo ========================================
echo   ALL SERVICES STARTED!
echo ========================================
echo.
echo Press any key to close this window...
pause > nul