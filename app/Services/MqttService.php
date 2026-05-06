<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    private $mqtt;

    public function __construct()
    {
        $server = 'broker.hivemq.com';
        $port = 1883;
        $clientId = 'laravel_' . uniqid();

        $connectionSettings = (new ConnectionSettings)
            ->setUsername(null)
            ->setPassword(null);

        $this->mqtt = new MqttClient($server, $port, $clientId);
        $this->mqtt->connect($connectionSettings, true);
    }

    public function publish($topic, $message, $qos = 1, $retain = false)
    {
        $this->mqtt->publish($topic, $message, $qos, $retain);
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
            ]),
            1,
            true
        );

        foreach ($acs as $ac) {

            $status = \App\Models\AcStatus::where('ac_unit_id', $ac->id)->first();

            if (!$status) continue;

            $topic = "room/" . strtolower($room->name) . "/ac/{$ac->ac_number}/control";

            $this->publish(
                $topic,
                json_encode([
                    "power" => $status->power,
                    "mode"  => $status->mode,
                    "temp"  => (int)($status->set_temperature ?? 24),
                    "fan_speed" => $status->fan_speed ?? 'AUTO',
                    "swing" => $status->swing ?? 'OFF',
                ]),
                1,
                true
            );
        }

        echo "CONFIG + STATUS DIKIRIM KE {$deviceId}\n";
    }

    public function subscribeMultiple(array $topics)
    {
        foreach ($topics as $topic => $callback) {
            $this->mqtt->subscribe($topic, $callback);
        }

        $this->mqtt->loop(true);
    }
}
