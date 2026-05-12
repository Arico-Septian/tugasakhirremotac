<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Activity Log — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('components.sidebar-styles')
    <style>
        .toolbar-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-row .search-input { flex: 1; min-width: 240px; }


        .stat-card .stat-label-sm {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-3);
        }

        .stat-card .stat-num-lg {
            font-family: 'JetBrains Mono', monospace;
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            margin: 8px 0 6px;
        }

        .stat-card .stat-sub {
            font-size: 11px;
            color: var(--ink-3);
        }

        .stat-card.acc-cyan .stat-num-lg   { color: var(--cyan); }
        .stat-card.acc-mint .stat-num-lg   { color: var(--mint); }
        .stat-card.acc-lavender .stat-num-lg { color: var(--lavender); }
        .stat-card.acc-coral .stat-num-lg  { color: var(--coral); }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 8px 3px 10px;
            border-radius: 999px;
            background: rgba(77, 212, 255, 0.1);
            border: 1px solid rgba(77, 212, 255, 0.25);
            font-size: 11px;
            color: var(--cyan);
        }

        .filter-tag button {
            background: none;
            border: none;
            color: var(--cyan);
            cursor: pointer;
            padding: 0;
            line-height: 1;
            opacity: 0.7;
        }

        .filter-tag button:hover { opacity: 1; }

        .adv-filter {
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-top: none;
        }

        .adv-filter-body {
            padding: 14px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .adv-filter-body .field { margin: 0; }

        .tbl tbody tr { transition: background 0.12s ease; }
        .tbl tbody tr:hover { background: var(--panel-2); }

        .tbl.tbl-log th {
            font-size: 10.5px;
            letter-spacing: 0.1em;
            padding: 12px 16px;
        }

        .tbl.tbl-log td {
            padding: 10px 16px;
            vertical-align: middle;
            border-top: 1px solid var(--line-soft);
        }

        .tbl.tbl-log tbody tr:first-child td { border-top: none; }

        .log-empty { color: var(--ink-5, var(--ink-4)); opacity: 0.5; }

        .log-user {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .log-user .name {
            color: var(--ink-0);
            font-weight: 500;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-room {
            color: var(--ink-1);
            font-size: 13px;
        }

        .log-detail {
            color: var(--ink-2);
            font-size: 12.5px;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-time {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
            font-family: 'JetBrains Mono', monospace;
            white-space: nowrap;
        }

        .log-time .t { color: var(--ink-1); font-size: 12.5px; font-weight: 500; }
        .log-time .d { color: var(--ink-4); font-size: 10.5px; }
    </style>
</head>

<body>
    <div class="custom-bg"></div>
    <div id="overlay"></div>

    <div class="layout">
        @include('components.sidebar')

        <div class="main-content">
            <header class="main-header">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden btn-icon" title="Menu">
                        <i class="fa-solid fa-bars text-xs"></i>
                    </button>
                    <div class="app-header-title">
                        <h1>Activity Log</h1>
                        <p>System &amp; user activity</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @include('components.notification-bell')
                    <span id="systemStatus" class="pill pill-online">
                        <span class="dot"></span>
                        <span>Online</span>
                    </span>
                </div>
            </header>

            <div class="page-body">
                <div class="app-content">
                    <div class="app-content-inner space-y-4">

                        @php
                            function activityBadge($activity)
                            {
                                if (str_starts_with($activity, 'set_temp_')) {
                                    $val = str_replace('set_temp_', '', $activity);
                                    return ["TEMP {$val}°C", 'act-amber'];
                                }
                                if (str_starts_with($activity, 'mode_')) {
                                    $val = strtoupper(str_replace('mode_', '', $activity));
                                    return ["MODE {$val}", 'act-cyan'];
                                }
                                if (str_starts_with($activity, 'fan_speed_')) {
                                    $val = strtoupper(str_replace('fan_speed_', '', $activity));
                                    return ["FAN {$val}", 'act-cyan'];
                                }
                                if (str_starts_with($activity, 'swing_')) {
                                    $val = strtoupper(str_replace('swing_', '', $activity));
                                    return ["SWING {$val}", 'act-lavender'];
                                }
                                return match ($activity) {
                                    'login' => ['LOGIN', 'act-mint'],
                                    'logout' => ['LOGOUT', 'act-slate'],
                                    'on' => ['POWER ON', 'act-mint'],
                                    'off' => ['POWER OFF', 'act-coral'],
                                    'bulk_on' => ['ALL ON', 'act-mint'],
                                    'bulk_off' => ['ALL OFF', 'act-coral'],
                                    'set_timer' => ['SET TIMER', 'act-amber'],
                                    'timer_on' => ['TIMER ON', 'act-mint'],
                                    'timer_off' => ['TIMER OFF', 'act-amber'],
                                    'control_ac' => ['CONTROL AC', 'act-lavender'],
                                    'add_room' => ['ADD ROOM', 'act-cyan'],
                                    'delete_room' => ['DELETE ROOM', 'act-coral'],
                                    'add_ac' => ['ADD AC', 'act-cyan'],
                                    'delete_ac' => ['DELETE AC', 'act-coral'],
                                    'add_user' => ['ADD USER', 'act-lavender'],
                                    'delete_user' => ['DELETE USER', 'act-coral'],
                                    'update_role' => ['UPDATE ROLE', 'act-lavender'],
                                    'activate_user' => ['ACTIVATE', 'act-mint'],
                                    'deactivate_user' => ['DEACTIVATE', 'act-coral'],
                                    'change_password' => ['CHG PASSWORD', 'act-amber'],
                                    default => [strtoupper($activity), 'act-lavender'],
                                };
                            }

                            $activityOptions = [
                                'auth'      => 'Auth (login/logout)',
                                'ac'        => 'Kontrol AC',
                                'room'      => 'Ruangan',
                                'user'      => 'User',
                                'power_on'  => 'Power ON',
                                'power_off' => 'Power OFF',
                                'temp'      => 'Set Temperature',
                                'mode'      => 'Mode Change',
                                'fan'       => 'Fan Speed',
                                'swing'     => 'Swing',
                            ];

                            $rangeOptions = [
                                ''      => 'Semua waktu',
                                'today' => 'Hari ini',
                                '7d'    => '7 Hari',
                                '30d'   => '30 Hari',
                            ];
                            $currentRange = request('range', '');
                            $rangeLabel = $rangeOptions[$currentRange] ?? 'Custom';

                            $activeFilters = array_filter(
                                request()->only(['user_id', 'room', 'activity', 'date_from', 'date_to', 'search', 'range']),
                            );

                            $quickCats = [
                                ''     => 'All',
                                'auth' => 'Auth',
                                'ac'   => 'AC',
                                'room' => 'Ruangan',
                                'user' => 'User',
                            ];
                            $currentCat = in_array(request('activity'), ['auth', 'ac', 'room', 'user']) ? request('activity') : '';
                        @endphp

                        {{-- Stats — 4 kartu sesuai mockup --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                            <div class="stat-card acc-cyan">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Total Aktivitas</p>
                                        <p class="stat-num-lg">{{ $stats['total'] }}</p>
                                        <p class="stat-sub">Halaman {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-mint">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Login Events</p>
                                        <p class="stat-num-lg">{{ $stats['auth'] }}</p>
                                        <p class="stat-sub">+{{ $stats['auth24'] }} dalam 24 jam</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-right-to-bracket"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-lavender">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Kontrol AC</p>
                                        <p class="stat-num-lg">{{ $stats['ac'] }}</p>
                                        <p class="stat-sub">on/off · mode · suhu</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-snowflake"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-coral">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Destructive</p>
                                        <p class="stat-num-lg">{{ $stats['destructive'] }}</p>
                                        <p class="stat-sub">delete user · room</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-trash"></i></div>
                                </div>
                            </div>
                        </div>

                        {{-- Toolbar + Table wrapper (no space-y between them) --}}
                        <div class="tbl-wrap">
                        {{-- Toolbar: search + quick category + date range --}}
                        <form method="GET" action="/logs" id="filterForm">
                            <div class="tbl-toolbar">
                                <label class="search-input" style="flex:1;max-width:none;">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input name="search" value="{{ request('search') }}" type="text"
                                        placeholder="Cari user / ruangan / aktivitas…" autocomplete="off">
                                    @if (request('search'))
                                        <button type="button" class="clear" title="Clear"
                                            onclick="removeFilter('search')"><i
                                                class="fa-solid fa-xmark text-[10px]"></i></button>
                                    @endif
                                </label>

                                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                    <div class="segmented">
                                        @foreach ($quickCats as $val => $label)
                                            <button type="button"
                                                class="seg {{ $currentCat === $val ? 'active' : '' }}"
                                                data-quick="{{ $val }}">{{ $label }}</button>
                                        @endforeach
                                    </div>

                                    @if (Auth::user()->role == 'admin')
                                        <button type="button" onclick="deleteAllLogs()"
                                            class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash text-[10px]"></i> Delete Activity
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Advanced (custom date / specific user / specific room) --}}
                            <div class="adv-filter">
                                <div class="adv-filter-body">
                                    <div class="field">
                                        <label class="field-label">User</label>
                                        <select class="input" name="user_id" onchange="this.form.submit()">
                                            <option value="">Semua User</option>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}"
                                                    {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="field-label">Room</label>
                                        <select class="input" name="room" onchange="this.form.submit()">
                                            <option value="">Semua Room</option>
                                            @foreach ($rooms as $r)
                                                <option value="{{ $r }}"
                                                    {{ request('room') === $r ? 'selected' : '' }}>
                                                    {{ $r }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="field-label">Aksi spesifik</label>
                                        <select class="input" name="activity" onchange="this.form.submit()">
                                            <option value="">Semua Aksi</option>
                                            @foreach ($activityOptions as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ request('activity') === $val ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="field-label">Dari Tanggal</label>
                                        <input class="input" type="date" name="date_from"
                                            value="{{ request('date_from') }}" onchange="this.form.submit()">
                                    </div>
                                    <div class="field">
                                        <label class="field-label">Sampai Tanggal</label>
                                        <input class="input" type="date" name="date_to"
                                            value="{{ request('date_to') }}" onchange="this.form.submit()">
                                    </div>
                                    <div class="field" style="align-self:end;display:flex;gap:8px;">
                                        @if (count($activeFilters))
                                            <a href="/logs" class="btn btn-sm"
                                                style="background:var(--panel-2);border-color:var(--line);">
                                                <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>

                        {{-- Active filter tags --}}
                        @if (count($activeFilters))
                            <div class="active-filters">
                                @if (request('search'))
                                    <span class="filter-tag">
                                        <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                                        "{{ request('search') }}"
                                        <button onclick="removeFilter('search')" title="Hapus">
                                            <i class="fa-solid fa-xmark text-[9px]"></i>
                                        </button>
                                    </span>
                                @endif
                                @if (request('user_id'))
                                    @php $uName = $users->firstWhere('id', request('user_id'))?->name ?? request('user_id'); @endphp
                                    <span class="filter-tag">
                                        <i class="fa-solid fa-user text-[9px]"></i>
                                        {{ $uName }}
                                        <button onclick="removeFilter('user_id')" title="Hapus">
                                            <i class="fa-solid fa-xmark text-[9px]"></i>
                                        </button>
                                    </span>
                                @endif
                                @if (request('room'))
                                    <span class="filter-tag">
                                        <i class="fa-solid fa-server text-[9px]"></i>
                                        {{ request('room') }}
                                        <button onclick="removeFilter('room')" title="Hapus">
                                            <i class="fa-solid fa-xmark text-[9px]"></i>
                                        </button>
                                    </span>
                                @endif
                                @if (request('date_from') || request('date_to'))
                                    <span class="filter-tag">
                                        <i class="fa-regular fa-calendar text-[9px]"></i>
                                        {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y') : '...' }}
                                        –
                                        {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y') : '...' }}
                                        <button onclick="removeFilter('date_from'); removeFilter('date_to')"
                                            title="Hapus">
                                            <i class="fa-solid fa-xmark text-[9px]"></i>
                                        </button>
                                    </span>
                                @endif
                            </div>
                        @endif

                        @php
                            $isEmpty = fn ($v) => $v === null || $v === '' || $v === '-' || $v === '—';
                        @endphp

                        {{-- Log table --}}
                            {{-- Mobile cards --}}
                            <div class="md:hidden">
                                @forelse ($logs as $log)
                                    <div style="padding:12px 16px;border-bottom:1px solid var(--line-soft);">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <div class="log-user">
                                                <span class="avatar"
                                                    style="width:26px;height:26px;font-size:10.5px;border-radius:7px;">
                                                    {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                                </span>
                                                <span class="name">{{ $log->user->name ?? '—' }}</span>
                                            </div>
                                            @php [$label, $class] = activityBadge($log->activity); @endphp
                                            <span class="act-badge {{ $class }}">{{ $label }}</span>
                                        </div>
                                        <div class="text-xs space-y-0.5" style="color:var(--ink-3);">
                                            @if (!$isEmpty($log->room))
                                                <p><i class="fa-solid fa-server mr-1.5 text-[10px]"
                                                        style="color:var(--ink-4);"></i>{{ $log->room }}</p>
                                            @endif
                                            @if (!$isEmpty($log->ac))
                                                <p><i class="fa-solid fa-snowflake mr-1.5 text-[10px]"
                                                        style="color:var(--ink-4);"></i>{{ $log->ac }}</p>
                                            @endif
                                        </div>
                                        <p class="text-mono text-xs mt-1.5" style="color:var(--ink-4);">
                                            {{ $log->created_at->format('H:i') }}
                                            <span style="opacity:0.7;">·
                                                {{ $log->created_at->format('d M Y') }}</span>
                                        </p>
                                    </div>
                                @empty
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                        <p class="empty-title">Tidak ada hasil</p>
                                        <p class="empty-sub">Coba ubah atau reset filter</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Desktop table --}}
                            <div class="hidden md:block" style="overflow-x:auto;">
                                <table class="tbl tbl-log">
                                    <thead>
                                        <tr>
                                            <th style="width:22%;">User</th>
                                            <th style="width:18%;">Room</th>
                                            <th>Detail</th>
                                            <th style="width:16%;">Activity</th>
                                            <th style="width:14%;" class="whitespace-nowrap">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            <tr>
                                                <td>
                                                    <div class="log-user">
                                                        <span class="avatar"
                                                            style="width:28px;height:28px;font-size:11px;border-radius:8px;flex-shrink:0;">
                                                            {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                                        </span>
                                                        <span class="name">{{ $log->user->name ?? '—' }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($isEmpty($log->room))
                                                        <span class="log-empty">—</span>
                                                    @else
                                                        <span class="log-room">{{ $log->room }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($isEmpty($log->ac))
                                                        <span class="log-empty">—</span>
                                                    @else
                                                        <span class="log-detail" title="{{ $log->ac }}">{{ $log->ac }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php [$label, $class] = activityBadge($log->activity); @endphp
                                                    <span class="act-badge {{ $class }}">{{ $label }}</span>
                                                </td>
                                                <td>
                                                    <div class="log-time">
                                                        <span class="t">{{ $log->created_at->format('H:i') }}</span>
                                                        <span class="d">{{ $log->created_at->format('d M Y') }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="empty-state">
                                                        <div class="empty-icon"><i
                                                                class="fa-solid fa-magnifying-glass"></i></div>
                                                        <p class="empty-title">Tidak ada hasil</p>
                                                        <p class="empty-sub">Coba ubah atau reset filter</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="tbl-footer">
                                <p>
                                    Menampilkan <span class="text-mono"
                                        style="color:var(--ink-1);">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span>
                                    dari <span class="text-mono"
                                        style="color:var(--ink-1);">{{ $logs->total() }}</span> aktivitas
                                </p>
                                <div class="pager">
                                    @php
                                        $current = $logs->currentPage();
                                        $last = $logs->lastPage();
                                        $pages = [];
                                        if ($last <= 7) {
                                            $pages = range(1, $last);
                                        } else {
                                            $pages[] = 1;
                                            if ($current > 3) $pages[] = '...';
                                            for ($i = max(2, $current - 1); $i <= min($last - 1, $current + 1); $i++) $pages[] = $i;
                                            if ($current < $last - 2) $pages[] = '...';
                                            $pages[] = $last;
                                        }
                                    @endphp

                                    @if ($logs->onFirstPage())
                                        <span class="disabled"><i class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                    @else
                                        <a href="{{ $logs->previousPageUrl() }}"><i class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                    @endif

                                    @foreach ($pages as $p)
                                        @if ($p === '...')
                                            <span class="disabled">…</span>
                                        @elseif ($p == $current)
                                            <span class="active text-mono">{{ $p }}</span>
                                        @else
                                            <a class="text-mono" href="{{ $logs->url($p) }}">{{ $p }}</a>
                                        @endif
                                    @endforeach

                                    @if ($logs->hasMorePages())
                                        <a href="{{ $logs->nextPageUrl() }}"><i class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                    @else
                                        <span class="disabled"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.bottom-nav')
    @include('components.sidebar-scripts')

    <script>
        // Quick category buttons → set activity = auth/ac/room/user
        document.querySelectorAll('[data-quick]').forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.getAttribute('data-quick');
                const url = new URL(window.location.href);
                url.searchParams.delete('page');
                if (val) {
                    url.searchParams.set('activity', val);
                } else {
                    url.searchParams.delete('activity');
                }
                window.location.href = url.toString();
            });
        });

        function removeFilter(key) {
            const url = new URL(window.location.href);
            url.searchParams.delete(key);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function deleteAllLogs() {
            if (!confirm('Hapus SEMUA log? Tindakan ini tidak dapat dibatalkan.')) return;

            fetch('/logs/delete-all', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Delete failed');
                    return r.json();
                })
                .then(() => {
                    window.smToast ? window.smToast('Semua log berhasil dihapus', 'success') : alert(
                        'Semua log berhasil dihapus');
                    setTimeout(() => location.reload(), 800);
                })
                .catch(err => {
                    window.smToast ? window.smToast('Gagal menghapus log', 'error') : alert('Gagal menghapus log');
                    console.error('Delete error:', err);
                });
        }

        function setSystemStatus(online) {
            const el = document.getElementById('systemStatus');
            if (!el) return;
            el.className = 'pill ' + (online ? 'pill-online' : 'pill-offline');
            el.innerHTML = `<span class="dot"></span><span>${online ? 'Online' : 'Offline'}</span>`;
        }
        window.addEventListener('online', () => setSystemStatus(true));
        window.addEventListener('offline', () => setSystemStatus(false));
        document.addEventListener('DOMContentLoaded', () => {
            setSystemStatus(navigator.onLine);
        });
    </script>
</body>

</html>
