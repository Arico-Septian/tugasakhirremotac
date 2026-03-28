<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\AcUnit;
use App\Services\MqttService;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;


class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('rooms.index', compact('rooms'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'device_id' => 'required|unique:rooms,device_id'
        ]);

        $room = Room::create([
            'name' => $request->name,
            'device_id' => $request->device_id
        ]);

        $mqtt = new MqttService();

        $topic = "device/{$room->device_id}/config";

        $mqtt->publish(
            $topic,
            json_encode([
                "room" => $room->name
            ])
        );

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => '-',
            'activity' => 'add_room'
        ]);

        return redirect('/rooms');
    }
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        $mqtt = new MqttService();

        $mqtt->publish(
            "device/{$room->device_id}/clear",
            json_encode([])
        );

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => '-',
            'activity' => 'delete_room'
        ]);

        $room->delete();

        return redirect('/rooms');
    }
    public function status($id)
    {
        $room = Room::findOrFail($id);
        $acs = AcUnit::with('status')->where('room_id', $id)->get();
        return view('rooms.status', compact('room', 'acs'));
    }
}
