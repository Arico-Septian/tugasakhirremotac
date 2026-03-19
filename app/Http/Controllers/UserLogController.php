<?php

namespace App\Http\Controllers;

use App\Models\UserLog;

class UserLogController extends Controller
{
    public function index()
    {
        $logs = UserLog::with('user')->latest()->get();
        return view('logs.index', compact('logs'));
    }
}
