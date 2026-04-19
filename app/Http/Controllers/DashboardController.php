<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\AcUnit;
use App\Models\User;
use App\Models\RoomTemperature;

class DashboardController extends Controller
{
    public function index()
    {
        $rooms = Room::with('acUnits.status')->get();

        foreach ($rooms as $room) {
            $latestTemp = RoomTemperature::where('room', $room->name)
                ->latest()
                ->first();

            $room->temperature = $latestTemp ? $latestTemp->temperature : null;
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
