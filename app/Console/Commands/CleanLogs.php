<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserLog;

class CleanLogs extends Command
{
    protected $signature = 'clean:logs';
    protected $description = 'Delete old logs';

    public function handle()
    {
        UserLog::where('created_at', '<', now()->subDays(7))->delete();

        $this->info('Old logs deleted successfully');
    }
}
