<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Room;
use App\Models\RoomTemperature;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MqttListener extends Command
{
    protected $signature = 'app:mqtt-listener';
    protected $description = 'MQTT Listener for temperature data';

    public function handle()
    {
        $server = 'broker.hivemq.com';
        $port = 1883;

        while (true) {

            try {

                $this->info("Connecting MQTT...");

                $mqtt = new MqttClient($server, $port, 'laravel-listener');

                $settings = (new ConnectionSettings)
                    ->setUsername(null)
                    ->setPassword(null)
                    ->setKeepAliveInterval(60);

                $mqtt->connect($settings, true);

                $this->info("Connected to MQTT");

                $mqtt->subscribe('room/+/temperature', function ($topic, $message, $retained) {

                    if ($retained) return;

                    if (!preg_match('#^room/[^/]+/temperature$#', $topic)) return;

                    $data = json_decode($message, true);

                    if (!is_array($data)) {
                        echo "JSON tidak valid: $message\n";
                        return;
                    }

                    if (!isset($data['temperature'], $data['room'])) {
                        echo "Data tidak lengkap\n";
                        return;
                    }

                    $roomKey = RoomTemperature::normalizeRoomName($data['room']);
                    if ($roomKey === '') {
                        echo "Data room kosong\n";
                        return;
                    }

                    $roomModel = Room::whereRaw('LOWER(name) = ?', [$roomKey])->first();
                    $room = $roomModel ? $roomModel->name : $roomKey;

                    if (!is_numeric($data['temperature'])) {
                        echo "Temperature bukan angka\n";
                        return;
                    }

                    $temperature = (float) $data['temperature'];

                    if ($temperature < 10 || $temperature > 60) {
                        echo "Suhu tidak wajar: $temperature\n";
                        return;
                    }

                    $normalizedRoom = RoomTemperature::normalizeRoomName($room);

                    $dupKey = 'dup_' . md5($normalizedRoom . $temperature);
                    if (Cache::has($dupKey)) return;
                    Cache::put($dupKey, true, 5);

                    $now  = now();
                    $key  = "last_temp_{$normalizedRoom}";
                    $last = Cache::get($key);

                    if (!$last || $now->diffInSeconds($last) >= 5) {

                        RoomTemperature::create([
                            'room' => $room,
                            'temperature' => $temperature
                        ]);

                        Cache::put($key, $now, 60);

                        Log::info('Temperature received', compact('room', 'temperature'));

                        if (app()->environment('local')) {
                            echo "[{$room}] {$temperature} C\n";
                        }
                    }

                }, 1);

                $mqtt->loop(true);

            } catch (\Throwable $e) {

                $this->error("MQTT ERROR: " . $e->getMessage());

                if (isset($mqtt)) {
                    try {
                        $mqtt->disconnect();
                    } catch (\Throwable $e2) {}
                }

                sleep(3 + rand(0, 2));
            }
        }
    }
}
