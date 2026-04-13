<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcUnit;
use App\Models\Room;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AcUnitController extends Controller
{
    public function index($id)
    {
        $room = Room::findOrFail($id);

        $acs = AcUnit::where('room_id', $id)->get();

        return view('ac.index', compact('room', 'acs'));
    }

    public function store(Request $request, $roomId)
    {
        $request->validate([
            'name' => 'required',
            'brand' => 'required',
            'ac_number' => [
                'required',
                'integer',
                'min:1',
                'max:15',
                Rule::unique('ac_units')->where(function ($q) use ($roomId) {
                    return $q->where('room_id', $roomId);
                })
            ]
        ]);

        $count = AcUnit::where('room_id', $roomId)->count();

        if ($count >= 15) {
            return back()->with('error', 'Maksimal 15 AC per ruangan');
        }

        $room = Room::findOrFail($roomId);

        $ac = AcUnit::create([
            'name' => $request->name,
            'room_id' => $roomId,
            'brand' => $request->brand,
            'ac_number' => $request->ac_number,
            'status' => 'OFF'
        ]);

        $mqtt = new \App\Services\MqttService();

        $mqtt->resendConfig($room->device_id);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC ' . $ac->ac_number,
            'activity' => 'add_ac'
        ]);

        return redirect()->back()->with('new_ac_id', $ac->id);
    }

    public function destroy($id)
    {
        $ac = AcUnit::findOrFail($id);

        $room = Room::findOrFail($ac->room_id);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC ' . $ac->ac_number,
            'activity' => 'delete_ac'
        ]);

        $room_id = $ac->room_id;

        $ac->delete();

        $mqtt = new \App\Services\MqttService();

        $mqtt->resendConfig($room->device_id);

        return redirect('/rooms/' . $room_id . '/ac');
    }

    public function schedule(Request $request, $id)
    {
        $request->validate([
            'timer_on' => 'nullable|date_format:H:i',
            'timer_off' => 'nullable|date_format:H:i',
        ]);

        if ($request->timer_on && $request->timer_off) {
            if ($request->timer_off <= $request->timer_on) {
                return back()->withErrors([
                    'Timer OFF harus lebih besar dari ON'
                ])->withInput();
            }
        }

        $ac = AcUnit::findOrFail($id);

        $ac->update([
            'timer_on' => $request->timer_on,
            'timer_off' => $request->timer_off
        ]);

        return back()->with('success', 'Timer disimpan');
    }
}
