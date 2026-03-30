<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'MQTT Listener';

    public function handle()
    {
        $mqtt = new MqttService();

        $mqtt->subscribeMultiple([

            'device/+/online' => function ($topic, $message) use ($mqtt) {

                $data = json_decode($message, true);
                $deviceId = $data['device_id'] ?? null;

                if (!$deviceId) return;

                echo "ESP ONLINE: {$deviceId}\n";

                // 🔥 KIRIM ULANG CONFIG
                $mqtt->resendConfig($deviceId);
            }

        ]);
    }
}
