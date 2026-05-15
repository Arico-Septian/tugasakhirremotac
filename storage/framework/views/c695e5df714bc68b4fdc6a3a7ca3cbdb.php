<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Activity Log — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo app('Illuminate\Foundation\Vite')('resources/js/app.js'); ?>
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-feature-settings: 'tnum' 1, 'lnum' 1, 'cv11' 1;
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: -0.02em;
            margin: 8px 0 6px;
        }

        .stat-card .stat-sub {
            font-size: 11px;
            color: var(--ink-3);
        }

        /* Angka utama selalu putih untuk hierarki yang kuat */
        .stat-card .stat-num-lg { color: var(--ink-0); }

        /* Label kecil di atas mengambil warna accent per kartu */
        .stat-card.acc-cyan     .stat-label-sm { color: var(--cyan); }
        .stat-card.acc-mint     .stat-label-sm { color: var(--mint); }
        .stat-card.acc-lavender .stat-label-sm { color: var(--lavender); }
        .stat-card.acc-coral    .stat-label-sm { color: var(--coral); }

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
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        @media (min-width: 768px) {
            .adv-filter-body {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .adv-filter-body {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        /* Toolbar responsiveness for very small screens */
        @media (max-width: 768px) {
            .tbl-toolbar {
                gap: 8px;
                padding: 10px 12px;
            }

            .tbl-toolbar label.search-input {
                flex: 1;
                min-width: 160px;
                max-width: 400px;
            }

            .tbl-toolbar > div {
                display: inline-flex;
                flex-wrap: nowrap;
                gap: 6px;
                align-items: center;
                flex-shrink: 0;
            }

            .segmented {
                display: inline-flex;
                gap: 3px;
            }

            .segmented .seg {
                font-size: 11px;
                padding: 6px 10px;
            }

            .btn.btn-danger {
                padding: 6px 10px;
                font-size: 11px;
                white-space: nowrap;
            }

            .search-input input {
                font-size: 13px;
                padding: 6px 10px;
            }

            .search-input i {
                font-size: 13px;
            }
        }

        /* Very small screens (< 480px) */
        @media (max-width: 480px) {
            .tbl-toolbar {
                padding: 8px;
                gap: 6px;
            }

            .tbl-toolbar label.search-input {
                flex: 0 1 120px;
                min-width: 0;
                transition: flex 0.2s ease;
            }

            .tbl-toolbar label.search-input:focus-within {
                flex: 1;
            }

            .tbl-toolbar > div {
                display: inline-flex;
                gap: 4px;
                align-items: center;
                flex-shrink: 0;
            }

            .segmented {
                display: inline-flex;
                gap: 2px;
            }

            .segmented .seg {
                font-size: 10px;
                padding: 5px 6px;
                min-width: auto;
            }

            .btn.btn-danger {
                padding: 5px 8px;
                font-size: 10px;
            }

            .btn.btn-danger span {
                display: none;
            }

            .btn.btn-danger i {
                margin: 0 !important;
                font-size: 11px;
            }

            .search-input {
                width: 100%;
            }

            .search-input input {
                font-size: 12px;
                padding: 6px 8px;
            }

            .search-input input::placeholder {
                color: transparent;
            }

            .search-input i {
                font-size: 12px;
                transition: opacity 0.2s ease;
            }

            .search-input:focus-within i {
                opacity: 0;
                pointer-events: none;
            }
        }

        /* Advanced filter landscape mode */
        @media (max-width: 896px) and (orientation: landscape) {
            .adv-filter-body {
                grid-template-columns: repeat(3, 1fr) !important;
            }
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

        /* Sortable table headers */
        .tbl-log th {
            cursor: pointer;
            user-select: none;
            position: relative;
            transition: background 0.12s ease;
        }

        .tbl-log th:hover {
            background: var(--panel-2);
        }

        .tbl-log th.sortable::after {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-left: 6px;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" fill="%23999"><path d="M3 2L6 0l3 2M3 10L6 12l3-2"/></svg>') center no-repeat;
            background-size: contain;
            opacity: 0.4;
            vertical-align: -1px;
        }

        .tbl-log th.sort-asc::after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" fill="%234dd4ff"><path d="M6 1L3 4h6z"/></svg>');
            opacity: 1;
        }

        .tbl-log th.sort-desc::after {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 12" fill="%234dd4ff"><path d="M6 11L3 8h6z"/></svg>');
            opacity: 1;
        }

        /* Enhanced pagination */
        .pager {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .pager a, .pager span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: var(--ink-2);
            background: transparent;
            border: 1px solid transparent;
            text-decoration: none;
            transition: all 0.12s ease;
        }

        .pager a {
            cursor: pointer;
        }

        .pager a:hover {
            background: var(--panel-2);
            color: var(--ink-0);
            border-color: var(--line);
        }

        .pager .active {
            background: var(--cyan-soft);
            color: var(--cyan);
            border-color: var(--cyan-soft-2);
            font-weight: 600;
        }

        .pager .disabled {
            opacity: 0.35;
            pointer-events: none;
            cursor: not-allowed;
        }

        .pager i {
            opacity: 0.7;
        }

        .pager a:hover i {
            opacity: 1;
        }

        /* Page sections spacing */
        .app-content-inner > * + * {
            margin-top: 32px;
        }
    </style>
</head>

<body>
    <div class="custom-bg"></div>
    <div id="overlay"></div>

    <div class="layout">
        <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
                    <?php echo $__env->make('components.notification-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <span id="systemStatus" class="pill pill-online">
                        <span class="dot"></span>
                        <span>Online</span>
                    </span>
                </div>
            </header>

            <div class="page-body">
                <div class="app-content">
                    <div class="app-content-inner space-y-4">

                        <?php
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
                        ?>

                        
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                            <div class="stat-card acc-cyan">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Total Aktivitas</p>
                                        <p class="stat-num-lg"><?php echo e($stats['total']); ?></p>
                                        <p class="stat-sub">Halaman <?php echo e($logs->currentPage()); ?> / <?php echo e($logs->lastPage()); ?></p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-mint">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Login Events</p>
                                        <p class="stat-num-lg"><?php echo e($stats['auth']); ?></p>
                                        <p class="stat-sub">+<?php echo e($stats['auth24']); ?> dalam 24 jam</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-right-to-bracket"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-lavender">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Kontrol AC</p>
                                        <p class="stat-num-lg"><?php echo e($stats['ac']); ?></p>
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
                                        <p class="stat-num-lg"><?php echo e($stats['destructive']); ?></p>
                                        <p class="stat-sub">delete user · room</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-trash"></i></div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="tbl-wrap">
                        
                        <form method="GET" action="/logs" id="filterForm">
                            <div class="tbl-toolbar">
                                <label class="search-input" style="flex:1;max-width:none;">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input name="search" value="<?php echo e(request('search')); ?>" type="text"
                                        placeholder="Cari user / ruangan / aktivitas…" autocomplete="off">
                                    <?php if(request('search')): ?>
                                        <button type="button" class="clear" title="Clear"
                                            onclick="removeFilter('search')"><i
                                                class="fa-solid fa-xmark text-[10px]"></i></button>
                                    <?php endif; ?>
                                </label>

                                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                    <div class="segmented">
                                        <?php $__currentLoopData = $quickCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <button type="button"
                                                class="seg <?php echo e($currentCat === $val ? 'active' : ''); ?>"
                                                data-quick="<?php echo e($val); ?>"><?php echo e($label); ?></button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>

                                    <?php if(Auth::user()->role == 'admin'): ?>
                                        <button type="button" onclick="deleteAllLogs()"
                                            class="btn btn-danger btn-sm" title="Delete Activity">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="adv-filter">
                                <div class="adv-filter-body">
                                    <div class="field">
                                        <label class="field-label">User</label>
                                        <select class="input" name="user_id" onchange="this.form.submit()">
                                            <option value="">Semua User</option>
                                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($u->id); ?>"
                                                    <?php echo e(request('user_id') == $u->id ? 'selected' : ''); ?>>
                                                    <?php echo e($u->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="field-label">Room</label>
                                        <select class="input" name="room" onchange="this.form.submit()">
                                            <option value="">Semua Room</option>
                                            <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($r); ?>"
                                                    <?php echo e(request('room') === $r ? 'selected' : ''); ?>>
                                                    <?php echo e($r); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="field-label">Aksi spesifik</label>
                                        <select class="input" name="activity" onchange="this.form.submit()">
                                            <option value="">Semua Aksi</option>
                                            <?php $__currentLoopData = $activityOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($val); ?>"
                                                    <?php echo e(request('activity') === $val ? 'selected' : ''); ?>>
                                                    <?php echo e($label); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="field-label"><i class="fa-regular fa-calendar text-[10px]" style="margin-right:4px;"></i>Dari Tanggal</label>
                                        <input class="input" type="date" name="date_from"
                                            value="<?php echo e(request('date_from')); ?>" onchange="this.form.submit()" style="cursor:pointer;">
                                    </div>
                                    <div class="field">
                                        <label class="field-label"><i class="fa-regular fa-calendar text-[10px]" style="margin-right:4px;"></i>Sampai Tanggal</label>
                                        <input class="input" type="date" name="date_to"
                                            value="<?php echo e(request('date_to')); ?>" onchange="this.form.submit()" style="cursor:pointer;">
                                    </div>
                                    <?php if(count($activeFilters)): ?>
                                        <div class="field" style="align-self:end;display:flex;gap:8px;">
                                            <a href="/logs" class="btn btn-sm"
                                                style="background:var(--panel-2);border-color:var(--line);">
                                                <i class="fa-solid fa-xmark text-[10px]"></i> Reset
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>

                        <?php
                            $isEmpty = fn ($v) => $v === null || $v === '' || $v === '-' || $v === '—';
                        ?>

                        
                            
                            <div class="md:hidden" id="logsMobile">
                                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div style="padding:12px 16px;border-bottom:1px solid var(--line-soft);">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <div class="log-user">
                                                <?php if($log->user && $log->user->avatar_url): ?>
                                                    <img src="<?php echo e($log->user->avatar_url); ?>" alt="<?php echo e($log->user->name); ?>"
                                                         class="avatar"
                                                         style="width:26px;height:26px;border-radius:7px;object-fit:cover;">
                                                <?php else: ?>
                                                    <span class="avatar"
                                                        style="width:26px;height:26px;font-size:10.5px;border-radius:7px;">
                                                        <?php echo e(strtoupper(substr($log->user->name ?? '?', 0, 1))); ?>

                                                    </span>
                                                <?php endif; ?>
                                                <span class="name"><?php echo e($log->user->name ?? '—'); ?></span>
                                            </div>
                                            <?php [$label, $class] = activityBadge($log->activity); ?>
                                            <span class="act-badge <?php echo e($class); ?>"><?php echo e($label); ?></span>
                                        </div>
                                        <div class="text-xs space-y-0.5" style="color:var(--ink-3);">
                                            <?php if(!$isEmpty($log->room)): ?>
                                                <p><i class="fa-solid fa-server mr-1.5 text-[10px]"
                                                        style="color:var(--ink-4);"></i><?php echo e($log->room); ?></p>
                                            <?php endif; ?>
                                            <?php if(!$isEmpty($log->ac)): ?>
                                                <p><i class="fa-solid fa-snowflake mr-1.5 text-[10px]"
                                                        style="color:var(--ink-4);"></i><?php echo e($log->ac); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-mono text-xs mt-1.5" style="color:var(--ink-4);">
                                            <?php echo e($log->created_at->format('H:i')); ?>

                                            <span style="opacity:0.7;">·
                                                <?php echo e($log->created_at->format('d M Y')); ?></span>
                                        </p>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                        <p class="empty-title">No activities found</p>
                                        <p class="empty-sub"><?php echo e(count($activeFilters) ? 'Try adjusting your filters or ' : ''); ?><a href="/logs" style="color:var(--cyan);text-decoration:underline;cursor:pointer;">reset all filters</a></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            
                            <?php if(count($activeFilters)): ?>
                                <div style="display:flex;flex-wrap:wrap;gap:8px;padding:10px 0;align-items:center;border-bottom:1px solid var(--line-soft);">
                                    <span style="font-size:12px;color:var(--ink-3);font-weight:500;">Filters:</span>
                                    <?php if(request('search')): ?>
                                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:rgba(77,212,255,0.1);border:1px solid rgba(77,212,255,0.25);border-radius:999px;font-size:12px;color:var(--cyan);">
                                            <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                                            "<?php echo e(request('search')); ?>"
                                            <button onclick="removeFilter('search')" style="background:none;border:none;color:var(--cyan);cursor:pointer;padding:0;font-size:10px;"><i class="fa-solid fa-xmark"></i></button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('activity')): ?>
                                        <?php $actLabel = $activityOptions[request('activity')] ?? request('activity'); ?>
                                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:rgba(77,212,255,0.1);border:1px solid rgba(77,212,255,0.25);border-radius:999px;font-size:12px;color:var(--cyan);">
                                            <i class="fa-solid fa-filter text-[9px]"></i>
                                            <?php echo e($actLabel); ?>

                                            <button onclick="removeFilter('activity')" style="background:none;border:none;color:var(--cyan);cursor:pointer;padding:0;font-size:10px;"><i class="fa-solid fa-xmark"></i></button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('user_id')): ?>
                                        <?php $userName = $users->firstWhere('id', request('user_id'))?->name ?? request('user_id'); ?>
                                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:rgba(77,212,255,0.1);border:1px solid rgba(77,212,255,0.25);border-radius:999px;font-size:12px;color:var(--cyan);">
                                            <i class="fa-solid fa-user text-[9px]"></i>
                                            <?php echo e($userName); ?>

                                            <button onclick="removeFilter('user_id')" style="background:none;border:none;color:var(--cyan);cursor:pointer;padding:0;font-size:10px;"><i class="fa-solid fa-xmark"></i></button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('room')): ?>
                                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:rgba(77,212,255,0.1);border:1px solid rgba(77,212,255,0.25);border-radius:999px;font-size:12px;color:var(--cyan);">
                                            <i class="fa-solid fa-server text-[9px]"></i>
                                            <?php echo e(request('room')); ?>

                                            <button onclick="removeFilter('room')" style="background:none;border:none;color:var(--cyan);cursor:pointer;padding:0;font-size:10px;"><i class="fa-solid fa-xmark"></i></button>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            
                            <div class="hidden md:block" style="overflow-x:auto;">
                                <table class="tbl tbl-log">
                                    <thead>
                                        <tr>
                                            <th style="width:22%;" class="sortable" data-sort="user_name" onclick="handleSort('user_name')">USER</th>
                                            <th style="width:18%;" class="sortable" data-sort="room" onclick="handleSort('room')">ROOM</th>
                                            <th>DETAIL</th>
                                            <th style="width:16%;" class="sortable" data-sort="activity" onclick="handleSort('activity')">ACTIVITY</th>
                                            <th style="width:14%;" class="whitespace-nowrap sortable" data-sort="created_at" onclick="handleSort('created_at')">TIME</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logsTbody">
                                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td>
                                                    <div class="log-user">
                                                        <?php if($log->user && $log->user->avatar_url): ?>
                                                            <img src="<?php echo e($log->user->avatar_url); ?>" alt="<?php echo e($log->user->name); ?>"
                                                                 class="avatar"
                                                                 style="width:28px;height:28px;border-radius:8px;flex-shrink:0;object-fit:cover;">
                                                        <?php else: ?>
                                                            <span class="avatar"
                                                                style="width:28px;height:28px;font-size:11px;border-radius:8px;flex-shrink:0;">
                                                                <?php echo e(strtoupper(substr($log->user->name ?? '?', 0, 1))); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                        <span class="name"><?php echo e($log->user->name ?? '—'); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if($isEmpty($log->room)): ?>
                                                        <span class="log-empty">—</span>
                                                    <?php else: ?>
                                                        <span class="log-room"><?php echo e($log->room); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($isEmpty($log->ac)): ?>
                                                        <span class="log-empty">—</span>
                                                    <?php else: ?>
                                                        <span class="log-detail" title="<?php echo e($log->ac); ?>"><?php echo e($log->ac); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php [$label, $class] = activityBadge($log->activity); ?>
                                                    <span class="act-badge <?php echo e($class); ?>"><?php echo e($label); ?></span>
                                                </td>
                                                <td>
                                                    <div class="log-time">
                                                        <span class="t"><?php echo e($log->created_at->format('H:i')); ?></span>
                                                        <span class="d"><?php echo e($log->created_at->format('d M Y')); ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="5">
                                                    <div class="empty-state">
                                                        <div class="empty-icon"><i
                                                                class="fa-solid fa-magnifying-glass"></i></div>
                                                        <p class="empty-title">No activities found</p>
                                                        <p class="empty-sub"><?php echo e(count($activeFilters) ? 'Try adjusting your filters or ' : ''); ?><a href="/logs" style="color:var(--cyan);text-decoration:underline;cursor:pointer;">reset all filters</a></p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tbl-footer">
                                <p>
                                    Menampilkan <span class="text-mono"
                                        style="color:var(--ink-1);"><?php echo e($logs->firstItem() ?? 0); ?>–<?php echo e($logs->lastItem() ?? 0); ?></span>
                                    dari <span class="text-mono"
                                        style="color:var(--ink-1);"><?php echo e($logs->total()); ?></span> aktivitas
                                </p>
                                <div class="pager">
                                    <?php
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
                                    ?>

                                    <?php if($logs->onFirstPage()): ?>
                                        <span class="disabled"><i class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                    <?php else: ?>
                                        <a href="<?php echo e($logs->previousPageUrl()); ?>"><i class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                    <?php endif; ?>

                                    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($p === '...'): ?>
                                            <span class="disabled">…</span>
                                        <?php elseif($p == $current): ?>
                                            <span class="active text-mono"><?php echo e($p); ?></span>
                                        <?php else: ?>
                                            <a class="text-mono" href="<?php echo e($logs->url($p)); ?>"><?php echo e($p); ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <?php if($logs->hasMorePages()): ?>
                                        <a href="<?php echo e($logs->nextPageUrl()); ?>"><i class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                    <?php else: ?>
                                        <span class="disabled"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('components.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.sidebar-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
            initializeSortIndicators();

            // Real-time: prepend log baru ke tabel tanpa reload
            function escapeHtml(s) {
                return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
            }
            function activityBadgeJs(activity) {
                const a = String(activity || '');
                if (a.startsWith('set_temp_')) return [`TEMP ${a.replace('set_temp_', '')}°C`, 'act-amber'];
                if (a.startsWith('mode_'))     return [`MODE ${a.replace('mode_', '').toUpperCase()}`, 'act-cyan'];
                if (a.startsWith('fan_speed_'))return [`FAN ${a.replace('fan_speed_', '').toUpperCase()}`, 'act-cyan'];
                if (a.startsWith('swing_'))    return [`SWING ${a.replace('swing_', '').toUpperCase()}`, 'act-lavender'];
                const map = {
                    login: ['LOGIN', 'act-mint'], logout: ['LOGOUT', 'act-slate'],
                    on: ['POWER ON', 'act-mint'], off: ['POWER OFF', 'act-coral'],
                    bulk_on: ['ALL ON', 'act-mint'], bulk_off: ['ALL OFF', 'act-coral'],
                    set_timer: ['SET TIMER', 'act-amber'], timer_on: ['TIMER ON', 'act-mint'],
                    timer_off: ['TIMER OFF', 'act-amber'], control_ac: ['CONTROL AC', 'act-lavender'],
                    add_room: ['ADD ROOM', 'act-cyan'], delete_room: ['DELETE ROOM', 'act-coral'],
                    add_ac: ['ADD AC', 'act-cyan'], delete_ac: ['DELETE AC', 'act-coral'],
                    add_user: ['ADD USER', 'act-lavender'], delete_user: ['DELETE USER', 'act-coral'],
                    update_role: ['UPDATE ROLE', 'act-lavender'],
                    activate_user: ['ACTIVATE', 'act-mint'], deactivate_user: ['DEACTIVATE', 'act-coral'],
                    change_password: ['CHG PASSWORD', 'act-amber'],
                };
                return map[a] || [a.toUpperCase(), 'act-lavender'];
            }

            function prependLogRow(payload) {
                // Skip kalau user di halaman lain (paginasi)
                const url = new URL(window.location.href);
                const onFirstPage = !url.searchParams.get('page') || url.searchParams.get('page') === '1';
                if (!onFirstPage) return;

                const name = escapeHtml(payload.user_name || '—');
                const initial = escapeHtml(payload.user_initial || (payload.user_name || '?').charAt(0).toUpperCase());
                const isEmpty = (v) => v == null || v === '' || v === '-' || v === '—';
                const [badgeLabel, badgeClass] = activityBadgeJs(payload.activity);
                const safeAvatar = payload.user_avatar ? escapeHtml(payload.user_avatar) : null;

                const now = new Date();
                const hh = String(now.getHours()).padStart(2, '0');
                const mm = String(now.getMinutes()).padStart(2, '0');
                const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                const dateStr = `${String(now.getDate()).padStart(2,'0')} ${months[now.getMonth()]} ${now.getFullYear()}`;

                // === DESKTOP TABLE ===
                const tbody = document.getElementById('logsTbody');
                if (tbody) {
                    const empty = tbody.querySelector('.empty-state');
                    if (empty) empty.closest('tr')?.remove();

                    const roomHtml = isEmpty(payload.room)
                        ? '<span class="log-empty">—</span>'
                        : `<span class="log-room">${escapeHtml(payload.room)}</span>`;
                    const acHtml = isEmpty(payload.ac)
                        ? '<span class="log-empty">—</span>'
                        : `<span class="log-detail" title="${escapeHtml(payload.ac)}">${escapeHtml(payload.ac)}</span>`;
                    const avatarHtml = safeAvatar
                        ? `<img src="${safeAvatar}" alt="${name}" class="avatar" style="width:28px;height:28px;border-radius:8px;flex-shrink:0;object-fit:cover;">`
                        : `<span class="avatar" style="width:28px;height:28px;font-size:11px;border-radius:8px;flex-shrink:0;">${initial}</span>`;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <div class="log-user">
                                ${avatarHtml}
                                <span class="name">${name}</span>
                            </div>
                        </td>
                        <td>${roomHtml}</td>
                        <td>${acHtml}</td>
                        <td><span class="act-badge ${badgeClass}">${escapeHtml(badgeLabel)}</span></td>
                        <td>
                            <div class="log-time">
                                <span class="t">${hh}:${mm}</span>
                                <span class="d">${dateStr}</span>
                            </div>
                        </td>`;
                    tbody.insertBefore(tr, tbody.firstChild);

                    const maxRows = 50;
                    while (tbody.children.length > maxRows) tbody.removeChild(tbody.lastChild);
                }

                // === MOBILE CARDS ===
                const mobile = document.getElementById('logsMobile');
                if (mobile) {
                    const empty = mobile.querySelector('.empty-state');
                    if (empty) empty.remove();

                    const mobileAvatar = safeAvatar
                        ? `<img src="${safeAvatar}" alt="${name}" class="avatar" style="width:26px;height:26px;border-radius:7px;object-fit:cover;">`
                        : `<span class="avatar" style="width:26px;height:26px;font-size:10.5px;border-radius:7px;">${initial}</span>`;

                    const card = document.createElement('div');
                    card.style.cssText = 'padding:12px 16px;border-bottom:1px solid var(--line-soft);';
                    card.innerHTML = `
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <div class="log-user">
                                ${mobileAvatar}
                                <span class="name">${name}</span>
                            </div>
                            <span class="act-badge ${badgeClass}">${escapeHtml(badgeLabel)}</span>
                        </div>
                        <div class="text-xs space-y-0.5" style="color:var(--ink-3);">
                            ${!isEmpty(payload.room) ? `<p><i class="fa-solid fa-server mr-1.5 text-[10px]" style="color:var(--ink-4);"></i>${escapeHtml(payload.room)}</p>` : ''}
                            ${!isEmpty(payload.ac) ? `<p><i class="fa-solid fa-snowflake mr-1.5 text-[10px]" style="color:var(--ink-4);"></i>${escapeHtml(payload.ac)}</p>` : ''}
                        </div>
                        <p class="text-mono text-xs mt-1.5" style="color:var(--ink-4);">
                            ${hh}:${mm} <span style="opacity:0.7;">· ${dateStr}</span>
                        </p>`;
                    mobile.insertBefore(card, mobile.firstChild);

                    const maxCards = 50;
                    while (mobile.children.length > maxCards) mobile.removeChild(mobile.lastChild);
                }
            }

            function clearAllLogs() {
                const tbody = document.getElementById('logsTbody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                    <p class="empty-title">No activities found</p>
                                    <p class="empty-sub"><a href="/logs" style="color:var(--cyan);text-decoration:underline;cursor:pointer;">reset all filters</a></p>
                                </div>
                            </td>
                        </tr>`;
                }
                const mobile = document.getElementById('logsMobile');
                if (mobile) {
                    mobile.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                            <p class="empty-title">No activities found</p>
                            <p class="empty-sub"><a href="/logs" style="color:var(--cyan);text-decoration:underline;cursor:pointer;">reset all filters</a></p>
                        </div>`;
                }
            }

            if (window.Echo) {
                window.Echo.channel('device-status')
                    .listen('.UserLogCreated', (e) => prependLogRow(e))
                    .listen('.UserLogsCleared', () => clearAllLogs());
            }
        });

        // Column sorting
        function handleSort(column) {
            const url = new URL(window.location.href);
            const currentSort = url.searchParams.get('sort');
            const currentOrder = url.searchParams.get('order') || 'asc';

            if (currentSort === column) {
                url.searchParams.set('order', currentOrder === 'asc' ? 'desc' : 'asc');
            } else {
                url.searchParams.set('sort', column);
                url.searchParams.set('order', 'asc');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        function initializeSortIndicators() {
            const params = new URLSearchParams(window.location.search);
            const sortColumn = params.get('sort');
            const sortOrder = params.get('order') || 'asc';

            if (sortColumn) {
                const th = document.querySelector(`th[data-sort="${sortColumn}"]`);
                if (th) {
                    th.classList.remove('sortable');
                    th.classList.add(sortOrder === 'asc' ? 'sort-asc' : 'sort-desc');
                }
            }
        }
    </script>
</body>

</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/logs/index.blade.php ENDPATH**/ ?>