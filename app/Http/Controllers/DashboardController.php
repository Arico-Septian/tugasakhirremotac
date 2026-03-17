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
        return view('dashboard.dashboard', compact(
            'rooms',
            'totalRooms',
            'totalAc',
            'activeAc',
            'users'
        ));
    }
}
