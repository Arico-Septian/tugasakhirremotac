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
        $request->validate([
        'name' => 'required',
        'password' => 'required'
    ]);
        $credentials = $request->only('name','password');
        if(Auth::attempt($credentials))
    {
        $request->session()->regenerate();
    return redirect('/dashboard');
    }
    return back()->with('error','Username atau password salah');
    }
// REGISTER
public function register(Request $request)
    {
        $request->validate([
        'name' => 'required|min:3|max:20|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6|confirmed'
    ]);
        $user = User::create([
        'name'=>$request->name,
        'email'=>$request->email,
        'password'=>Hash::make($request->password)
    ]);
        Auth::login($user);

    return redirect('/dashboard');
    }
// LOGOUT
public function logout(Request $request)
    {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
    }
}
