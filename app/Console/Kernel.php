<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Clean logs daily
        $schedule->command('logs:clean')
            ->dailyAt('07:00');

        // Check device online/offline status every minute
        $schedule->command('device:check-status')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Run AC timers every minute
        $schedule->command('ac:run-timer')
            ->everyMinute()
            ->withoutOverlapping();

        // MQTT Subscriber (listener) - harus selalu running
        // Gunakan runInBackground() agar tidak block scheduler lain
        $schedule->command('mqtt:subscribe')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // Atau jika ingin menggunakan app:mqtt-listener
        $schedule->command('app:mqtt-listener')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
