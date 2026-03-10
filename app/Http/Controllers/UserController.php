<?php

namespace App\Http\Controllers;

use App\Models\User;


class UserController extends Controller
{
 public function index()
    {
        $users = User::all();

        return view('users.index', compact('users'));
    }

public function destroy($id)
    {

    $user = User::findOrFail($id);

    $user->delete();

    return redirect('/users');

    }

}
