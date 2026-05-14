<?php

namespace App\Events;

use App\Models\UserLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UserLogCreated implements ShouldBroadcastNow
{
    public array $payload;

    public function __construct(UserLog $log)
    {
        $log->loadMissing('user');

        $name = $log->user->name ?? ($log->user_id === null ? 'System' : 'Deleted User');

        $this->payload = [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'user_name' => $name,
            'user_initial' => mb_strtoupper(mb_substr($name, 0, 1)),
            'user_avatar' => $log->user?->avatar_url ?? null,
            'room' => $log->room,
            'ac' => $log->ac,
            'activity' => $log->activity,
            'time' => optional($log->created_at)->format('H:i'),
            'time_human' => optional($log->created_at)->diffForHumans(),
        ];
    }

    public function broadcastOn()
    {
        return new Channel('device-status');
    }

    public function broadcastAs(): string
    {
        return 'UserLogCreated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
