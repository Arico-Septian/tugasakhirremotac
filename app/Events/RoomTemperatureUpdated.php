<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RoomTemperatureUpdated implements ShouldBroadcast
{
    public $room;

    public $temperature;

    public function __construct(string $room, float $temperature)
    {
        $this->room = $room;
        $this->temperature = $temperature;
    }

    public function broadcastOn()
    {
        return new Channel('device-status');
    }

    public function broadcastAs(): string
    {
        return 'RoomTemperatureUpdated';
    }
}
