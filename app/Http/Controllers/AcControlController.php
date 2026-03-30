<?php

namespace App\Http\Controllers;

use App\Models\AcStatus;
use App\Models\AcUnit;
use App\Models\Room;
use App\Models\UserLog;
use App\Services\MqttService;
use Illuminate\Support\Facades\Auth;

class AcControlController extends Controller
{
    private function sendFullState($ac, $room, $status)
    {
        $mqtt = new MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/{$ac->ac_number}/control",
            json_encode([
                "power" => $status->power ?? 'OFF',
                "mode" => $status->mode ?? 'COOL',
                "temp" => (int)($status->set_temperature ?? 24)
            ])
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

        $status = $ac->status ?? new AcStatus(['ac_unit_id' => $ac->id]);

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
}
