<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogController extends Controller
{
    public function index(Request $request)
    {
        $query = UserLog::with('user:id,name')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('room')) {
            $query->where('room', $request->room);
        }

        if ($request->filled('activity')) {
            match ($request->activity) {
                'power_on'  => $query->whereIn('activity', ['on', 'bulk_on', 'timer_on']),
                'power_off' => $query->whereIn('activity', ['off', 'bulk_off', 'timer_off']),
                'temp'      => $query->where('activity', 'like', 'set_temp_%'),
                'mode'      => $query->where('activity', 'like', 'mode_%'),
                'fan'       => $query->where('activity', 'like', 'fan_speed_%'),
                'swing'     => $query->where('activity', 'like', 'swing_%'),
                'auth'      => $query->whereIn('activity', ['login', 'logout', 'change_password']),
                'user_mgmt' => $query->whereIn('activity', ['add_user', 'delete_user', 'update_role', 'activate_user', 'deactivate_user']),
                'room_mgmt' => $query->whereIn('activity', ['add_room', 'delete_room', 'add_ac', 'delete_ac']),
                default     => null,
            };
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('room', 'like', "%{$s}%")
                  ->orWhere('ac', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $logs  = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);
        $rooms = UserLog::whereNotNull('room')->distinct()->orderBy('room')->pluck('room');

        return view('logs.index', compact('logs', 'users', 'rooms'));
    }

    public function export(Request $request)
    {
        $query = UserLog::with('user:id,name')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('room')) {
            $query->where('room', $request->room);
        }
        if ($request->filled('activity')) {
            match ($request->activity) {
                'power_on'  => $query->whereIn('activity', ['on', 'bulk_on', 'timer_on']),
                'power_off' => $query->whereIn('activity', ['off', 'bulk_off', 'timer_off']),
                'temp'      => $query->where('activity', 'like', 'set_temp_%'),
                'mode'      => $query->where('activity', 'like', 'mode_%'),
                'fan'       => $query->where('activity', 'like', 'fan_speed_%'),
                'swing'     => $query->where('activity', 'like', 'swing_%'),
                'auth'      => $query->whereIn('activity', ['login', 'logout', 'change_password']),
                'user_mgmt' => $query->whereIn('activity', ['add_user', 'delete_user', 'update_role', 'activate_user', 'deactivate_user']),
                'room_mgmt' => $query->whereIn('activity', ['add_room', 'delete_room', 'add_ac', 'delete_ac']),
                default     => null,
            };
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('room', 'like', "%{$s}%")
                  ->orWhere('ac', 'like', "%{$s}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%"));
            });
        }

        $logs      = $query->limit(1000)->get();
        $filters   = $request->only(['user_id', 'room', 'activity', 'date_from', 'date_to', 'search']);
        $exportedAt = now()->setTimezone('Asia/Jakarta')->format('d M Y H:i');

        $pdf = Pdf::loadView('logs.pdf', compact('logs', 'filters', 'exportedAt'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('activity_log_' . now()->format('Y-m-d_His') . '.pdf');
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
