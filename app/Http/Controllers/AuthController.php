<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private function rateLimitKey(Request $request): string
    {
        return 'login:' . strtolower($request->input('name', '')) . '|' . $request->ip();
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $credentials = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string'
        ]);

        $key = $this->rateLimitKey($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            throw ValidationException::withMessages([
                'name' => "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        $user = User::where('name', $credentials['name'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($key, 900); // 15 menit lockout
            $remaining = 5 - RateLimiter::attempts($key);
            $msg = $remaining > 0
                ? "Username atau password salah. Sisa percobaan: {$remaining}."
                : 'Akun dikunci sementara selama 15 menit karena terlalu banyak percobaan.';
            throw ValidationException::withMessages(['name' => $msg]);
        }

        if (!$user->is_active) {
            return back()
                ->withInput($request->only('name'))
                ->with('error', 'User tidak aktif');
        }

        RateLimiter::clear($key);

        Auth::login($user);
        $request->session()->regenerate();

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

        return redirect()->intended(route('dashboard'));
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
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
