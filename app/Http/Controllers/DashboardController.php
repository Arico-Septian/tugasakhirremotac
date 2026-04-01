<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\AcUnit;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $rooms = Room::with('acUnits.status')->get();
        $totalRooms = Room::count();
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
