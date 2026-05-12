<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SmartAC</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="/js/chart.umd.js"></script>
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        .trend-filter-select {
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            color: var(--ink-1);
            border-radius: var(--r-md);
            padding: 6px 10px;
            font-size: 11px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            outline: none;
            transition: var(--t-base);
        }

        .trend-filter-select:hover {
            background: var(--panel-2);
            border-color: var(--line);
        }

        .trend-filter-select:focus {
            border-color: var(--cyan);
        }

        .dashboard-rooms-panel {
            padding: 24px;
            border-radius: 20px;
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            box-shadow: var(--inset-hi);
        }

        .dashboard-rooms-panel .panel-header {
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .dashboard-rooms-title {
            font-size: 19px;
            font-weight: 700;
            line-height: 1.15;
            color: var(--ink-0);
        }

        .dashboard-rooms-subtitle {
            margin-top: 4px;
            font-size: 16px;
            line-height: 1.25;
            color: var(--ink-2);
        }

        .dashboard-rooms-action {
            min-width: 108px;
            min-height: 60px;
            padding: 10px 16px;
            border-radius: 12px;
            background: var(--panel-2);
            border: 1px solid var(--line-soft);
            color: var(--ink-0);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-weight: 700;
            line-height: 1.05;
            transition: var(--t-base);
        }

        .dashboard-rooms-action:hover {
            background: var(--panel-2);
            border-color: var(--line);
            color: var(--ink-0);
            transform: translateY(-1px);
        }

        .dashboard-room-list {
            display: grid;
            gap: 10px;
        }

        .dashboard-room-row {
            min-height: 72px;
            padding: 14px 16px 14px 18px;
            border-radius: 14px;
            background: var(--panel-2);
            border: 1px solid var(--line-soft);
            color: inherit;
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 18px;
            position: relative;
            transition: var(--t-base);
        }

        .dashboard-room-row::before {
            content: '';
            position: absolute;
            left: 18px;
            top: 15px;
            bottom: 15px;
            width: 5px;
            border-radius: 999px;
            background: #fca5a5;
        }

        .dashboard-room-row:hover {
            background: var(--panel-2);
            border-color: var(--line);
            transform: translateY(-1px);
        }

        .dashboard-room-main {
            min-width: 0;
            padding-left: 24px;
        }

        .dashboard-room-name {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.15;
            color: var(--ink-0);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dashboard-room-meta {
            margin-top: 3px;
            color: var(--ink-2);
            font-size: 14px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dashboard-room-temp {
            min-width: 76px;
            text-align: right;
            color: var(--ink-3);
            font-family: 'JetBrains Mono', monospace;
            font-size: 15px;
            font-weight: 700;
        }

        .dashboard-room-status {
            min-width: 82px;
            padding: 6px 12px;
            border-radius: 999px;
            text-align: center;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
        }

        .dashboard-room-status.online {
            background: var(--mint-soft);
            color: var(--mint);
        }

        .dashboard-room-status.offline {
            background: rgba(251, 113, 133, 0.14);
            color: #fca5a5;
        }

        @media (max-width: 640px) {
            .dashboard-rooms-panel {
                padding: 18px;
            }

            .dashboard-rooms-panel .panel-header {
                flex-wrap: nowrap;
            }

            .dashboard-rooms-action {
                min-width: 84px;
                min-height: 50px;
                padding: 8px 12px;
                font-size: 12px;
            }

            .dashboard-room-row {
                grid-template-columns: 1fr auto;
                gap: 10px;
                padding-right: 12px;
            }

            .dashboard-room-temp {
                grid-column: 2;
                grid-row: 1;
                min-width: 62px;
                font-size: 13px;
            }

            .dashboard-room-status {
                grid-column: 2;
                grid-row: 2;
                min-width: 74px;
                font-size: 10px;
                padding: 5px 9px;
            }

            .dashboard-room-name {
                font-size: 15px;
            }

            .dashboard-room-meta {
                font-size: 12px;
            }
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
                    <h1>Dashboard</h1>
                    <p>Overview &amp; live monitoring</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <?php echo $__env->make('components.notification-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <span id="systemStatus" class="pill pill-offline">
                    <span class="dot"></span><span>Offline</span>
                </span>
            </div>
        </header>

        
        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-5">

                    
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                        <div class="stat-card acc-cyan">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Rooms</p>
                                    <p class="stat-value"><?php echo e($rooms->count()); ?></p>
                                    <p class="stat-meta">Total registered</p>
                                </div>
                                <div class="stat-icon"><i class="fa-solid fa-server"></i></div>
                            </div>
                        </div>
                        <div class="stat-card acc-lavender">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">AC Units</p>
                                    <p class="stat-value"><?php echo e($totalAc); ?></p>
                                    <p class="stat-meta">Across all rooms</p>
                                </div>
                                <div class="stat-icon"><i class="fa-solid fa-snowflake"></i></div>
                            </div>
                        </div>
                        <div class="stat-card acc-mint">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Ac Active</p>
                                    <p class="stat-value"><?php echo e($activeAc); ?></p>
                                    <p class="stat-meta">Currently powered on</p>
                                </div>
                                <div class="stat-icon"><i class="fa-solid fa-bolt"></i></div>
                            </div>
                        </div>
                        <div class="stat-card acc-slate">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Ac Idle</p>
                                    <p class="stat-value"><?php echo e($inactiveAc); ?></p>
                                    <p class="stat-meta">Powered off</p>
                                </div>
                                <div class="stat-icon"><i class="fa-regular fa-circle"></i></div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-chart-line"></i> <span id="trendRangeLabel">Trend 1 jam terakhir</span></p>
                                <h2 class="panel-title">Room Temperatures</h2>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <select id="trendRange" class="trend-filter-select" title="Pilih range waktu">
                                    <option value="1h">1 Jam</option>
                                    <option value="3h">3 Jam</option>
                                    <option value="6h">6 Jam</option>
                                    <option value="24h">24 Jam</option>
                                </select>
                                <select id="trendLimit" class="trend-filter-select" title="Pilih jumlah ruangan">
                                    <option value="5">Top 5</option>
                                    <option value="10">Top 10</option>
                                    <option value="0">Semua</option>
                                </select>
                                <span id="chartLastUpdated" class="panel-meta">—</span>
                            </div>
                        </div>
                        <div style="height:300px;position:relative;">
                            <canvas id="tempChart"></canvas>
                            <div id="tempChartEmpty" class="empty-state" style="position:absolute;inset:0;display:none;align-items:center;justify-content:center;">
                                <div style="text-align:center;">
                                    <div class="empty-icon"><i class="fa-solid fa-temperature-empty"></i></div>
                                    <p class="empty-sub">Belum ada data suhu dalam 1 jam terakhir</p>
                                </div>
                            </div>
                        </div>
                        <p id="trendInfo" class="panel-meta" style="margin-top:8px;font-size:11px;color:var(--ink-4);"></p>
                    </div>

                    
                    <section class="panel dashboard-rooms-panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="dashboard-rooms-title">Server Rooms</h2>
                                <p class="dashboard-rooms-subtitle"><?php echo e($totalRooms); ?> ruangan terdaftar</p>
                            </div>
                            <a href="<?php echo e(route('rooms.overview')); ?>" class="dashboard-rooms-action" aria-label="Lihat semua server rooms">
                                <span>Lihat<br>semua</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>

                        <?php
                            $previewRooms = $rooms->take(4);
                        ?>

                        <?php if($previewRooms->isNotEmpty()): ?>
                            <div class="dashboard-room-list">
                                <?php $__currentLoopData = $previewRooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $temperature = $room->temperature;
                                        $status = $room->device_status === 'online' ? 'online' : 'offline';
                                    ?>
                                    <a href="<?php echo e(route('rooms.overview')); ?>"
                                       class="dashboard-room-row"
                                       data-dashboard-room-id="<?php echo e($room->id); ?>">
                                        <div class="dashboard-room-main">
                                            <h3 class="dashboard-room-name"><?php echo e(ucfirst($room->name)); ?></h3>
                                            <p class="dashboard-room-meta">
                                                <?php echo e($room->acUnits->count()); ?> unit &middot; <?php echo e($room->device_id ?: '-'); ?>

                                            </p>
                                        </div>
                                        <div id="dashboard-room-temp-<?php echo e($room->id); ?>" class="dashboard-room-temp">
                                            <?php if($temperature !== null): ?>
                                                <?php echo e(number_format((float) $temperature, 1)); ?>&deg;C
                                            <?php else: ?>
                                                -- &deg;C
                                            <?php endif; ?>
                                        </div>
                                        <div class="dashboard-room-status <?php echo e($status); ?>">
                                            <?php echo e(strtoupper($status)); ?>

                                        </div>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state" style="padding:28px 12px;">
                                <div class="empty-icon"><i class="fa-solid fa-server"></i></div>
                                <p class="empty-title">Belum ada ruangan</p>
                                <p class="empty-sub">Tambahkan ruangan untuk mulai monitoring</p>
                            </div>
                        <?php endif; ?>
                    </section>

                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('components.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
function tempColor(t) {
    if (t === null || isNaN(Number(t))) return 'rgba(100,116,139,0.55)';
    if (t > 30) return 'rgba(251,113,133,0.85)';   // coral
    if (t > 25) return 'rgba(251,191,36,0.85)';    // amber
    return 'rgba(77,212,255,0.85)';                // cyan
}

/* ===== NOTIFICATIONS ===== */
let notifEnabled = localStorage.getItem('notifEnabled') === 'true';
const notifCooldown = {};

function updateNotifButton() {
    const btn = document.getElementById('notifBtn');
    if (!btn) return;
    const i = btn.querySelector('i');
    if (notifEnabled && Notification.permission === 'granted') {
        btn.style.color = 'var(--amber)';
        btn.style.background = 'var(--amber-soft)';
        btn.style.borderColor = 'var(--amber-soft-2)';
        i.className = 'fa-solid fa-bell text-xs';
        btn.title = 'Notifications enabled — click to disable';
    } else {
        btn.style.color = '';
        btn.style.background = '';
        btn.style.borderColor = '';
        i.className = 'fa-regular fa-bell text-xs';
        btn.title = 'Enable critical temperature notifications';
    }
}

function toggleNotifications() {
    if (!('Notification' in window)) { window.smToast('Browser tidak mendukung notifikasi', 'error'); return; }
    if (notifEnabled) {
        notifEnabled = false;
        localStorage.setItem('notifEnabled', 'false');
        updateNotifButton();
        return;
    }
    Notification.requestPermission().then(perm => {
        notifEnabled = perm === 'granted';
        localStorage.setItem('notifEnabled', notifEnabled ? 'true' : 'false');
        updateNotifButton();
        if (perm === 'denied') window.smToast('Izin notifikasi ditolak', 'error');
    });
}

let tempChart;
function initChart() {
    const ctx = document.getElementById('tempChart');
    if (!ctx) return;
    tempChart = new Chart(ctx, {
        type: 'line',
        data: { labels: [], datasets: [] },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#94a3b8',
                        font: { family: 'Inter', size: 11 },
                        boxWidth: 10,
                        boxHeight: 10,
                        usePointStyle: true,
                        padding: 12,
                        generateLabels: function(chart) {
                            const original = Chart.defaults.plugins.legend.labels.generateLabels;
                            const labels = original.call(this, chart);
                            labels.forEach((label, i) => {
                                const ds = chart.data.datasets[i];
                                if (ds && ds._isOffline) {
                                    // Warna text memudar utk room offline
                                    label.fontColor = '#64748b';
                                }
                            });
                            return labels;
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(7,16,31,0.96)',
                    titleColor: '#f5f7fb',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(77,212,255,0.35)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: c => {
                            const v = c.parsed.y;
                            const valStr = v === null || isNaN(v) ? '—' : v + '°C';
                            return ` ${c.dataset.label}: ${valStr}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#64748b', maxRotation: 0, font: { size: 10 } },
                    grid: { display: false }
                },
                y: {
                    suggestedMin: 20,
                    suggestedMax: 35,
                    ticks: { color: '#64748b', font: { size: 10 }, callback: v => v + '°C' },
                    grid: { color: 'rgba(255,255,255,0.04)' }
                }
            }
        }
    });
}

function refreshTemperature() {
    fetch('/temperature')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            data.forEach(room => {
                const tempEl = document.getElementById(`dashboard-room-temp-${room.id}`);
                if (!tempEl) return;
                const temp = parseFloat(room.temp);
                tempEl.textContent = isNaN(temp) ? '-- \u00b0C' : `${temp.toFixed(1)}\u00b0C`;
            });
        })
        .catch(() => {});
}

function getTrendLimit() {
    const saved = localStorage.getItem('trendLimit');
    return saved !== null ? saved : '5';
}

function getTrendRange() {
    const saved = localStorage.getItem('trendRange');
    return saved !== null ? saved : '1h';
}

const RANGE_LABELS = {
    '1h':  'Trend 1 jam terakhir',
    '3h':  'Trend 3 jam terakhir',
    '6h':  'Trend 6 jam terakhir',
    '24h': 'Trend 24 jam terakhir',
};

function refreshTrendChart() {
    if (!tempChart) return;
    const limit = getTrendLimit();
    const range = getTrendRange();

    const labelEl = document.getElementById('trendRangeLabel');
    if (labelEl) labelEl.textContent = RANGE_LABELS[range] || RANGE_LABELS['1h'];

    fetch(`/temperature/trend?limit=${encodeURIComponent(limit)}&range=${encodeURIComponent(range)}`)
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data || !tempChart) return;

            const hasAnyData = (data.datasets || []).some(ds =>
                (ds.data || []).some(v => v !== null && !isNaN(v))
            );

            const emptyEl = document.getElementById('tempChartEmpty');
            const canvasEl = document.getElementById('tempChart');
            if (emptyEl && canvasEl) {
                emptyEl.style.display = hasAnyData ? 'none' : 'flex';
                canvasEl.style.display = hasAnyData ? 'block' : 'none';
            }

            tempChart.data.labels = data.labels || [];
            tempChart.data.datasets = (data.datasets || []).map(ds => {
                const tempStr = ds.current_temp !== null && ds.current_temp !== undefined
                    ? `${Number(ds.current_temp).toFixed(1)}°C`
                    : '—';
                // Warna memudar untuk room offline (alpha ~35%)
                const lineColor = ds.is_offline ? ds.color + '55' : ds.color;
                const fillColor = ds.is_offline ? ds.color + '11' : ds.color + '22';
                return {
                    label: `${ds.room} (${tempStr})`,
                    data: ds.data,
                    borderColor: lineColor,
                    backgroundColor: fillColor,
                    tension: 0.35,
                    borderWidth: ds.is_offline ? 1.5 : 2,
                    pointRadius: ds.is_offline ? 2 : 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: lineColor,
                    spanGaps: true,
                    fill: false,
                    _isOffline: ds.is_offline,
                    _offlineSince: ds.offline_since,
                };
            });
            tempChart.update();

            const infoEl = document.getElementById('trendInfo');
            if (infoEl) {
                if (data.total_rooms > data.shown) {
                    infoEl.textContent = `Menampilkan ${data.shown} dari ${data.total_rooms} ruangan (urutkan: suhu tertinggi). Klik nama ruangan di legenda untuk show/hide.`;
                } else {
                    infoEl.textContent = `Menampilkan ${data.shown} ruangan. Klik nama ruangan di legenda untuk show/hide.`;
                }
            }

            const tsEl = document.getElementById('chartLastUpdated');
            if (tsEl) tsEl.textContent = 'Updated ' + new Date().toLocaleTimeString('id-ID');
        })
        .catch(() => {});
}

setInterval(refreshTemperature, 5000);
setInterval(refreshTrendChart, 30000);

function refreshDashboardRoomStatuses() {
    fetch('/device-status', { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!Array.isArray(data)) return;

            data.forEach(device => {
                const row = document.querySelector(`[data-dashboard-room-id="${device.room_id}"]`);
                const statusEl = row?.querySelector('.dashboard-room-status');
                if (!statusEl) return;

                const isOnline = device.is_online === true || device.status === 'online';
                statusEl.classList.toggle('online', isOnline);
                statusEl.classList.toggle('offline', !isOnline);
                statusEl.textContent = isOnline ? 'ONLINE' : 'OFFLINE';
            });
        })
        .catch(() => {});
}

setInterval(refreshDashboardRoomStatuses, 5000);

function setSystemStatus(online) {
    const el = document.getElementById('systemStatus');
    if (!el) return;
    el.className = 'pill ' + (online ? 'pill-online' : 'pill-offline');
    el.innerHTML = `<span class="dot"></span><span>${online ? 'Online' : 'Offline'}</span>`;
}
window.addEventListener('online',  () => setSystemStatus(true));
window.addEventListener('offline', () => setSystemStatus(false));

document.addEventListener('DOMContentLoaded', () => {
    initChart();
    setSystemStatus(navigator.onLine);
    updateNotifButton();

    // Setup trend filter dropdowns
    const trendSelect = document.getElementById('trendLimit');
    if (trendSelect) {
        trendSelect.value = getTrendLimit();
        trendSelect.addEventListener('change', (e) => {
            localStorage.setItem('trendLimit', e.target.value);
            refreshTrendChart();
        });
    }
    const rangeSelect = document.getElementById('trendRange');
    if (rangeSelect) {
        rangeSelect.value = getTrendRange();
        rangeSelect.addEventListener('change', (e) => {
            localStorage.setItem('trendRange', e.target.value);
            refreshTrendChart();
        });
    }

    setTimeout(refreshTemperature, 400);
    setTimeout(refreshTrendChart, 500);
    setTimeout(refreshDashboardRoomStatuses, 600);
});
</script>
<?php echo $__env->make('components.sidebar-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views\dashboard\dashboard.blade.php ENDPATH**/ ?>