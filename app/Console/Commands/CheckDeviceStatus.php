<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\AcStatus;
use App\Models\Room;

class CheckDeviceStatus extends Command
{
    protected $signature = 'device:check-status';
    protected $description = 'Check device online/offline status';

    public function handle()
    {
        $this->info("🚀 Device checker started...");

        while (true) {

            Room::chunk(50, function ($rooms) {

                foreach ($rooms as $room) {

                    // 🔥 WAJIB lowercase
                    $deviceId = strtolower($room->device_id);

                    $lastSeen = Cache::get("device_{$deviceId}_last_seen");

                    $isOffline = !$lastSeen || now()->diffInSeconds($lastSeen) > 10;

                    $currentStatus = Cache::get("device_status_{$deviceId}");

                    if ($isOffline) {

                        if ($currentStatus !== 'offline') {

                            $this->info("🔴 OFFLINE: {$deviceId}");

                            // 🔥 update cache + TTL
                            Cache::put("device_status_{$deviceId}", 'offline', 60);

                            AcStatus::whereHas('acUnit.room', function ($q) use ($deviceId) {
                                $q->where('device_id', $deviceId);
                            })
                                ->where('power', '!=', 'OFF')
                                ->update([
                                    'power' => 'OFF'
                                ]);
                        }
                    } else {

                        if ($currentStatus !== 'online') {

                            $this->info("🟢 ONLINE: {$deviceId}");

                            Cache::put("device_status_{$deviceId}", 'online', 60);
                        }
                    }
                }
            });

            sleep(3);
        }

        return Command::SUCCESS;
    }
}
