<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use Illuminate\Support\Facades\Cache;
use App\Models\AcUnit;
use App\Models\AcStatus;
use App\Events\DeviceStatusUpdated;
use App\Models\Room;
use Illuminate\Support\Facades\Log;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'MQTT Listener (Realtime IoT)';

    public function handle()
    {
        $mqtt = new MqttService();

        $this->info("MQTT LISTENER STARTED");

        $mqtt->subscribeMultiple([

            /* === DEVICE ONLINE === */
            'device/+/online' => function ($topic, $message) use ($mqtt) {

                $data = json_decode($message, true);

                if (!$data || empty($data['device_id'])) {
                    $this->warn("JSON ONLINE tidak valid");
                    return;
                }

                $deviceId = $this->normalize($data['device_id']);

                $this->info("ESP ONLINE: {$deviceId}");

                $this->setOnline($deviceId);

                $mqtt->resendConfig($deviceId);

                event(new DeviceStatusUpdated($deviceId, 'online'));
            },

            /* === PING === */
            'device/+/ping' => function ($topic) {

                $deviceId = $this->extractDeviceId($topic, 'ping');
                if (!$deviceId) return;

                $this->setOnline($deviceId);

                $this->line("PING: {$deviceId}");

                event(new DeviceStatusUpdated($deviceId, 'online'));
            },

            /* === STATUS (LWT) === */
            'device/+/status' => function ($topic, $message) {

                $deviceId = $this->extractDeviceId($topic, 'status');
                if (!$deviceId) return;

                if ($message === 'offline') {

                    $this->error("ESP OFFLINE: {$deviceId}");

                    Cache::forget("device_{$deviceId}_last_seen");
                    Cache::put("device_status_{$deviceId}", 'offline', 300);
                    Cache::forget("device_unknown_{$deviceId}");

                    AcStatus::whereHas('acUnit.room', function ($q) use ($deviceId) {
                        $q->where('device_id', $deviceId);
                    })->update([
                        'power' => 'OFF'
                    ]);
                    Room::where('device_id', $deviceId)->update([
                        'device_status' => 'offline',
                    ]);

                    event(new DeviceStatusUpdated($deviceId, 'offline'));

                    Log::info("Device marked OFFLINE via LWT", ['device' => $deviceId]);
                } elseif ($message === 'online') {

                    $this->info("STATUS ONLINE: {$deviceId}");

                    $this->setOnline($deviceId);

                    event(new DeviceStatusUpdated($deviceId, 'online'));
                }
            },

            'room/+/ac/+/control' => function ($topic, $message) {

                $data = json_decode($message, true);

                if (!$data) {
                    $this->warn("CONTROL tidak valid");
                    return;
                }

                $parts = explode('/', $topic);
                $roomName = strtolower($parts[1] ?? '');
                $acId = $parts[3] ?? null;

                if (!$roomName || !$acId) return;

                $room = Room::whereRaw('LOWER(name) = ?', [$roomName])->first();
                if (!$room) return;

                $ac = AcUnit::where('room_id', $room->id)
                    ->where('ac_number', $acId)
                    ->first();

                if (!$ac) return;

                AcStatus::updateOrCreate(
                    ['ac_unit_id' => $ac->id],
                    [
                        'power' => $this->normalizePower($data['power'] ?? 'OFF'),
                        'mode'  => $this->normalizeMode($data['mode'] ?? 'COOL'),
                        'set_temperature' => $this->normalizeTemperature($data['temp'] ?? 24),
                    ]
                );

                $this->info("AC {$acId} di {$roomName} diupdate");
            },

            'room/+/ac/+/status' => function ($topic, $message) {

                try {

                    $data = json_decode($message, true);

                    if (!is_array($data)) {
                        Log::warning("MQTT STATUS INVALID JSON", [
                            'topic' => $topic,
                            'message' => $message
                        ]);
                        return;
                    }

                    $parts = explode('/', $topic);

                    if (count($parts) < 5) {
                        Log::warning("MQTT TOPIC INVALID", [
                            'topic' => $topic
                        ]);
                        return;
                    }

                    $roomName = strtolower(trim($parts[1]));
                    $acNumber = (int) $parts[3];

                    if (!$roomName || !$acNumber) {
                        Log::warning("MQTT DATA TIDAK LENGKAP", compact('topic'));
                        return;
                    }

                    $room = Room::whereRaw('LOWER(name) = ?', [$roomName])->first();

                    if (!$room) {
                        Log::warning("ROOM TIDAK DITEMUKAN", [
                            'room' => $roomName
                        ]);
                        return;
                    }

                    $ac = AcUnit::where('room_id', $room->id)
                        ->where('ac_number', $acNumber)
                        ->first();

                    if (!$ac) {
                        Log::warning("AC TIDAK TERDAFTAR (DIABAIKAN)", [
                            'room' => $roomName,
                            'ac_number' => $acNumber
                        ]);
                        return;
                    }

                    $power = $this->normalizePower($data['power'] ?? 'OFF');
                    $mode  = $this->normalizeMode($data['mode'] ?? 'COOL');
                    $temp  = $this->normalizeTemperature($data['ac_temp'] ?? $data['temp'] ?? 24);

                    AcStatus::updateOrCreate(
                        ['ac_unit_id' => $ac->id],
                        [
                            'power' => $power,
                            'mode' => $mode,
                            'set_temperature' => $temp,
                        ]
                    );

                    Log::info("MQTT STATUS UPDATED", [
                        'room' => $roomName,
                        'ac' => $acNumber,
                        'power' => $power,
                        'mode' => $mode,
                        'temp' => $temp
                    ]);
                } catch (\Throwable $e) {

                    Log::error("MQTT STATUS ERROR", [
                        'topic' => $topic,
                        'error' => $e->getMessage()
                    ]);
                }
            },

            /* === HEARTBEAT === */
            'device/+/heartbeat' => function ($topic) {

                $deviceId = $this->extractDeviceId($topic, 'heartbeat');
                if (!$deviceId) return;

                $this->setOnline($deviceId);
            },

            /* === ADD AC === */
            'room/+/ac/add' => function ($topic, $message) {

                $data = json_decode($message, true);

                if (!$data || empty($data['id'])) {
                    $this->warn("AC ADD tidak valid");
                    return;
                }

                $parts = explode('/', $topic);
                $roomName = strtolower(trim($parts[1] ?? ''));

                if (!$roomName) return;

                $room = Room::whereRaw('LOWER(name) = ?', [$roomName])->first();
                if (!$room || !$room->device_id) return;

                $acNumber = (int) $data['id'];

                if ($acNumber < 1 || $acNumber > 15) {
                    $this->warn("Nomor AC tidak valid: {$acNumber}");
                    return;
                }

                $ac = AcUnit::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'ac_number' => $acNumber,
                    ],
                    [
                        'name' => "AC {$acNumber}",
                        'brand' => $data['brand'] ?? 'Unknown',
                    ]
                );

                AcStatus::firstOrCreate(
                    ['ac_unit_id' => $ac->id],
                    [
                        'power' => 'OFF',
                        'mode' => 'COOL',
                        'set_temperature' => 24,
                    ]
                );

                $this->info("AC ditambahkan ke {$roomName}");
            },

        ]);
    }

    /* === HELPER: SET ONLINE === */
    private function setOnline($deviceId)
    {
        $deviceId = $this->normalize($deviceId);
        $now = now();

        Cache::put("device_{$deviceId}_last_seen", $now, 60);
        Cache::put("device_status_{$deviceId}", 'online', 60);
        Cache::forget("device_unknown_{$deviceId}");

        Room::where('device_id', $deviceId)->update([
            'device_status' => 'online',
            'last_seen' => $now,
        ]);
    }

    /* === HELPER: EXTRACT DEVICE ID === */
    private function extractDeviceId($topic, $type)
    {
        if (!preg_match("/device\/(.+)\/{$type}/", $topic, $matches)) {
            return null;
        }

        return $this->normalize($matches[1]);
    }

    /* === HELPER: NORMALIZE ID === */
    private function normalize($value)
    {
        return strtolower(trim($value));
    }

    private function normalizePower($value)
    {
        $power = strtoupper(trim((string) $value));

        return in_array($power, ['ON', 'OFF'], true) ? $power : 'OFF';
    }

    private function normalizeMode($value)
    {
        $mode = strtoupper(trim((string) $value));

        return in_array($mode, ['COOL', 'HEAT', 'FAN', 'AUTO'], true) ? $mode : 'COOL';
    }

    private function normalizeTemperature($value)
    {
        $temperature = (int) $value;

        return min(30, max(16, $temperature ?: 24));
    }
}
