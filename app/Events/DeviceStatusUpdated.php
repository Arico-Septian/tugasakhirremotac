<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DeviceStatusUpdated implements ShouldBroadcast
{
    public $deviceId;
    public $status;

    public function __construct($deviceId, $status)
    {
        $this->deviceId = $deviceId;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new Channel('device-status');
    }
}
