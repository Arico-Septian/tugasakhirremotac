<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\AcUnit;

class RoomController extends Controller
{
public function index()
    {
        $rooms = Room::all();
        return view('rooms.index', compact('rooms'));
    }
public function store(Request $request)
    {
        $request->validate([
        'name'=>'required'
        ]);
        Room::create([
        'name'=>$request->name
    ]);
    return redirect('/rooms');
    }
 public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return redirect('/rooms');
    }
public function status($id)
    {
    $room = Room::findOrFail($id);
    $acs = AcUnit::with('status')->where('room_id',$id)->get();
    return view('rooms.status',compact('room','acs'));
    }
}
