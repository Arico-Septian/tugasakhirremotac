<?php

namespace App\Events;

use App\Models\AcStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AcStatusUpdated implements ShouldBroadcastNow
{
    public array $payload;

    public function __construct(AcStatus $status)
    {
        $status->loadMissing('acUnit.room');

        $this->payload = [
            'ac_status_id' => $status->id,
            'ac_unit_id' => $status->ac_unit_id,
            'ac_number' => optional($status->acUnit)->ac_number,
            'room_id' => optional(optional($status->acUnit)->room)->id,
            'room_name' => optional(optional($status->acUnit)->room)->name,
            'power' => $status->power,
            'mode' => $status->mode,
            'set_temperature' => (int) $status->set_temperature,
            'fan_speed' => $status->fan_speed,
            'swing' => $status->swing,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('device-status');
    }

    public function broadcastAs(): string
    {
        return 'AcStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
