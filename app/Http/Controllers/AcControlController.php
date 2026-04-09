<?php

namespace App\Http\Controllers;

use App\Models\AcStatus;
use App\Models\AcUnit;
use App\Models\Room;
use App\Models\UserLog;
use App\Services\MqttService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AcControlController extends Controller
{
    private $mqtt;

    public function __construct()
    {
        $this->mqtt = new MqttService();
    }
    private function sendFullState($ac, $room, $status)
    {
        $this->mqtt->publish(
            "room/" . strtolower($room->name) . "/ac/{$ac->ac_number}/control",
            json_encode([
                "power" => $status->power ?? 'OFF',
                "mode"  => $status->mode ?? 'COOL',
                "temp"  => (int)($status->set_temperature ?? 24)
            ]),
            1,
            true
        );
    }

    public function powerOn($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = AcStatus::firstOrCreate([
            'ac_unit_id' => $id
        ]);

        $status->power = 'ON';
        $status->save();

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => $ac->ac_number,
            'activity' => 'on'
        ]);

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

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => $ac->ac_number,
            'activity' => 'off'
        ]);

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

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => $ac->ac_number,
            'activity' => 'set_temp_' . $value
        ]);

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

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => $ac->ac_number,
            'activity' => 'mode_' . strtoupper($mode)
        ]);

        return back();
    }

    public function togglePower($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = AcStatus::firstOrCreate([
            'ac_unit_id' => $ac->id
        ]);

        $status->power = ($status->power == 'ON') ? 'OFF' : 'ON';
        $status->save();

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => $ac->ac_number,
            'activity' => strtolower($status->power)
        ]);

        return back();
    }

    public function control(Request $request, $id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $topic = "room/" . strtolower($room->name) . "/ac/{$ac->ac_number}/control";

        $payload = [
            "power" => $request->power,
            "mode"  => $request->mode,
            "temp"  => (int)$request->temp
        ];

        $this->mqtt->publish($topic, json_encode($payload), 1, true);

        AcStatus::updateOrCreate(
            ['ac_unit_id' => $ac->id],
            [
                'power' => $request->power,
                'mode'  => $request->mode,
                'set_temperature' => (int)$request->temp
            ]
        );

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC ' . $ac->ac_number,
            'activity' => 'control_ac'
        ]);

        return response()->json(['success' => true]);
    }
}
