<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Room;
use Carbon\Carbon;

class CheckDeviceStatus extends Command
{
    protected $signature = 'device:check-status';
    protected $description = 'Check device online/offline status';

    const STATUS_ONLINE  = 'online';
    const STATUS_OFFLINE = 'offline';

    const OFFLINE_THRESHOLD = 10;
    const STATUS_TTL = 300;
    const UNKNOWN_TTL = 60;

    public function handle()
    {
        $lock = Cache::lock('device_check_lock', 70);

        if (!$lock->get()) {
            $this->warn('Another instance is already running');
            return Command::SUCCESS;
        }

        try {
            $this->info('Starting device status checker...');

            for ($i = 0; $i < 12; $i++) {  // Run for ~1 minute (12 * 5 seconds)

                $now = now('Asia/Jakarta');

                // Get all devices from rooms table
                $devices = Room::whereNotNull('device_id')
                    ->select('id', 'device_id')
                    ->get();

                foreach ($devices as $room) {
                    $deviceId = strtolower(trim($room->device_id));

                    $this->checkDeviceStatus($deviceId, $now);
                }

                // Also check for devices not in rooms table (if any)
                $this->checkOrphanDevices($now);

                sleep(5);
            }

            $this->info('Device status check completed');

        } catch (\Throwable $e) {
            Log::error('Device status check error: ' . $e->getMessage());
            $this->error('Error: ' . $e->getMessage());
        } finally {
            optional($lock)->release();
        }

        return Command::SUCCESS;
    }

    private function checkDeviceStatus($deviceId, $now)
    {
        $lastSeen   = Cache::get("device_{$deviceId}_last_seen");
        $statusKey  = "device_status_{$deviceId}";
        $unknownKey = "device_unknown_{$deviceId}";

        // UNKNOWN - never seen
        if (!$lastSeen) {
            if (!Cache::get($unknownKey)) {
                Log::info("Device UNKNOWN", ['device' => $deviceId]);
                $this->warn("UNKNOWN -> {$deviceId}");

                Cache::put($unknownKey, true, self::UNKNOWN_TTL);
            }
            return;
        }

        // Parse last_seen
        try {
            if (!$lastSeen instanceof Carbon) {
                $lastSeen = Carbon::parse($lastSeen);
            }
        } catch (\Exception $e) {
            Log::warning("Invalid lastSeen format for {$deviceId}", [
                'value' => $lastSeen,
                'error' => $e->getMessage()
            ]);
            return;
        }

        // Check if offline
        $diff = max(0, $now->diffInSeconds($lastSeen));
        $isOffline = $diff > self::OFFLINE_THRESHOLD;
        $currentStatus = Cache::get($statusKey);

        // OFFLINE
        if ($isOffline && $currentStatus !== self::STATUS_OFFLINE) {
            Log::info("Device OFFLINE", [
                'device' => $deviceId,
                'last_seen' => $lastSeen->toDateTimeString(),
                'diff_seconds' => $diff
            ]);

            $this->error("OFFLINE -> {$deviceId} ({$diff}s ago)");

            Cache::put($statusKey, self::STATUS_OFFLINE, self::STATUS_TTL);
            Cache::forget($unknownKey);

            // Optional: Update database
            $this->updateDeviceInDatabase($deviceId, 'offline');
        }
        // ONLINE
        elseif (!$isOffline && $currentStatus !== self::STATUS_ONLINE) {
            Log::info("Device ONLINE", [
                'device' => $deviceId,
                'last_seen' => $lastSeen->toDateTimeString(),
                'diff_seconds' => $diff
            ]);

            $this->info("ONLINE -> {$deviceId} ({$diff}s ago)");

            Cache::put($statusKey, self::STATUS_ONLINE, self::STATUS_TTL);
            Cache::forget($unknownKey);

            // Optional: Update database
            $this->updateDeviceInDatabase($deviceId, 'online');
        }
    }

    private function checkOrphanDevices($now)
    {
        // Check for devices in cache that are not in rooms table
        // This is optional, for debugging purposes
        $cacheKeys = Cache::get('device_keys', []);

        foreach ($cacheKeys as $deviceId) {
            $roomExists = Room::where('device_id', $deviceId)->exists();

            if (!$roomExists) {
                $this->line("Orphan device found in cache: {$deviceId}");
            }
        }
    }

    private function updateDeviceInDatabase($deviceId, $status)
    {
        try {
            $room = Room::where('device_id', $deviceId)->first();

            if ($room) {
                // If you have a 'status' column in rooms table
                // $room->update(['status' => $status, 'last_seen' => now()]);

                // Or update a separate device_statuses table
                // DeviceStatus::updateOrCreate(
                //     ['device_id' => $deviceId],
                //     ['status' => $status, 'last_seen' => now()]
                // );
            }
        } catch (\Exception $e) {
            Log::warning("Failed to update device status in DB: " . $e->getMessage());
        }
    }
}
