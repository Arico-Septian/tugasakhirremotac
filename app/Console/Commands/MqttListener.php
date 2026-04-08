<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\RoomTemperature;

class MqttListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server = '192.168.18.194';
        $port = 1883;

        $mqtt = new MqttClient($server, $port, 'laravel-client');

        $settings = (new ConnectionSettings)
            ->setUsername('mqtt_user')
            ->setPassword('Raspberry3');

        $mqtt->connect($settings, true);

        $mqtt->subscribe('room/+/ac/+/status', function ($topic, $message) {

            $data = json_decode($message, true);

            if (isset($data['room_temp'])) {

                RoomTemperature::create([
                    'room' => $data['room'],
                    'temperature' => $data['room_temp']
                ]);

                echo "Suhu masuk: " . $data['room_temp'] . "\n";
            }
        }, 0);

        $mqtt->loop(true);
    }
}
