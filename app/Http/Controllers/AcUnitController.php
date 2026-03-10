<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcUnit;
use App\Models\Room;

class AcUnitController extends Controller
{
public function index($id)
{
    $room = Room::findOrFail($id);

    $acs = AcUnit::with('status')
            ->where('room_id',$id)
            ->get();

    return view('ac.index', compact('room','acs'));
}
public function store(Request $request,$room_id)
    {
        AcUnit::create([
        'room_id'=>$room_id,
        'ac_number'=>$request->ac_number,
        'name'=>$request->name,
        'brand'=>$request->brand
    ]);
    return back();
    }
public function destroy($id)
    {
    $ac = AcUnit::findOrFail($id);
    $room_id = $ac->room_id;
    $ac->delete();
    return redirect('/rooms/'.$room_id.'/ac');
    }
}
