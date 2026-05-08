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
        while (true) {
            try {
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

                if (!is_array($data)) {
                    $this->warn("CONTROL tidak valid");
                    return;
                }

                $parts = explode('/', $topic);
                $roomName = strtolower($parts[1] ?? '');
                $acId = $parts[3] ?? null;

                if (!$roomName || !$acId) return;

                $room = Room::whereRaw('REPLACE(LOWER(name), " ", "_") = ?', [$roomName])->first();
                if (!$room) return;

                $ac = AcUnit::where('room_id', $room->id)
                    ->where('ac_number', $acId)
                    ->first();

                if (!$ac) return;

                $status = AcStatus::firstOrNew(['ac_unit_id' => $ac->id]);

                $status->fill([
                    'power' => array_key_exists('power', $data)
                        ? $this->normalizePower($data['power'])
                        : $this->normalizePower($status->power ?? 'OFF'),
                    'mode' => array_key_exists('mode', $data)
                        ? $this->normalizeMode($data['mode'])
                        : $this->normalizeMode($status->mode ?? 'COOL'),
                    'set_temperature' => $this->normalizeTemperature(
                        $data['temp'] ?? $data['ac_temp'] ?? $status->set_temperature ?? 24
                    ),
                    'fan_speed' => $this->normalizeFanSpeed(
                        $data['fan_speed'] ?? $status->fan_speed ?? 'AUTO'
                    ),
                    'swing' => $this->normalizeSwing(
                        $data['swing'] ?? $status->swing ?? 'OFF'
                    ),
                ])->save();

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

                    $room = Room::whereRaw('REPLACE(LOWER(name), " ", "_") = ?', [$roomName])->first();

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

                    $status = AcStatus::firstOrNew(['ac_unit_id' => $ac->id]);

                    $power = array_key_exists('power', $data)
                        ? $this->normalizePower($data['power'])
                        : $this->normalizePower($status->power ?? 'OFF');
                    $mode = array_key_exists('mode', $data)
                        ? $this->normalizeMode($data['mode'])
                        : $this->normalizeMode($status->mode ?? 'COOL');
                    $temp = $this->normalizeTemperature(
                        $data['ac_temp'] ?? $data['temp'] ?? $status->set_temperature ?? 24
                    );
                    $fanSpeed = $this->normalizeFanSpeed(
                        $data['fan_speed'] ?? $status->fan_speed ?? 'AUTO'
                    );
                    $swing = $this->normalizeSwing(
                        $data['swing'] ?? $status->swing ?? 'OFF'
                    );

                    $status->fill([
                        'power' => $power,
                        'mode' => $mode,
                        'set_temperature' => $temp,
                        'fan_speed' => $fanSpeed,
                        'swing' => $swing,
                    ])->save();

                    Log::info("MQTT STATUS UPDATED", [
                        'room' => $roomName,
                        'ac' => $acNumber,
                        'power' => $power,
                        'mode' => $mode,
                        'temp' => $temp,
                        'fan_speed' => $fanSpeed,
                        'swing' => $swing,
                    ]);
                } catch (\Throwable $e) {

                    Log::error("MQTT STATUS ERROR", [
                        'topic' => $topic,
                        'error' => $e->getMessage()
                    ]);
                }
            },

            /* === RASPI TEMPERATURE === */
            'raspi/temperature' => function ($topic, $message) {
                $temp = (float) trim($message);
                if ($temp > 0) {
                    Cache::put('raspi_temperature', $temp, 300);
                    $this->line("RASPI TEMP: {$temp}°C");
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

                $room = Room::whereRaw('REPLACE(LOWER(name), " ", "_") = ?', [$roomName])->first();
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
                        'fan_speed' => 'AUTO',
                        'swing' => 'OFF',
                    ]
                );

                $this->info("AC ditambahkan ke {$roomName}");
            },

                ]);
            } catch (\Throwable $e) {
                Log::error("MQTT SUBSCRIBER ERROR", [
                    'error' => $e->getMessage(),
                ]);

                $this->error("MQTT subscriber error: " . $e->getMessage());
                $this->line("Retrying in 5 seconds...");

                sleep(5);
            }
        }
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

        return in_array($mode, ['COOL', 'HEAT', 'DRY', 'FAN', 'AUTO'], true) ? $mode : 'COOL';
    }

    private function normalizeTemperature($value)
    {
        $temperature = (int) $value;

        return min(30, max(16, $temperature ?: 24));
    }

    private function normalizeFanSpeed($value)
    {
        $fanSpeed = strtoupper(trim((string) $value));

        return in_array($fanSpeed, ['AUTO', 'LOW', 'MEDIUM', 'HIGH'], true) ? $fanSpeed : 'AUTO';
    }

    private function normalizeSwing($value)
    {
        $swing = strtoupper(trim((string) $value));

        return in_array($swing, ['OFF', 'FULL', 'HALF', 'DOWN'], true) ? $swing : 'OFF';
    }
}
