<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

// LOGIN
public function login(Request $request)
{

$credentials = $request->only('email','password');

if(Auth::attempt($credentials))
{
return redirect('/dashboard');
}

return back()->with('error','Email atau password salah');

}


// REGISTER
public function register(Request $request)
{

$user = User::create([

'name'=>$request->name,
'email'=>$request->email,
'password'=>Hash::make($request->password)

]);

Auth::login($user);

return redirect('/dashboard');

}


// LOGOUT
public function logout()
{

Auth::logout();

return redirect('/login');

}

}
