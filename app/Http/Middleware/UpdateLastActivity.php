<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            /** @var User $user */
            $user = Auth::user();

            if (! $user->last_activity || now()->diffInSeconds($user->last_activity, true) > 10) {
                $user->last_activity = now();
                $user->save();
            }
        }

        return $next($request);
    }
}
