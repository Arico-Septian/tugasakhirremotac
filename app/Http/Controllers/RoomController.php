<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

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

}
