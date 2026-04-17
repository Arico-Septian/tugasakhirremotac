<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\AcStatus;
use App\Models\Room;
use Carbon\Carbon;

class CheckDeviceStatus extends Command
{
    protected $signature = 'device:check-status';
    protected $description = 'Check device online/offline status';

    const STATUS_ONLINE  = 'online';
    const STATUS_OFFLINE = 'offline';

    const OFFLINE_THRESHOLD = 15;
    const STATUS_TTL = 300;
    const UNKNOWN_TTL = 60;

    public function handle()
    {
        $lock = Cache::lock('device_check_lock', 70);

        if (!$lock->get()) {
            return Command::SUCCESS;
        }

        try {

            for ($i = 0; $i < 12; $i++) {

                $now = now('Asia/Jakarta');

                Room::whereNotNull('device_id')
                    ->select('id', 'device_id')
                    ->chunk(50, function ($rooms) use ($now) {

                        foreach ($rooms as $room) {

                            if (empty($room->device_id)) continue;

                            $deviceId = strtolower(trim($room->device_id));

                            $lastSeen   = Cache::get("device_{$deviceId}_last_seen");
                            $statusKey  = "device_status_{$deviceId}";
                            $unknownKey = "device_unknown_{$deviceId}";

                            // UNKNOWN
                            if (!$lastSeen) {

                                if (!Cache::get($unknownKey)) {
                                    Log::info("Device UNKNOWN", [
                                        'device' => $deviceId
                                    ]);

                                    $this->warn("UNKNOWN → $deviceId");

                                    Cache::put($unknownKey, true, self::UNKNOWN_TTL);
                                }

                                continue;
                            }

                            // SAFE PARSE
                            try {
                                if (!$lastSeen instanceof Carbon) {
                                    $lastSeen = Carbon::parse($lastSeen);
                                }
                            } catch (\Exception $e) {

                                Log::warning("Invalid lastSeen format", [
                                    'device' => $deviceId,
                                    'value'  => $lastSeen
                                ]);

                                continue;
                            }

                            // CHECK STATUS
                            $diff = max(0, $now->diffInSeconds($lastSeen));
                            $isOffline = $diff > self::OFFLINE_THRESHOLD;

                            $currentStatus = Cache::get($statusKey);

                            // OFFLINE
                            if ($isOffline && $currentStatus !== self::STATUS_OFFLINE) {

                                Log::info("Device OFFLINE", [
                                    'device' => $deviceId,
                                    'diff'   => $diff
                                ]);

                                $this->error("OFFLINE → $deviceId ({$diff}s)");

                                Cache::put($statusKey, self::STATUS_OFFLINE, self::STATUS_TTL);
                                Cache::forget($unknownKey);
                            }

                            // ONLINE
                            elseif (!$isOffline && $currentStatus !== self::STATUS_ONLINE) {

                                Log::info("Device ONLINE", [
                                    'device' => $deviceId,
                                    'diff'   => $diff
                                ]);

                                $this->info("ONLINE → $deviceId ({$diff}s)");

                                Cache::put($statusKey, self::STATUS_ONLINE, self::STATUS_TTL);
                                Cache::forget($unknownKey);
                            }
                        }
                    });

                sleep(5);
            }
        } finally {
            optional($lock)->release();
        }

        return Command::SUCCESS;
    }
}
