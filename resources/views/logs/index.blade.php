<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.sidebar-styles')
    <style>
        .log-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }
        tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.15s ease; }
        tbody tr:hover { background: rgba(255,255,255,0.04); }
        @media (max-width: 1024px) { .page-body { padding-bottom: 72px; } }
    </style>
</head>
<body>
<div class="custom-bg"></div>
<div id="overlay" class="fixed inset-0 bg-black/50 z-40"></div>

<div class="layout">
    @include('components.sidebar')

    <div class="main-content">
        <header class="main-header">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white/10 text-gray-300 transition">
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Activity Log</h1>
                    <p class="text-xs text-blue-300 font-medium hidden sm:block">System & User Activity Monitoring</p>
                </div>
            </div>
            @if (Auth::user()->role == 'admin')
                <div class="flex items-center gap-2">
                    <a href="/logs/export"
                        class="flex items-center gap-1.5 bg-green-600/80 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">
                        <i class="fa-solid fa-file-csv"></i>
                        <span class="hidden sm:inline">Export CSV</span>
                    </a>
                    <form action="/logs/delete-all" method="POST" onsubmit="return confirm('Hapus SEMUA log? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button class="flex items-center gap-1.5 bg-red-600/80 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition">
                            <i class="fa-solid fa-trash"></i>
                            <span class="hidden sm:inline">Hapus Semua</span>
                        </button>
                    </form>
                </div>
            @endif
        </header>

        <div class="page-body">
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-5 space-y-4">

                @php
                    function activityBadge($activity)
                    {
                        if (str_starts_with($activity, 'set_temp_')) {
                            $val = str_replace('set_temp_', '', $activity);
                            return ["SUHU {$val}°C", 'bg-yellow-500/20 text-yellow-300'];
                        }
                        if (str_starts_with($activity, 'mode_')) {
                            $val = ucfirst(strtolower(str_replace('mode_', '', $activity)));
                            return ["MODE {$val}", 'bg-cyan-500/20 text-cyan-300'];
                        }
                        if (str_starts_with($activity, 'fan_speed_')) {
                            $val = ucfirst(strtolower(str_replace('fan_speed_', '', $activity)));
                            return ["FAN {$val}", 'bg-sky-500/20 text-sky-300'];
                        }
                        if (str_starts_with($activity, 'swing_')) {
                            $val = ucfirst(strtolower(str_replace('swing_', '', $activity)));
                            return ["SWING {$val}", 'bg-violet-500/20 text-violet-300'];
                        }
                        return match ($activity) {
                            'login'           => ['LOGIN',           'bg-green-500/20 text-green-300'],
                            'logout'          => ['LOGOUT',          'bg-gray-500/20 text-gray-300'],
                            'on'              => ['NYALA',           'bg-green-500/20 text-green-300'],
                            'off'             => ['MATI',            'bg-red-500/20 text-red-300'],
                            'bulk_on'         => ['ALL ON',          'bg-emerald-500/20 text-emerald-300'],
                            'bulk_off'        => ['ALL OFF',         'bg-rose-500/20 text-rose-300'],
                            'set_timer'       => ['SET TIMER',       'bg-amber-500/20 text-amber-300'],
                            'timer_on'        => ['TIMER ON',        'bg-teal-500/20 text-teal-300'],
                            'timer_off'       => ['TIMER OFF',       'bg-orange-500/20 text-orange-300'],
                            'control_ac'      => ['KONTROL AC',      'bg-purple-500/20 text-purple-300'],
                            'add_room'        => ['ADD ROOM',        'bg-blue-500/20 text-blue-300'],
                            'delete_room'     => ['DELETE ROOM',     'bg-red-500/20 text-red-300'],
                            'add_ac'          => ['ADD AC',          'bg-blue-500/20 text-blue-300'],
                            'delete_ac'       => ['DELETE AC',       'bg-orange-500/20 text-orange-300'],
                            'add_user'        => ['ADD USER',        'bg-indigo-500/20 text-indigo-300'],
                            'delete_user'     => ['DELETE USER',     'bg-red-500/20 text-red-300'],
                            'update_role'     => ['UPDATE ROLE',     'bg-indigo-500/20 text-indigo-300'],
                            'activate_user'   => ['AKTIFKAN USER',   'bg-green-500/20 text-green-300'],
                            'deactivate_user' => ['NONAKTIF USER',   'bg-red-500/20 text-red-300'],
                            'change_password' => ['GANTI PASSWORD',  'bg-amber-500/20 text-amber-300'],
                            default           => [strtoupper($activity), 'bg-purple-500/20 text-purple-300'],
                        };
                    }
                @endphp

                <!-- Stats Bar -->
                <div class="log-card px-5 py-4 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="text-xs text-gray-500 font-medium mb-0.5">Total Aktivitas</p>
                        <h2 class="text-3xl font-bold text-white">{{ $logs->total() }}</h2>
                    </div>
                    <p class="text-xs text-gray-600">Halaman {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</p>
                </div>

                <!-- Log Table -->
                <div class="log-card overflow-hidden">
                    <!-- Mobile view -->
                    <div class="md:hidden divide-y divide-white/05">
                        @foreach ($logs as $log)
                            <div class="p-4 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-white">{{ $log->user->name ?? '-' }}</span>
                                    @php [$label, $class] = activityBadge($log->activity); @endphp
                                    <span class="{{ $class }} px-2 py-0.5 rounded-md text-xs font-semibold">{{ $label }}</span>
                                </div>
                                <div class="text-xs text-gray-500 space-y-0.5">
                                    @if ($log->room)<p><i class="fa-solid fa-server mr-1 text-gray-700"></i>{{ $log->room }}</p>@endif
                                    @if ($log->ac)<p><i class="fa-solid fa-snowflake mr-1 text-gray-700"></i>{{ $log->ac }}</p>@endif
                                </div>
                                <p class="text-xs text-gray-600">{{ $log->created_at->format('d M Y H:i') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop view -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b border-white/08">
                                <tr class="text-left text-xs text-gray-500 font-semibold tracking-wide uppercase">
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Ruangan</th>
                                    <th class="px-4 py-3">Detail</th>
                                    <th class="px-4 py-3">Aktivitas</th>
                                    <th class="px-4 py-3 whitespace-nowrap">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td class="px-4 py-3 text-white font-medium">{{ $log->user->name }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ $log->room ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-400 max-w-[200px] truncate" title="{{ $log->ac }}">{{ $log->ac ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            @php [$label, $class] = activityBadge($log->activity); @endphp
                                            <span class="{{ $class }} px-2 py-0.5 rounded-md text-xs font-semibold">{{ $label }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">{{ $log->created_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between px-4 py-3 border-t border-white/05 flex-wrap gap-2">
                        <p class="text-xs text-gray-500">
                            Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            @if ($logs->onFirstPage())
                                <span class="px-3 py-1.5 bg-white/05 text-gray-600 rounded-lg text-xs cursor-not-allowed">«</span>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}"
                                    class="px-3 py-1.5 bg-white/08 hover:bg-white/15 text-white rounded-lg text-xs transition">«</a>
                            @endif
                            @if ($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}"
                                    class="px-3 py-1.5 bg-white/08 hover:bg-white/15 text-white rounded-lg text-xs transition">»</a>
                            @else
                                <span class="px-3 py-1.5 bg-white/05 text-gray-600 rounded-lg text-xs cursor-not-allowed">»</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')
@include('components.sidebar-scripts')
</body>
</html>
