<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    private $mqtt;

    public function __construct()
    {
        $server = '192.168.18.194';
        $port = 1883;
        $clientId = 'laravel_client';

        $connectionSettings = (new ConnectionSettings)
            ->setUsername('mqtt_user')
            ->setPassword('Raspberry3');

        $this->mqtt = new MqttClient($server, $port, $clientId);
        $this->mqtt->connect($connectionSettings, true);
    }

    public function publish($topic, $message)
    {
        $this->mqtt->publish($topic, $message, 1, true);
    }

    public function subscribe($topic, $callback)
    {
        $this->mqtt->subscribe($topic, $callback);
        $this->mqtt->loop(true);
    }

public function resendConfig($deviceId)
{
    $room = \App\Models\Room::where('device_id', $deviceId)->first();

    if (!$room) return;

    $acs = \App\Models\AcUnit::where('room_id', $room->id)->get();

    $this->publish(
        "device/{$deviceId}/config",
        json_encode([
            "room" => $room->name,
            "acs" => $acs->map(fn($ac) => [
                "id" => (int)$ac->ac_number,
                "brand" => $ac->brand
            ])
        ])
    );

    echo "📤 CONFIG + AC LIST DIKIRIM KE {$deviceId}\n";
}

    public function subscribeMultiple(array $topics)
    {
        foreach ($topics as $topic => $callback) {
            $this->mqtt->subscribe($topic, $callback);
        }

        // loop sekali untuk semua
        $this->mqtt->loop(true);
    }
}
