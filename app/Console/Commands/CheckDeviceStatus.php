<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\AcStatus;
use App\Models\Room;
use Carbon\Carbon;

class CheckDeviceStatus extends Command
{
    protected $signature = 'device:check-status';
    protected $description = 'Check device online/offline status';

    public function handle()
    {
        $this->info("🚀 Device checker started...");
        sleep(5);

        while (true) {

            Room::whereNotNull('device_id')
                ->select('id', 'device_id')
                ->chunk(50, function ($rooms) {

                    foreach ($rooms as $room) {

                        if (!$room->device_id) {
                            continue;
                        }

                        $deviceId = strtolower(trim($room->device_id));

                        $lastSeen   = Cache::get("device_{$deviceId}_last_seen");
                        $statusKey  = "device_status_{$deviceId}";
                        $unknownKey = "device_unknown_{$deviceId}";

                        /* === UNKNOWN === */
                        if (!$lastSeen) {

                            if (!Cache::get($unknownKey)) {
                                $this->line("⚠️ UNKNOWN: {$deviceId}");
                                Cache::put($unknownKey, true, 60);
                            }

                            continue;
                        }

                        /* === PARSE WAKTU === */
                        if (!$lastSeen instanceof Carbon) {
                            $lastSeen = Carbon::parse($lastSeen);
                        }

                        $diff = max(0, now()->diffInSeconds($lastSeen));
                        $isOffline = $diff > 15;

                        $currentStatus = Cache::get($statusKey);

                        /* === OFFLINE === */
                        if ($isOffline) {

                            if ($currentStatus !== 'offline') {

                                $this->info("🔴 OFFLINE: {$deviceId} (diff: {$diff}s)");

                                Cache::forever($statusKey, 'offline');
                                Cache::forget($unknownKey);

                                AcStatus::whereHas('acUnit.room', function ($q) use ($deviceId) {
                                    $q->where('device_id', $deviceId);
                                })
                                ->where('power', '!=', 'OFF')
                                ->update([
                                    'power' => 'OFF'
                                ]);
                            }
                        }

                        /* === ONLINE === */
                        else {

                            if ($currentStatus !== 'online') {

                                $this->info("🟢 ONLINE: {$deviceId} (diff: {$diff}s)");

                                Cache::forever($statusKey, 'online');
                                Cache::forget($unknownKey);
                            }
                        }
                    }
                });

            sleep(5);
        }

        return Command::SUCCESS;
    }
}
