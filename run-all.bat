@echo off
cd /d C:\xampp\htdocs\tugasakhirremotac

start cmd /k "C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000"
start cmd /k "C:\xampp\php\php.exe artisan device:check-status"
start cmd /k "C:\xampp\php\php.exe artisan mqtt:subscribe"
start cmd /k "C:\xampp\php\php.exe artisan schedule:work"
start cmd /k "C:\xampp\php\php.exe artisan app:mqtt-listener"