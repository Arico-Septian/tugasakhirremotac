<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('notification:cleanup')]
#[Description('Delete notifications older than 30 days')]
class CleanupNotifications extends Command
{
    public function handle()
    {
        $deleted = Notification::where('created_at', '<', now()->subDays(30))->delete();

        if ($deleted > 0) {
            $this->info("Deleted {$deleted} old notification(s)");
        } else {
            $this->info('No old notifications to delete');
        }

        return Command::SUCCESS;
    }
}
