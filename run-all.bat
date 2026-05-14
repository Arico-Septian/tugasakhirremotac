@echo off
title SmartAC - Run All Services
cd /d "%~dp0"

echo ============================================
echo   SmartAC - Starting all services
echo ============================================
echo   Project: %CD%
echo.

REM ===== 1. Cari PHP =====
set "PHP=php"
where php >nul 2>&1
if errorlevel 1 (
    if exist "C:\laragon\bin\php\php-8.4.21-Win32-vs17-x64\php.exe" (
        set "PHP=C:\laragon\bin\php\php-8.4.21-Win32-vs17-x64\php.exe"
    ) else (
        echo [ERROR] PHP tidak ditemukan di PATH dan tidak ada di Laragon.
        echo Aktifkan Laragon dulu, atau tambahkan PHP ke PATH.
        echo.
        pause
        exit /b 1
    )
)

echo Using PHP: %PHP%
echo.

REM ===== 2. Pastikan artisan ada =====
if not exist "artisan" (
    echo [ERROR] File artisan tidak ditemukan.
    echo Pastikan file run-all.bat ada di root project Laravel.
    echo.
    pause
    exit /b 1
)

REM ===== 3. Hapus stale Vite marker =====
if exist "public\hot" (
    del /q "public\hot" >nul 2>&1
    echo Stale public/hot dihapus.
)

REM ===== 4. Clear cache =====
echo Membersihkan cache Laravel...
"%PHP%" artisan view:clear >nul 2>&1
"%PHP%" artisan config:clear >nul 2>&1
"%PHP%" artisan route:clear >nul 2>&1
echo Cache cleared.
echo.

REM ===== 5. Build asset Vite kalau belum ada =====
if not exist "public\build\manifest.json" (
    echo Asset Vite belum di-build, menjalankan npm run build...
    call npm run build
    echo.
)

REM ===== 6. Spawn 4 service di window terpisah =====
echo Menjalankan MQTT Subscriber...
start "SmartAC MQTT Subscriber" cmd /k "%PHP% artisan mqtt:subscribe"
timeout /t 1 /nobreak >nul

echo Menjalankan Reverb WebSocket (port 8080)...
start "SmartAC Reverb" cmd /k "%PHP% artisan reverb:start"
timeout /t 1 /nobreak >nul

echo Menjalankan Scheduler...
start "SmartAC Scheduler" cmd /k "%PHP% artisan schedule:work"
timeout /t 1 /nobreak >nul

echo Menjalankan Vite dev (hot reload)...
start "SmartAC Vite Dev" cmd /k "npm run dev"
timeout /t 2 /nobreak >nul

REM ===== 7. Buka browser =====
start http://127.0.0.1:8000

REM ===== 8. Jalankan web server di window utama =====
echo.
echo ============================================
echo   SEMUA SERVICE BERJALAN
echo ============================================
echo   URL  : http://127.0.0.1:8000
echo   Tutup window ini untuk mematikan web server.
echo   Tutup window service lain untuk matikan service tsb.
echo ============================================
echo.
"%PHP%" artisan serve --host=0.0.0.0 --port=8000

echo.
echo Web server berhenti.
pause
