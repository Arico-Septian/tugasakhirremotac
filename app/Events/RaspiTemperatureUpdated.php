<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class RaspiTemperatureUpdated implements ShouldBroadcastNow
{
    public float $temperature;

    public function __construct(float $temperature)
    {
        $this->temperature = $temperature;
    }

    public function broadcastOn()
    {
        return new Channel('device-status');
    }

    public function broadcastAs(): string
    {
        return 'RaspiTemperatureUpdated';
    }

    public function broadcastWith(): array
    {
        return ['temperature' => $this->temperature];
    }
}
