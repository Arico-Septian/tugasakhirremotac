<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcUnit;
use App\Models\Room;

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
            'ac_number' => 'required'
        ]);

        $ac = AcUnit::create([
            'name' => $request->name,
            'room_id' => $roomId,
            'brand' => $request->brand,
            'ac_number' => $request->ac_number,
            'status' => 'OFF'

        ]);

        $mqtt = new \App\Services\MqttService();

        $mqtt->publish(
            "device/esp32_01/ac/add",
            json_encode([
                "ac_id" => $ac->id,
                "name" => $ac->name,
                "room_id" => $ac->room_id,
                "brand" => $ac->brand
            ])
        );

        return back();
    }

    public function destroy($id)
    {
        $ac = AcUnit::findOrFail($id);
        $room_id = $ac->room_id;

        $ac->delete();

        return redirect('/rooms/' . $room_id . '/ac');
    }
}
