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
                Rule::unique('ac_units')->where(function ($q) use ($roomId) {
                    return $q->where('room_id', $roomId);
                })
            ]
        ]);

        $room = Room::findOrFail($roomId);

        $ac = AcUnit::create([
            'name' => $request->name,
            'room_id' => $roomId,
            'brand' => $request->brand,
            'ac_number' => $request->ac_number,
            'status' => 'OFF'
        ]);

        $mqtt = new \App\Services\MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/add",
            json_encode([
                "id" => (int)$ac->ac_number,
                "brand" => $ac->brand
            ])
        );

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

        $mqtt = new \App\Services\MqttService();

        $mqtt->publish(
            "room/{$room->name}/ac/remove",
            json_encode([
                "id" => (int)$ac->ac_number
            ])
        );

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => 'AC ' . $ac->ac_number,
            'activity' => 'delete_ac'
        ]);

        $room_id = $ac->room_id;

        $ac->delete();

        return redirect('/rooms/' . $room_id . '/ac');
    }

    public function schedule(Request $request, $id)
    {
        $ac = AcUnit::findOrFail($id);

        $ac->update([
            'timer_on' => $request->timer_on,
            'timer_off' => $request->timer_off
        ]);

        return back()->with('success', 'Timer disimpan');
    }
}
