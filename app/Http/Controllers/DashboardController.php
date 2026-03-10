<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\AcUnit;
use App\Models\User;
use App\Models\AcStatus;

class DashboardController extends Controller
{
public function index()
    {
    $rooms = Room::with('acUnits.status')->get();
    $totalRooms = Room::count(); // TOTAL ROOM
    $totalAc = AcUnit::count(); // TOTAL AC UNIT
    $activeAc = AcUnit::whereHas('status', function($q){
    $q->where('power','ON');
    })->count(); // AC ACTIVE
    $users = User::count(); // TOTAL USER
    return view('dashboard.dashboard', compact(
        'rooms',
        'totalRooms',
        'totalAc',
        'activeAc',
        'users'
        ));
    }
}
