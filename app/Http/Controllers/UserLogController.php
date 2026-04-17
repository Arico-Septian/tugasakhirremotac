<?php

namespace App\Http\Controllers;

use App\Models\UserLog;

class UserLogController extends Controller
{
    public function index()
    {
        $logs = UserLog::with('user:id,name')->latest()->simplePaginate(10);

        return view('logs.index', compact('logs'));
    }
}
