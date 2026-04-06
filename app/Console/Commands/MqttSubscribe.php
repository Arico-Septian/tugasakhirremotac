<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use Illuminate\Support\Facades\Cache;
use App\Models\AcStatus;
use App\Events\DeviceStatusUpdated;
use App\Models\Room;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'MQTT Listener (Realtime IoT)';

    public function handle()
    {
        $mqtt = new MqttService();

        $this->info("🚀 MQTT LISTENER STARTED");

        $mqtt->subscribeMultiple([

            /**
             * =============================
             * 🔥 DEVICE ONLINE
             * =============================
             */
            'device/+/online' => function ($topic, $message) use ($mqtt) {

                $data = json_decode($message, true);

                if (!$data || !isset($data['device_id'])) {
                    echo "⚠️ JSON ONLINE tidak valid\n";
                    return;
                }

                $deviceId = strtolower($data['device_id']);

                echo "🟢 ESP ONLINE: {$deviceId}\n";

                // simpan last seen
                Cache::put("device_{$deviceId}_last_seen", now(), 15);

                // 🔥 tambahan (biar Blade gampang baca)
                Cache::put("device_status_{$deviceId}", 'online');

                // resend config
                $mqtt->resendConfig($deviceId);

                event(new DeviceStatusUpdated($deviceId, 'online'));
            },

            /**
             * =============================
             * 💓 PING / HEARTBEAT
             * =============================
             */
            'device/+/ping' => function ($topic, $message) {

                preg_match('/device\/(.+)\/ping/', $topic, $matches);
                $deviceId = strtolower($matches[1] ?? null);

                if (!$deviceId) return;

                Cache::put("device_{$deviceId}_last_seen", now(), 15);
                Cache::put("device_status_{$deviceId}", 'online');

                echo "💓 PING: {$deviceId}\n";

                event(new DeviceStatusUpdated($deviceId, 'online'));
            },

            /**
             * =============================
             * 🔥 STATUS (LWT)
             * =============================
             */
            'device/+/status' => function ($topic, $message) {

                preg_match('/device\/(.+)\/status/', $topic, $matches);
                $deviceId = strtolower($matches[1] ?? null);

                if (!$deviceId) return;

                if ($message === 'offline') {

                    echo "🔴 ESP OFFLINE: {$deviceId}\n";

                    Cache::forget("device_{$deviceId}_last_seen");

                    // 🔥 penting untuk Blade
                    Cache::put("device_status_{$deviceId}", 'offline');

                    // matikan semua AC
                    AcStatus::whereHas('acUnit', function ($q) use ($deviceId) {
                        $q->where('device_id', $deviceId);
                    })->update([
                        'power' => 'OFF'
                    ]);

                    event(new DeviceStatusUpdated($deviceId, 'offline'));
                }

                if ($message === 'online') {

                    echo "🟢 STATUS ONLINE: {$deviceId}\n";

                    Cache::put("device_{$deviceId}_last_seen", now(), 15);
                    Cache::put("device_status_{$deviceId}", 'online');
                }
            },

            /**
             * =============================
             * ❤️ HEARTBEAT (optional ESP)
             * =============================
             */
            'device/+/heartbeat' => function ($topic, $message) {

                $parts = explode('/', $topic);
                $deviceId = strtolower($parts[1] ?? null);

                if (!$deviceId) return;

                Cache::put("device_{$deviceId}_last_seen", now(), 15);
                Cache::put("device_status_{$deviceId}", 'online');
            },

            /**
             * =============================
             * ➕ ADD AC
             * =============================
             */
            'room/+/ac/add' => function ($topic, $message) {

                $data = json_decode($message, true);

                if (!$data || !isset($data['id'])) {
                    echo "⚠️ AC ADD tidak valid\n";
                    return;
                }

                $parts = explode('/', $topic);
                $roomName = $parts[1] ?? null;

                if (!$roomName) return;

                // 🔥 FIX UTAMA DI SINI
                $roomName = trim($roomName);

                $room = Room::whereRaw('LOWER(name) = ?', [strtolower($roomName)])->first();

                if (!$room) {
                    return;
                }

                $deviceId = strtolower($room->device_id);

                \App\Models\AcUnit::firstOrCreate(
                    [
                        'device_id' => $deviceId,
                        'ac_id' => $data['id']
                    ],
                    [
                        'room_id' => $room->id,
                        'brand' => $data['brand'] ?? 'Unknown'
                    ]
                );
            },

        ]);

        /**
         * =============================
         * 🔥 AUTO OFFLINE DETECTOR
         * =============================
         */
        while (true) {

            sleep(5);

            $devices = Room::pluck('device_id');

            foreach ($devices as $deviceId) {

                $deviceId = strtolower($deviceId);

                $lastSeen = Cache::get("device_{$deviceId}_last_seen");

                if (!$lastSeen) {

                    Cache::put("device_status_{$deviceId}", 'offline');
                    continue;
                }

                // jika > 30 detik tidak ping → OFFLINE
                if (now()->diffInSeconds($lastSeen) > 10) {

                    echo "⚠️ TIMEOUT OFFLINE: {$deviceId}\n";

                    Cache::put("device_status_{$deviceId}", 'offline');

                    event(new DeviceStatusUpdated($deviceId, 'offline'));
                }
            }
        }
    }
}
