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
        $server = '10.218.5.60';
        $port = 1883;

        $mqtt = new MqttClient($server, $port, 'laravel-client');

        $settings = (new ConnectionSettings)
            ->setUsername('mqtt_user')
            ->setPassword('Raspberry3');

        $mqtt->connect($settings, true);

        $mqtt->subscribe('room/+/temperature', function ($topic, $message) {

            $data = json_decode($message, true);

            if (isset($data['temperature'])) {

                RoomTemperature::create([
                    'room' => $data['room'],
                    'temperature' => $data['temperature']
                ]);

                echo "Suhu masuk: " . $data['temperature'] . "\n";
            }
        }, 0);

        $mqtt->loop(true);
    }
}
