<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogController extends Controller
{
    public function index(Request $request)
    {
        $authActs = ['login', 'logout', 'change_password'];
        $acActs   = ['on', 'off', 'bulk_on', 'bulk_off', 'timer_on', 'timer_off', 'set_timer', 'control_ac'];
        $acLikes  = ['set_temp_%', 'mode_%', 'fan_speed_%', 'swing_%'];
        $userActs = ['add_user', 'delete_user', 'update_role', 'activate_user', 'deactivate_user'];
        $roomActs = ['add_room', 'delete_room', 'add_ac', 'delete_ac'];
        $destructiveActs = ['delete_user', 'delete_room', 'delete_ac', 'deactivate_user'];

        $applyAcFilter = function ($q) use ($acActs, $acLikes) {
            $q->where(function ($qq) use ($acActs, $acLikes) {
                $qq->whereIn('activity', $acActs);
                foreach ($acLikes as $like) {
                    $qq->orWhere('activity', 'like', $like);
                }
            });
        };

        $query = UserLog::with('user:id,name')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('room')) {
            $query->where('room', $request->room);
        }

        if ($request->filled('activity')) {
            match ($request->activity) {
                'auth'      => $query->whereIn('activity', $authActs),
                'ac'        => $applyAcFilter($query),
                'user'      => $query->whereIn('activity', $userActs),
                'room'      => $query->whereIn('activity', $roomActs),
                'power_on'  => $query->whereIn('activity', ['on', 'bulk_on', 'timer_on']),
                'power_off' => $query->whereIn('activity', ['off', 'bulk_off', 'timer_off']),
                'temp'      => $query->where('activity', 'like', 'set_temp_%'),
                'mode'      => $query->where('activity', 'like', 'mode_%'),
                'fan'       => $query->where('activity', 'like', 'fan_speed_%'),
                'swing'     => $query->where('activity', 'like', 'swing_%'),
                'user_mgmt' => $query->whereIn('activity', $userActs),
                'room_mgmt' => $query->whereIn('activity', $roomActs),
                default     => null,
            };
        }

        // Date preset (range=today|7d|30d) overrides date_from/date_to
        $range = $request->input('range');
        if ($range === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($range === '7d') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($range === '30d') {
            $query->where('created_at', '>=', now()->subDays(30));
        } else {
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('room', 'like', "%{$s}%")
                  ->orWhere('ac', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $logs  = $query->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);
        $rooms = UserLog::whereNotNull('room')->distinct()->orderBy('room')->pluck('room');

        // Stats — selalu dihitung dari seluruh data (tidak terpengaruh filter), kecuali date range
        $statsScope = UserLog::query();
        if ($range === 'today') {
            $statsScope->whereDate('created_at', now()->toDateString());
        } elseif ($range === '7d') {
            $statsScope->where('created_at', '>=', now()->subDays(7));
        } elseif ($range === '30d') {
            $statsScope->where('created_at', '>=', now()->subDays(30));
        }

        $stats = [
            'total'   => (clone $statsScope)->count(),
            'auth'    => (clone $statsScope)->whereIn('activity', $authActs)->count(),
            'auth24'  => (clone $statsScope)->whereIn('activity', $authActs)
                          ->where('created_at', '>=', now()->subDay())->count(),
            'ac'      => (clone $statsScope)->where(function ($qq) use ($acActs, $acLikes) {
                              $qq->whereIn('activity', $acActs);
                              foreach ($acLikes as $like) {
                                  $qq->orWhere('activity', 'like', $like);
                              }
                          })->count(),
            'destructive' => (clone $statsScope)->whereIn('activity', $destructiveActs)->count(),
        ];

        return view('logs.index', compact('logs', 'users', 'rooms', 'stats'));
    }

public function destroyAll(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        UserLog::truncate();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Semua log berhasil dihapus']);
        }

        return back()->with('success', 'Semua log berhasil dihapus');
    }
}
