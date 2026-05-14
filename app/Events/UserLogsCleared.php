<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UserLogsCleared implements ShouldBroadcastNow
{
    public function broadcastOn()
    {
        return new Channel('device-status');
    }

    public function broadcastAs(): string
    {
        return 'UserLogsCleared';
    }

    public function broadcastWith(): array
    {
        return ['cleared_at' => now()->toIso8601String()];
    }
}
