<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\AcUnit;

class AcUnitController extends Controller
{

public function index($room_id)
{

$room = Room::findOrFail($room_id);

$acs = AcUnit::where('room_id',$room_id)->get();

return view('ac.index',compact('room','acs'));

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

}
