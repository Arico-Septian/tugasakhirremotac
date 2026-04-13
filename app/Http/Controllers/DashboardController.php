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
        // ambil room + relasi AC
        $rooms = Room::with('acUnits.status')->get();

        // 🔥 ambil suhu terbaru per room (PALING AMAN)
        foreach ($rooms as $room) {
            $latestTemp = RoomTemperature::where('room', $room->name)
                ->latest()
                ->first();

            $room->temperature = $latestTemp ? $latestTemp->temperature : null;
        }

        // statistik
        $totalRooms = $rooms->count();
        $totalAc = AcUnit::count();

        $activeAc = AcUnit::whereHas('status', function ($q) {
            $q->where('power', 'ON');
        })->count();

        $users = User::count();

        $usersOnline = User::where('is_online', true)
            ->where('last_activity', '>=', now()->subMinutes(5))
            ->count();

        $onlineUsers = User::where('is_online', true)
            ->where('last_activity', '>=', now()->subMinute())
            ->get();

        $idleUsers = User::where('is_online', true)
            ->where('last_activity', '<', now()->subMinute())
            ->where('last_activity', '>=', now()->subMinutes(5))
            ->get();

        return view('dashboard.dashboard', compact(
            'rooms',
            'totalRooms',
            'totalAc',
            'activeAc',
            'users',
            'usersOnline',
            'onlineUsers',
            'idleUsers'
        ));
    }
}

