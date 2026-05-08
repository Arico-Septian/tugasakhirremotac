<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Activity Log — SmartAC</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #1e293b; background: #fff; }

        .header { padding: 16px 20px 12px; border-bottom: 2px solid #0ea5e9; margin-bottom: 12px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .logo-wrap { display: flex; align-items: center; gap: 10px; }
        .logo-box { width: 32px; height: 32px; background: linear-gradient(135deg, #0ea5e9, #818cf8); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .logo-box span { color: #fff; font-size: 14px; }
        .brand-name { font-size: 16px; font-weight: 700; color: #0f172a; }
        .brand-sub { font-size: 8px; color: #64748b; margin-top: 1px; }
        .export-meta { text-align: right; font-size: 8px; color: #64748b; }
        .export-meta .date { font-size: 9px; color: #0f172a; font-weight: 600; }

        .title-row { margin-top: 8px; }
        .report-title { font-size: 13px; font-weight: 700; color: #0f172a; }
        .report-subtitle { font-size: 8px; color: #64748b; margin-top: 2px; }

        .filter-summary { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 20px; }
        .filter-chip { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 2px 7px; font-size: 7.5px; color: #475569; }
        .filter-chip strong { color: #0f172a; }

        table { width: 100%; border-collapse: collapse; margin: 0 0 16px; }
        thead tr { background: #0f172a; }
        thead th { color: #fff; padding: 6px 8px; text-align: left; font-size: 8px; font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody td { padding: 5px 8px; font-size: 8.5px; color: #334155; vertical-align: middle; }
        .badge { display: inline-block; padding: 1.5px 6px; border-radius: 4px; font-size: 7.5px; font-weight: 700; letter-spacing: 0.02em; }
        .act-mint    { background: #d1fae5; color: #065f46; }
        .act-coral   { background: #fee2e2; color: #991b1b; }
        .act-cyan    { background: #cffafe; color: #164e63; }
        .act-amber   { background: #fef3c7; color: #92400e; }
        .act-lavender{ background: #ede9fe; color: #4c1d95; }
        .act-slate   { background: #f1f5f9; color: #475569; }
        .num { font-family: 'DejaVu Sans Mono', monospace; }

        .footer { text-align: center; font-size: 7.5px; color: #94a3b8; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0; }
        .total-row { background: #f8fafc !important; font-weight: 700; color: #0f172a !important; border-top: 2px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="logo-wrap">
                <div class="logo-box"><span>&#10052;</span></div>
                <div>
                    <div class="brand-name">SmartAC</div>
                    <div class="brand-sub">Control Suite</div>
                </div>
            </div>
            <div class="export-meta">
                <div class="date">{{ $exportedAt }}</div>
                <div>Exported by SmartAC System</div>
            </div>
        </div>
        <div class="title-row">
            <div class="report-title">Activity Log Report</div>
            <div class="report-subtitle">Total {{ $logs->count() }} record(s) ditampilkan</div>
        </div>
    </div>

    @if (array_filter($filters))
        <div class="filter-summary">
            <span style="font-size:7.5px;color:#64748b;padding:2px 0;">Filter aktif:</span>
            @if (!empty($filters['date_from']) || !empty($filters['date_to']))
                <span class="filter-chip">
                    <strong>Tanggal:</strong>
                    {{ !empty($filters['date_from']) ? \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') : '...' }}
                    –
                    {{ !empty($filters['date_to']) ? \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') : '...' }}
                </span>
            @endif
            @if (!empty($filters['room']))
                <span class="filter-chip"><strong>Room:</strong> {{ $filters['room'] }}</span>
            @endif
            @if (!empty($filters['activity']))
                <span class="filter-chip"><strong>Aksi:</strong> {{ $filters['activity'] }}</span>
            @endif
            @if (!empty($filters['search']))
                <span class="filter-chip"><strong>Search:</strong> {{ $filters['search'] }}</span>
            @endif
        </div>
    @endif

    @php
        function pdfActivityBadge($activity) {
            if (str_starts_with($activity, 'set_temp_')) return [str_replace('set_temp_', 'TEMP ', $activity) . '°C', 'act-amber'];
            if (str_starts_with($activity, 'mode_')) return ['MODE ' . strtoupper(str_replace('mode_', '', $activity)), 'act-cyan'];
            if (str_starts_with($activity, 'fan_speed_')) return ['FAN ' . strtoupper(str_replace('fan_speed_', '', $activity)), 'act-cyan'];
            if (str_starts_with($activity, 'swing_')) return ['SWING ' . strtoupper(str_replace('swing_', '', $activity)), 'act-lavender'];
            return match ($activity) {
                'login'           => ['LOGIN',        'act-mint'],
                'logout'          => ['LOGOUT',        'act-slate'],
                'on'              => ['POWER ON',      'act-mint'],
                'off'             => ['POWER OFF',     'act-coral'],
                'bulk_on'         => ['ALL ON',        'act-mint'],
                'bulk_off'        => ['ALL OFF',       'act-coral'],
                'set_timer'       => ['SET TIMER',     'act-amber'],
                'timer_on'        => ['TIMER ON',      'act-mint'],
                'timer_off'       => ['TIMER OFF',     'act-amber'],
                'control_ac'      => ['CONTROL AC',    'act-lavender'],
                'add_room'        => ['ADD ROOM',      'act-cyan'],
                'delete_room'     => ['DELETE ROOM',   'act-coral'],
                'add_ac'          => ['ADD AC',        'act-cyan'],
                'delete_ac'       => ['DELETE AC',     'act-coral'],
                'add_user'        => ['ADD USER',      'act-lavender'],
                'delete_user'     => ['DELETE USER',   'act-coral'],
                'update_role'     => ['UPDATE ROLE',   'act-lavender'],
                'activate_user'   => ['ACTIVATE',      'act-mint'],
                'deactivate_user' => ['DEACTIVATE',    'act-coral'],
                'change_password' => ['CHG PASSWORD',  'act-amber'],
                default           => [strtoupper($activity), 'act-lavender'],
            };
        }
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:14%">User</th>
                <th style="width:14%">Room</th>
                <th style="width:28%">Detail AC</th>
                <th style="width:14%">Activity</th>
                <th style="width:16%">Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $i => $log)
                @php [$label, $cls] = pdfActivityBadge($log->activity); @endphp
                <tr>
                    <td class="num" style="color:#94a3b8;">{{ $i + 1 }}</td>
                    <td>{{ $log->user->name ?? '—' }}</td>
                    <td>{{ $log->room ?? '—' }}</td>
                    <td>{{ $log->ac ?? '—' }}</td>
                    <td><span class="badge {{ $cls }}">{{ $label }}</span></td>
                    <td class="num">{{ $log->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;">Tidak ada data</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5" style="padding:5px 8px;font-size:8px;">Total Records</td>
                <td class="num" style="font-size:8.5px;">{{ $logs->count() }} entries</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini digenerate otomatis oleh SmartAC Control Suite &mdash; {{ $exportedAt }}
    </div>
</body>
</html>
