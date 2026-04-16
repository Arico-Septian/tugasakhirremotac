<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('clean:logs')
            ->daily();

        $schedule->command('device:check-status')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('ac:run-timer')
            ->everyMinute()
            ->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
