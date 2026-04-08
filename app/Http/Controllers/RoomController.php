<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\AcUnit;
use App\Services\MqttService;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class RoomController extends Controller
{
    /* === LIST ROOM + STATUS ESP ===*/
    public function index()
    {
        $rooms = Room::with(['acUnits.status'])->get();

        foreach ($rooms as $room) {

            $deviceId = strtolower($room->device_id);

            $status = Cache::get("device_status_{$deviceId}", 'offline');

            $lastSeen = Cache::get("device_{$deviceId}_last_seen");

            if ($lastSeen && now()->diffInSeconds($lastSeen) <= 30) {
                $status = 'online';
            }

            $room->device_status = $status;
        }

        return view('rooms.index', compact('rooms'));
    }

    /*=== CREATE ROOM ===*/
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'device_id' => 'required|unique:rooms,device_id'
        ]);

        $deviceId = strtolower($request->device_id);

        $room = Room::create([
            'name' => $request->name,
            'device_id' => $deviceId
        ]);

        $mqtt = new MqttService();

        $topic = "device/{$deviceId}/config";

        $mqtt->publish(
            $topic,
            json_encode([
                "room" => $room->name
            ])
        );

        Cache::put("device_status_{$deviceId}", 'offline');

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => '-',
            'activity' => 'add_room'
        ]);

        return redirect('/rooms');
    }

    /*=== DELETE ROOM ===*/
    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        $deviceId = strtolower($room->device_id);

        $mqtt = new MqttService();

        $mqtt->publish(
            "device/{$deviceId}/clear",
            json_encode(new \stdClass())
        );

        Cache::forget("device_status_{$deviceId}");
        Cache::forget("device_{$deviceId}_last_seen");

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $room->name,
            'ac' => '-',
            'activity' => 'delete_room'
        ]);

        $room->delete();

        return redirect('/rooms');
    }

    /*=== DETAIL STATUS AC ===*/
    public function status($id)
    {
        $room = Room::findOrFail($id);

        $acs = AcUnit::with('status')
            ->where('room_id', $id)
            ->get();

        return view('rooms.status', compact('room', 'acs'));
    }
}
