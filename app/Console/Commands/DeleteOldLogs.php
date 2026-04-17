<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = \App\Models\UserLog::where('created_at', '<', now()->subDays(7))->delete();

        $this->info("Deleted $deleted logs older than 7 days");
    }
}
