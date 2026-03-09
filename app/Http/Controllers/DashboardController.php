<?php

namespace App\Http\Controllers;

use App\Models\Room;

class DashboardController extends Controller
{

public function index()
{

$rooms = Room::with('acUnits.status')->get();

return view('dashboard.dashboard',compact('rooms'));

}

}
