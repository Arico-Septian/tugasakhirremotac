<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Activity Log — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        .filter-bar {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-xl);
            padding: 16px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 10px;
        }

        .filter-grid .field {
            margin: 0;
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
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

        .filter-tag button:hover {
            opacity: 1;
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
                                'power_on' => 'Power ON',
                                'power_off' => 'Power OFF',
                                'temp' => 'Set Temperature',
                                'mode' => 'Mode Change',
                                'fan' => 'Fan Speed',
                                'swing' => 'Swing',
                                'auth' => 'Login / Logout',
                                'user_mgmt' => 'User Management',
                                'room_mgmt' => 'Room / AC Mgmt',
                            ];

                            $activeFilters = array_filter(
                                request()->only(['user_id', 'room', 'activity', 'date_from', 'date_to', 'search']),
                            );
                        ?>

                        
                        <div class="filter-bar">
                            <form method="GET" action="/logs" id="filterForm">
                                <div class="filter-grid">
                                    
                                    <div class="field" style="grid-column: span 2;">
                                        <label class="field-label">Search</label>
                                        <div class="input-icon-wrap">
                                            <i class="fa-solid fa-magnifying-glass"></i>
                                            <input class="input" type="text" name="search"
                                                value="<?php echo e(request('search')); ?>"
                                                placeholder="User, room, atau detail AC...">
                                        </div>
                                    </div>

                                    
                                    <div class="field">
                                        <label class="field-label">User</label>
                                        <select class="input" name="user_id">
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
                                        <select class="input" name="room">
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
                                        <label class="field-label">Aksi</label>
                                        <select class="input" name="activity">
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
                                        <label class="field-label">Dari Tanggal</label>
                                        <input class="input" type="date" name="date_from"
                                            value="<?php echo e(request('date_from')); ?>">
                                    </div>

                                    
                                    <div class="field">
                                        <label class="field-label">Sampai Tanggal</label>
                                        <input class="input" type="date" name="date_to"
                                            value="<?php echo e(request('date_to')); ?>">
                                    </div>
                                </div>

                                <div class="filter-actions">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-filter text-[10px]"></i>
                                        Filter
                                    </button>
                                    <?php if(count($activeFilters)): ?>
                                        <a href="/logs" class="btn btn-sm"
                                            style="background:rgba(255,255,255,0.05);border-color:var(--line);">
                                            <i class="fa-solid fa-xmark text-[10px]"></i>
                                            Reset
                                        </a>
                                    <?php endif; ?>
                                    <?php if(Auth::user()->role == 'admin'): ?>
                                        <button type="button" onclick="deleteAllLogs()" class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                            <span class="hidden sm:inline">Hapus Semua</span>
                                        </button>
                                    <?php endif; ?>
                                    <span class="text-xs" style="color:var(--ink-4);margin-left:4px;">
                                        <?php echo e($logs->total()); ?> hasil ditemukan
                                    </span>
                                </div>
                            </form>

                            
                            <?php if(count($activeFilters)): ?>
                                <div class="active-filters">
                                    <?php if(request('search')): ?>
                                        <span class="filter-tag">
                                            <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                                            "<?php echo e(request('search')); ?>"
                                            <button onclick="removeFilter('search')" title="Hapus">
                                                <i class="fa-solid fa-xmark text-[9px]"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('user_id')): ?>
                                        <?php $uName = $users->firstWhere('id', request('user_id'))?->name ?? request('user_id'); ?>
                                        <span class="filter-tag">
                                            <i class="fa-solid fa-user text-[9px]"></i>
                                            <?php echo e($uName); ?>

                                            <button onclick="removeFilter('user_id')" title="Hapus">
                                                <i class="fa-solid fa-xmark text-[9px]"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('room')): ?>
                                        <span class="filter-tag">
                                            <i class="fa-solid fa-server text-[9px]"></i>
                                            <?php echo e(request('room')); ?>

                                            <button onclick="removeFilter('room')" title="Hapus">
                                                <i class="fa-solid fa-xmark text-[9px]"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('activity')): ?>
                                        <span class="filter-tag">
                                            <i class="fa-solid fa-bolt text-[9px]"></i>
                                            <?php echo e($activityOptions[request('activity')] ?? request('activity')); ?>

                                            <button onclick="removeFilter('activity')" title="Hapus">
                                                <i class="fa-solid fa-xmark text-[9px]"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                    <?php if(request('date_from') || request('date_to')): ?>
                                        <span class="filter-tag">
                                            <i class="fa-regular fa-calendar text-[9px]"></i>
                                            <?php echo e(request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y') : '...'); ?>

                                            –
                                            <?php echo e(request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y') : '...'); ?>

                                            <button onclick="removeFilter('date_from'); removeFilter('date_to')"
                                                title="Hapus">
                                                <i class="fa-solid fa-xmark text-[9px]"></i>
                                            </button>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4">
                            <div class="stat-card acc-cyan">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label">Total Activity</p>
                                        <p class="stat-value"><?php echo e($logs->total()); ?></p>
                                        <p class="stat-meta">
                                            <?php echo e(count($activeFilters) ? 'Hasil filter' : 'All-time event count'); ?></p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-mint">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label">Page</p>
                                        <p class="stat-value"><?php echo e($logs->currentPage()); ?><span class="text-mono"
                                                style="font-size:16px;color:var(--ink-3);"> /
                                                <?php echo e($logs->lastPage()); ?></span></p>
                                        <p class="stat-meta"><?php echo e($logs->perPage()); ?> entries per page</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-layer-group"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-lavender">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label">Showing</p>
                                        <p class="stat-value"><?php echo e($logs->firstItem() ?? 0); ?><span class="text-mono"
                                                style="font-size:16px;color:var(--ink-3);">–<?php echo e($logs->lastItem() ?? 0); ?></span>
                                        </p>
                                        <p class="stat-meta">In view right now</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="tbl-wrap">
                            
                            <div class="md:hidden">
                                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div style="padding:14px 16px;border-bottom:1px solid var(--line-soft);">
                                        <div class="flex items-center justify-between gap-2 mb-1.5">
                                            <span class="text-sm font-semibold"
                                                style="color:var(--ink-0);"><?php echo e($log->user->name ?? '-'); ?></span>
                                            <?php [$label, $class] = activityBadge($log->activity); ?>
                                            <span class="act-badge <?php echo e($class); ?>"><?php echo e($label); ?></span>
                                        </div>
                                        <div class="text-xs space-y-0.5" style="color:var(--ink-3);">
                                            <?php if($log->room): ?>
                                                <p><i
                                                        class="fa-solid fa-server mr-1.5 text-[10px]"></i><?php echo e($log->room); ?>

                                                </p>
                                            <?php endif; ?>
                                            <?php if($log->ac): ?>
                                                <p><i
                                                        class="fa-solid fa-snowflake mr-1.5 text-[10px]"></i><?php echo e($log->ac); ?>

                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-mono text-xs mt-1.5" style="color:var(--ink-4);">
                                            <?php echo e($log->created_at->format('d M Y H:i')); ?></p>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                        <p class="empty-title">Tidak ada hasil</p>
                                        <p class="empty-sub">Coba ubah atau reset filter</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            
                            <div class="hidden md:block" style="overflow-x:auto;">
                                <table class="tbl">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Room</th>
                                            <th>Detail</th>
                                            <th>Activity</th>
                                            <th class="whitespace-nowrap">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td>
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="avatar"
                                                            style="width:26px;height:26px;font-size:10.5px;border-radius:7px;">
                                                            <?php echo e(strtoupper(substr($log->user->name ?? '?', 0, 1))); ?>

                                                        </span>
                                                        <span class="font-medium"
                                                            style="color:var(--ink-0);"><?php echo e($log->user->name ?? '—'); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo e($log->room ?? '—'); ?></td>
                                                <td class="max-w-[240px] truncate" title="<?php echo e($log->ac); ?>">
                                                    <?php echo e($log->ac ?? '—'); ?></td>
                                                <td>
                                                    <?php [$label, $class] = activityBadge($log->activity); ?>
                                                    <span
                                                        class="act-badge <?php echo e($class); ?>"><?php echo e($label); ?></span>
                                                </td>
                                                <td class="num whitespace-nowrap">
                                                    <?php echo e($log->created_at->format('d M Y H:i')); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
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
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tbl-footer">
                                <p>
                                    Showing <span class="text-mono"
                                        style="color:var(--ink-1);"><?php echo e($logs->firstItem() ?? 0); ?></span>–<span
                                        class="text-mono"
                                        style="color:var(--ink-1);"><?php echo e($logs->lastItem() ?? 0); ?></span>
                                    of <span class="text-mono"
                                        style="color:var(--ink-1);"><?php echo e($logs->total()); ?></span>
                                </p>
                                <div class="pager">
                                    <?php if($logs->onFirstPage()): ?>
                                        <span class="disabled"><i
                                                class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                    <?php else: ?>
                                        <a href="<?php echo e($logs->previousPageUrl()); ?>"><i
                                                class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                    <?php endif; ?>
                                    <span class="active text-mono"><?php echo e($logs->currentPage()); ?></span>
                                    <?php if($logs->hasMorePages()): ?>
                                        <a href="<?php echo e($logs->nextPageUrl()); ?>"><i
                                                class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                    <?php else: ?>
                                        <span class="disabled"><i
                                                class="fa-solid fa-chevron-right text-[9px]"></i></span>
                                    <?php endif; ?>
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
    </script>
</body>

</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/logs/index.blade.php ENDPATH**/ ?>