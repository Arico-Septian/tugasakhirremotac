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

        // Mengambil semua unit AC dari koleksi rooms untuk efisiensi query
        $allAcUnits = $rooms->flatMap->acUnits;
        $totalAc = $allAcUnits->count();
        $activeAc = $allAcUnits->filter(fn($ac) => optional($ac->status)->power === 'ON')->count();
        $inactiveAc = $totalAc - $activeAc;

        return view('dashboard.dashboard', compact(
            'rooms',
            'totalRooms',
            'totalAc',
            'activeAc',
            'inactiveAc'
        ));
    }
}
