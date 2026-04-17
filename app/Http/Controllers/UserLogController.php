<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class UserLogController extends Controller
{
    public function index()
    {
        $logs = UserLog::with('user:id,name')->latest()->paginate(10);

        return view('logs.index', compact('logs'));
    }

    public function destroyAll()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        UserLog::truncate();

        return back()->with('success', 'Semua log berhasil dihapus');
    }
}
