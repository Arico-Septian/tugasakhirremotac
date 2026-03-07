<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    private $mqtt;

    public function __construct()
    {
        $server = '192.168.1.20';
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
        $this->mqtt->publish($topic, $message);
    }

    public function subscribe($topic, $callback)
    {
        $this->mqtt->subscribe($topic, $callback);
        $this->mqtt->loop(true);
    }
}