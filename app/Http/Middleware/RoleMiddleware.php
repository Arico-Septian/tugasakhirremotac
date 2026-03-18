<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403, 'Belum login');
        }

        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses hanya untuk admin');
        }

        return $next($request);
    }
}
