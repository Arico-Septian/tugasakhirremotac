<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SmartAC</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="/js/chart.umd.js"></script>
    @include('components.sidebar-styles')
    <style>
        .dashboard-rooms-panel {
            padding: 24px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.035);
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
            background: rgba(255, 255, 255, 0.04);
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
            background: rgba(255, 255, 255, 0.03);
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
    @include('components.sidebar')

    <div class="main-content">
        {{-- HEADER --}}
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
                @include('components.notification-bell')
                <span id="systemStatus" class="pill pill-offline">
                    <span class="dot"></span><span>Offline</span>
                </span>
            </div>
        </header>

        {{-- BODY --}}
        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-5">

                    {{-- Critical alert banner --}}
                    <div id="tempAlertBanner" class="alert alert-error" hidden>
                        <i class="fa-solid fa-triangle-exclamation alert-icon"></i>
                        <div class="flex-1 min-w-0">
                            <p class="alert-title">Critical Temperature Detected</p>
                            <p id="tempAlertRooms" class="alert-body text-xs leading-relaxed"></p>
                        </div>
                        <button type="button" onclick="document.getElementById('tempAlertBanner').hidden = true"
                                class="btn-icon" style="border:0;background:transparent;color:var(--coral);">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    {{-- Stat cards --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                        <div class="stat-card acc-cyan">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Rooms</p>
                                    <p class="stat-value">{{ $rooms->count() }}</p>
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
                                    <p class="stat-value">{{ $totalAc }}</p>
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
                                    <p class="stat-value">{{ $activeAc }}</p>
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
                                    <p class="stat-value">{{ $inactiveAc }}</p>
                                    <p class="stat-meta">Powered off</p>
                                </div>
                                <div class="stat-icon"><i class="fa-regular fa-circle"></i></div>
                            </div>
                        </div>
                    </div>

                    {{-- Temperature chart --}}
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-temperature-half"></i> Live</p>
                                <h2 class="panel-title">Room Temperatures</h2>
                            </div>
                            <span id="chartLastUpdated" class="panel-meta">—</span>
                        </div>
                        <div style="height:280px;">
                            <canvas id="tempChart"></canvas>
                        </div>
                    </div>

                    {{-- Server rooms preview --}}
                    <section class="panel dashboard-rooms-panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="dashboard-rooms-title">Server Rooms</h2>
                                <p class="dashboard-rooms-subtitle">{{ $totalRooms }} ruangan terdaftar</p>
                            </div>
                            <a href="{{ route('rooms.overview') }}" class="dashboard-rooms-action" aria-label="Lihat semua server rooms">
                                <span>Lihat<br>semua</span>
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>

                        @php
                            $previewRooms = $rooms->take(4);
                        @endphp

                        @if ($previewRooms->isNotEmpty())
                            <div class="dashboard-room-list">
                                @foreach ($previewRooms as $room)
                                    @php
                                        $temperature = $room->temperature;
                                        $status = $room->device_status === 'online' ? 'online' : 'offline';
                                    @endphp
                                    <a href="{{ route('rooms.overview') }}"
                                       class="dashboard-room-row"
                                       data-dashboard-room-id="{{ $room->id }}">
                                        <div class="dashboard-room-main">
                                            <h3 class="dashboard-room-name">{{ ucfirst($room->name) }}</h3>
                                            <p class="dashboard-room-meta">
                                                {{ $room->acUnits->count() }} unit &middot; {{ $room->device_id ?: '-' }}
                                            </p>
                                        </div>
                                        <div id="dashboard-room-temp-{{ $room->id }}" class="dashboard-room-temp">
                                            @if ($temperature !== null)
                                                {{ number_format((float) $temperature, 1) }}&deg;C
                                            @else
                                                -- &deg;C
                                            @endif
                                        </div>
                                        <div class="dashboard-room-status {{ $status }}">
                                            {{ strtoupper($status) }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state" style="padding:28px 12px;">
                                <div class="empty-icon"><i class="fa-solid fa-server"></i></div>
                                <p class="empty-title">Belum ada ruangan</p>
                                <p class="empty-sub">Tambahkan ruangan untuk mulai monitoring</p>
                            </div>
                        @endif
                    </section>

                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
const roomNames = @json($rooms->pluck('name')->map(fn($n) => str_replace('server ', 'srv ', $n)));
const roomTemps = @json($rooms->pluck('temperature')->map(fn($t) => is_null($t) ? null : (float)$t)->values());

function tempColor(t) {
    if (t === null || isNaN(Number(t))) return 'rgba(100,116,139,0.55)';
    if (t > 30) return 'rgba(251,113,133,0.85)';   // coral
    if (t > 25) return 'rgba(251,191,36,0.85)';    // amber
    return 'rgba(77,212,255,0.85)';                // cyan
}

const valueLabelPlugin = {
    id: 'valueLabel',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;
        chart.data.datasets.forEach((ds, i) => {
            if (ds.type !== 'bar') return;
            const meta = chart.getDatasetMeta(i);
            meta.data.forEach((bar, idx) => {
                const v = ds.data[idx];
                if (Number.isFinite(v) && v > 0) {
                    ctx.save();
                    ctx.fillStyle = '#f5f7fb';
                    ctx.font = `600 ${window.innerWidth < 768 ? 9 : 10.5}px Inter`;
                    ctx.textAlign = 'center';
                    ctx.fillText(v + '°C', bar.x, bar.y - 6);
                    ctx.restore();
                }
            });
        });
    }
};

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

function sendTempNotification(roomName, temp) {
    if (!notifEnabled || Notification.permission !== 'granted') return;
    const now = Date.now();
    if (notifCooldown[roomName] && now - notifCooldown[roomName] < 5 * 60 * 1000) return;
    notifCooldown[roomName] = now;
    new Notification('⚠️ Suhu Kritis', {
        body: `${roomName}: ${temp}°C — segera periksa ruangan`,
        tag: 'temp-' + roomName,
    });
}

let tempChart;
function initChart() {
    const ctx = document.getElementById('tempChart');
    if (!ctx) return;
    tempChart = new Chart(ctx, {
        plugins: [valueLabelPlugin],
        data: {
            labels: roomNames,
            datasets: [
                { type: 'bar', label: 'Temperature (°C)', data: roomTemps,
                  backgroundColor: roomTemps.map(tempColor), borderRadius: 6,
                  barPercentage: 0.7, categoryPercentage: 0.78 },
                { type: 'line', label: 'Trend', data: roomTemps,
                  borderColor: 'rgba(245,247,251,0.32)', backgroundColor: 'transparent',
                  tension: 0.4, pointBackgroundColor: '#f5f7fb', pointRadius: 3, borderWidth: 1.5 }
            ]
        },
        options: {
            maintainAspectRatio: false, responsive: true,
            plugins: {
                legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 }, boxWidth: 10, boxHeight: 10 } },
                tooltip: {
                    backgroundColor: 'rgba(7,16,31,0.96)',
                    titleColor: '#f5f7fb', bodyColor: '#cbd5e1',
                    borderColor: 'rgba(77,212,255,0.35)', borderWidth: 1,
                    padding: 10, cornerRadius: 10, displayColors: false,
                    callbacks: { label: c => ` ${c.parsed.y}°C` }
                }
            },
            scales: {
                x: { ticks: { color: '#64748b', maxRotation: 0, font: { size: 10 } }, grid: { display: false } },
                y: { suggestedMin: 20, suggestedMax: 40,
                     ticks: { color: '#64748b', font: { size: 10 }, callback: v => v + '°C' },
                     grid: { color: 'rgba(255,255,255,0.04)' } }
            }
        }
    });
}

