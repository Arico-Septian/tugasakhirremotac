<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UpdateLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user->last_activity || now()->diffInSeconds($user->last_activity) > 60) {
                $user->last_activity = now();
                $user->save();
            }
        }

        return $next($request);
    }
}
