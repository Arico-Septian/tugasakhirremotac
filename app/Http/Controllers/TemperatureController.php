<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomTemperature;

class TemperatureController extends Controller
{
    public function index()
    {
        $latest = RoomTemperature::latestByNormalizedRoom();

        return Room::orderBy('name')->get()->map(function ($room) use ($latest) {
            $key = RoomTemperature::normalizeRoomName($room->name);
            $temp = optional($latest->get($key))->temperature;

            return [
                'id' => $room->id,
                'temp' => $temp,
            ];
        });
    }
}