function refreshTemperature() {
    if (!tempChart) return;
    fetch('/temperature')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data || !tempChart) return;
            const safe = data.map(r => { const t = parseFloat(r.temp); return isNaN(t) ? null : t; });
            tempChart.data.datasets[0].data = safe;
            tempChart.data.datasets[1].data = safe;
            tempChart.data.datasets[0].backgroundColor = safe.map(tempColor);
            tempChart.update();

            data.forEach(room => {
                const tempEl = document.getElementById(`dashboard-room-temp-${room.id}`);
                if (!tempEl) return;

                const temp = parseFloat(room.temp);
                tempEl.textContent = isNaN(temp) ? '-- \u00b0C' : `${temp.toFixed(1)}\u00b0C`;
            });

            const tsEl = document.getElementById('chartLastUpdated');
            if (tsEl) tsEl.textContent = 'Updated ' + new Date().toLocaleTimeString('id-ID');

            const critical = data.filter(r => { const t = parseFloat(r.temp); return !isNaN(t) && t > 30; });
            const banner = document.getElementById('tempAlertBanner');
            const alertMsg = document.getElementById('tempAlertRooms');
            if (banner && alertMsg) {
                if (critical.length > 0) {
                    alertMsg.textContent = critical.map(r => `${r.name} (${parseFloat(r.temp).toFixed(1)}°C)`).join(' · ');
                    banner.hidden = false;
                } else {
                    banner.hidden = true;
                }
            }
            critical.forEach(r => sendTempNotification(r.name, parseFloat(r.temp).toFixed(1)));
        })
        .catch(() => {});
}

setInterval(refreshTemperature, 5000);

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
    setTimeout(refreshTemperature, 400);
    setTimeout(refreshDashboardRoomStatuses, 600);
});
</script>
@include('components.sidebar-scripts')
</body>
</html>
