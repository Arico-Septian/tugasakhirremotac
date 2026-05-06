<?php

namespace App\Http\Controllers;

use App\Models\AcStatus;
use App\Models\AcUnit;
use App\Models\Room;
use App\Models\UserLog;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcControlController extends Controller
{
    private const MODES = ['COOL', 'HEAT', 'DRY', 'FAN', 'AUTO'];

    private $mqtt;

    public function __construct()
    {
        $this->mqtt = new MqttService();
    }
    private function statusFor(AcUnit $ac): AcStatus
    {
        return AcStatus::firstOrCreate(
            ['ac_unit_id' => $ac->id],
            [
                'power' => 'OFF',
                'mode' => 'COOL',
                'set_temperature' => 24,
            ]
        );
    }

    private function normalizeTemperature($value): int
    {
        return min(30, max(16, (int) $value ?: 24));
    }

    private function normalizeMode($mode): string
    {
        $mode = strtoupper(trim((string) $mode));

        abort_unless(in_array($mode, self::MODES, true), 422, 'Mode AC tidak valid');

        return $mode;
    }

    private function normalizePower($power): string
    {
        $power = strtoupper(trim((string) $power));

        abort_unless(in_array($power, ['ON', 'OFF'], true), 422, 'Power AC tidak valid');

        return $power;
    }

    private function sendFullState(AcUnit $ac, Room $room, AcStatus $status)
    {
        $this->mqtt->publish(
            "room/" . strtolower(trim($room->name)) . "/ac/{$ac->ac_number}/control",
            json_encode([
                "power" => $status->power ?? 'OFF',
                "mode"  => $status->mode ?? 'COOL',
                "temp"  => $this->normalizeTemperature($status->set_temperature ?? 24)
            ]),
            1,
            true
        );
    }

    public function powerOn($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = $this->statusFor($ac);

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

        $status = $this->statusFor($ac);

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

        $value = $this->normalizeTemperature($value);
        $status = $this->statusFor($ac);

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

        $mode = $this->normalizeMode($mode);
        $status = $this->statusFor($ac);

        $status->mode = $mode;
        $status->save();

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => $ac->ac_number,
            'activity' => 'mode_' . $mode
        ]);

        return back();
    }

    public function togglePower($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room = Room::findOrFail($ac->room_id);

        $status = $this->statusFor($ac);

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

        $status = $this->statusFor($ac);

        $power = $this->normalizePower($request->input('power', $status->power));
        $mode = $this->normalizeMode($request->input('mode', $status->mode));
        $temp = $this->normalizeTemperature($request->input('temp', $status->set_temperature));

        $status->update([
            'power' => $power,
            'mode' => $mode,
            'set_temperature' => $temp,
        ]);

        $this->sendFullState($ac, $room, $status);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC ' . $ac->ac_number,
            'activity' => 'control_ac'
        ]);

        return response()->json(['success' => true]);
    }
}
