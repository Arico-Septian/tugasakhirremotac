<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\AcUnit;
use App\Models\RoomTemperature;

class DashboardController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['acUnits.status'])
            ->orderBy('name')
            ->get();
        $latestTemperatures = RoomTemperature::latestByNormalizedRoom();

        foreach ($rooms as $room) {
            $room->temperature = optional(
                $latestTemperatures->get(RoomTemperature::normalizeRoomName($room->name))
            )->temperature;
        }

        $totalRooms = $rooms->count();
        $totalAc = AcUnit::count();

        $activeAc = AcUnit::whereHas('status', function ($q) {
            $q->where('power', 'ON');
        })->count();

        $inactiveAc = AcUnit::whereDoesntHave('status', function ($q) {
            $q->where('power', 'ON');
        })->count();

        return view('dashboard.dashboard', compact(
            'rooms',
            'totalRooms',
            'totalAc',
            'activeAc',
            'inactiveAc'
        ));
    }
}
