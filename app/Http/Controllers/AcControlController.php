<?php

namespace App\Http\Controllers;

use App\Models\AcStatus;
use App\Models\AcUnit;
use App\Models\Room;
use App\Services\MqttService;

class AcControlController extends Controller
{
    public function powerOn($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = AcStatus::firstOrCreate([
            'ac_unit_id' => $id
        ]);

        $status->power = 'ON';
        $status->save();

        $mqtt = new MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/{$ac->ac_number}/control",
            json_encode(["power" => "ON"])
        );

        return back();
    }

    public function powerOff($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = AcStatus::firstOrCreate([
            'ac_unit_id' => $id
        ]);

        $status->power = 'OFF';
        $status->save();

        $mqtt = new MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/{$ac->ac_number}/control",
            json_encode(["power" => "OFF"])
        );

        return back();
    }

    public function setTemp($id, $value)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = AcStatus::firstOrCreate([
            'ac_unit_id' => $id
        ]);

        $status->set_temperature = $value;
        $status->save();

        $mqtt = new MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/{$ac->ac_number}/control",
            json_encode(["temp" => (int)$value])
        );

        return back();
    }

    public function setMode($id, $mode)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = AcStatus::firstOrCreate([
            'ac_unit_id' => $id
        ]);

        $status->mode = strtoupper($mode);
        $status->save();

        $mqtt = new MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/{$ac->ac_number}/control",
            json_encode(["mode" => strtoupper($mode)])
        );

        return back();
    }

    public function togglePower($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = $ac->status;

        if (!$status) {
            $status = new AcStatus();
            $status->ac_unit_id = $ac->id;
        }

        $status->power = ($status->power == 'ON') ? 'OFF' : 'ON';
        $status->save();

        $mqtt = new MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/{$ac->ac_number}/control",
            json_encode(["power" => $status->power])
        );

        return back();
    }
}
