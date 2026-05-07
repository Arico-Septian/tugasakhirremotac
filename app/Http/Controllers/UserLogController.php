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

    public function export()
    {
        $logs = UserLog::with('user:id,name')->latest()->get();

        $filename = 'activity_log_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($file, ['Time', 'User', 'Target Room', 'AC Detail', 'Activity']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s'),
                    $log->user->name ?? '-',
                    $log->room ?? '-',
                    $log->ac ?? '-',
                    strtoupper($log->activity),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
