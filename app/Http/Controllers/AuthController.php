<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan');
        }

        if (!$user->is_active) {
            return back()->with('error', 'User tidak aktif');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Password salah');
        }

        Auth::login($user);

        $user->is_online = true;
        $user->last_login_at = now();
        $user->last_activity = now();

        $user->save();

        UserLog::create([
            'user_id' => $user->id,
            'room' => null,
            'ac' => null,
            'activity' => 'login'
        ]);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $user->is_online = false;
            $user->last_logout_at = now();
            $user->last_activity = null;

            $user->save();

            UserLog::create([
                'user_id' => $user->id,
                'room' => null,
                'ac' => null,
                'activity' => 'logout'
            ]);
        }

        Auth::logout();

        return redirect('/login');
    }
}
