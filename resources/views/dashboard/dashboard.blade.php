<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="/js/chart.umd.js"></script>
    @include('components.sidebar-styles')
    <style>
        /* ===== STAT CARDS ===== */
        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--accent, #3b82f6);
            opacity: 0.6;
        }
        .stat-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .stat-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        /* ===== CHART CARD ===== */
        .chart-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }
        /* ===== NODE CARD ===== */
        .node-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 20px 24px;
            backdrop-filter: blur(10px);
            transition: all 0.25s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .node-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(59,130,246,0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        /* ===== ALERT BANNER ===== */
        .alert-banner {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            .stat-card { padding: 14px 16px; }
            .stat-icon { width: 36px; height: 36px; font-size: 15px; }
        }
    </style>
</head>
<body>
<div class="custom-bg"></div>

<div id="overlay" class="fixed inset-0 bg-black/50 z-40"></div>

<div class="layout">
    @include('components.sidebar')

    <div class="main-content">
        <!-- HEADER -->
        <header class="main-header">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white/10 text-gray-300 transition">
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Centralized AC</h1>
                    <p class="text-xs text-blue-300 font-medium hidden sm:block">Server Room Cooling Control</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div id="systemStatus"
                    class="flex items-center gap-1.5 bg-gray-500/10 text-gray-400 px-3 py-1.5 rounded-full text-xs font-medium transition-all">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                    <span>Offline</span>
                </div>
                <button id="notifBtn" onclick="toggleNotifications()" title="Notifikasi Suhu Kritis"
                    class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-gray-500 transition">
                    <i class="fa-regular fa-bell text-sm"></i>
                </button>
            </div>
        </header>

        <!-- PAGE BODY -->
        <div class="page-body">
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-5 space-y-6">

                <!-- CRITICAL TEMPERATURE ALERT -->
                <div id="tempAlertBanner" class="alert-banner" style="display:none;">
                    <i class="fa-solid fa-triangle-exclamation text-red-400 text-base mt-0.5 flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-red-300 font-semibold text-sm">Peringatan Suhu Kritis!</p>
                        <p id="tempAlertRooms" class="text-red-200 text-xs mt-0.5 leading-relaxed"></p>
                    </div>
                    <button onclick="document.getElementById('tempAlertBanner').style.display='none'"
                        class="text-red-400 hover:text-red-200 flex-shrink-0">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- STAT CARDS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                    <div class="stat-card" style="--accent:#3b82f6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400 font-medium mb-1">Ruangan</p>
                                <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $rooms->count() }}</h2>
                            </div>
                            <div class="stat-icon bg-blue-500/15 text-blue-400">
                                <i class="fa-solid fa-server"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent:#818cf8">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400 font-medium mb-1">Unit AC</p>
                                <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $totalAc }}</h2>
                            </div>
                            <div class="stat-icon bg-indigo-500/15 text-indigo-400">
                                <i class="fa-solid fa-snowflake"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent:#22c55e">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400 font-medium mb-1">AC Aktif</p>
                                <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $activeAc }}</h2>
                            </div>
                            <div class="stat-icon bg-green-500/15 text-green-400">
                                <i class="fa-solid fa-power-off"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card" style="--accent:#64748b">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-400 font-medium mb-1">AC Nonaktif</p>
                                <h2 class="text-2xl md:text-3xl font-bold text-white">{{ $inactiveAc }}</h2>
                            </div>
                            <div class="stat-icon bg-slate-500/15 text-slate-400">
                                <i class="fa-solid fa-plug-circle-xmark"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TEMPERATURE CHART -->
                <div class="chart-card">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div>
                            <h2 class="text-sm font-semibold text-white">Suhu Ruangan</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Real-time monitoring</p>
                        </div>
                        <span id="chartLastUpdated" class="text-xs text-gray-500 font-medium">--</span>
                    </div>
                    <div style="height: 260px;">
                        <canvas id="tempChart"></canvas>
                    </div>
                </div>

                <!-- SERVER ROOMS NODE CARD -->
                <a href="{{ route('rooms.overview') }}" class="node-card group">
                    <div class="flex-shrink-0 w-12 h-12 rounded-2xl
                                bg-gradient-to-br from-violet-600 to-indigo-600
                                flex items-center justify-center shadow-lg shadow-violet-900/30">
                        <i class="fa-solid fa-server text-white text-base"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-bold tracking-widest text-teal-400 mb-0.5">/// NODES</p>
                        <h2 class="text-base font-bold text-white group-hover:text-blue-200 transition leading-tight">
                            Server Rooms
                        </h2>
                        <p class="text-xs text-gray-500 truncate">{{ $totalRooms }} ruangan terdaftar</p>
                    </div>
                    <div class="flex-shrink-0 flex items-center gap-4 md:gap-6 pr-2">
                        <div class="text-center hidden sm:block">
                            <p class="text-lg font-bold text-white leading-none">{{ $totalRooms }}</p>
                            <p class="text-[10px] text-gray-500 font-semibold tracking-wide mt-1">TOTAL</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-green-400 leading-none">{{ $onlineRooms }}</p>
                            <p class="text-[10px] text-green-600 font-semibold tracking-wide mt-1">ONLINE</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-red-400 leading-none">{{ $offlineRooms }}</p>
                            <p class="text-[10px] text-red-600 font-semibold tracking-wide mt-1">OFFLINE</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 w-8 h-8 rounded-xl bg-white/6 group-hover:bg-blue-600
                                transition-all flex items-center justify-center ml-1">
                        <i class="fa-solid fa-arrow-right text-white text-xs"></i>
                    </div>
                </a>

            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
const roomNames = @json($rooms->pluck('name')->map(fn($n) => str_replace('server ', 'srv ', $n)));
const roomTemps = @json($rooms->pluck('temperature')->map(fn($t) => is_null($t) ? null : (float)$t)->values());
const ctx = document.getElementById('tempChart');

function tempColor(t) {
    if (t === null || isNaN(Number(t))) return 'rgba(100,116,139,0.6)';
    if (t > 30) return 'rgba(239,68,68,0.8)';
    if (t > 25) return 'rgba(250,204,21,0.8)';
    return 'rgba(59,130,246,0.8)';
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
                    ctx.fillStyle = '#fff';
                    ctx.font = `bold ${window.innerWidth < 768 ? 9 : 11}px Inter`;
                    ctx.textAlign = 'center';
                    ctx.fillText(v + '°C', bar.x, bar.y - 5);
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
    if (notifEnabled && Notification.permission === 'granted') {
        btn.classList.replace('text-gray-500', 'text-amber-400');
        btn.querySelector('i').className = 'fa-solid fa-bell text-sm';
        btn.title = 'Notifikasi aktif — klik untuk nonaktifkan';
    } else {
        btn.classList.replace('text-amber-400', 'text-gray-500');
        btn.querySelector('i').className = 'fa-regular fa-bell text-sm';
        btn.title = 'Aktifkan notifikasi suhu kritis';
    }
}

function toggleNotifications() {
    if (!('Notification' in window)) { alert('Browser tidak mendukung notifikasi.'); return; }
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
        if (perm === 'denied') alert('Izin notifikasi ditolak. Aktifkan di pengaturan browser.');
    });
}

function sendTempNotification(roomName, temp) {
    if (!notifEnabled || Notification.permission !== 'granted') return;
    const now = Date.now();
    if (notifCooldown[roomName] && now - notifCooldown[roomName] < 5 * 60 * 1000) return;
    notifCooldown[roomName] = now;
    new Notification('⚠️ Suhu Kritis!', {
        body: `${roomName}: ${temp}°C — segera periksa ruangan`,
        icon: '/images/wallpaper.jpeg',
        tag:  'temp-' + roomName,
    });
}

let tempChart;
function initChart() {
    if (!ctx) return;
    tempChart = new Chart(ctx, {
        plugins: [valueLabelPlugin],
        data: {
            labels: roomNames,
            datasets: [
                { type: 'bar', label: 'Suhu (°C)', data: roomTemps,
                  backgroundColor: roomTemps.map(tempColor), borderRadius: 8,
                  barPercentage: 0.7, categoryPercentage: 0.8 },
                { type: 'line', label: 'Tren', data: roomTemps,
                  borderColor: 'rgba(255,255,255,0.3)', backgroundColor: 'transparent',
                  tension: 0.4, pointBackgroundColor: '#fff', pointRadius: 3, borderWidth: 1.5 }
            ]
        },
        options: {
            maintainAspectRatio: false, responsive: true,
            plugins: {
                legend: { labels: { color: '#64748b', font: { family: 'Inter', size: 11 } } },
                tooltip: {
                    backgroundColor: 'rgba(12,22,40,0.95)',
                    titleColor: '#fff', bodyColor: '#94a3b8',
                    borderColor: 'rgba(59,130,246,0.4)', borderWidth: 1,
                    padding: 10, cornerRadius: 10,
                    callbacks: { label: c => ` ${c.parsed.y}°C` }
                }
            },
            scales: {
                x: { ticks: { color: '#64748b', maxRotation: 0, font: { size: 10 } }, grid: { display: false } },
                y: { suggestedMin: 20, suggestedMax: 40,
                     ticks: { color: '#64748b', font: { size: 10 }, callback: v => v + '°C' },
                     grid: { color: 'rgba(255,255,255,0.05)' } }
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

            const tsEl = document.getElementById('chartLastUpdated');
            if (tsEl) tsEl.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');

            const critical = data.filter(r => { const t = parseFloat(r.temp); return !isNaN(t) && t > 30; });
            const banner   = document.getElementById('tempAlertBanner');
            const alertMsg = document.getElementById('tempAlertRooms');
            if (banner && alertMsg) {
                if (critical.length > 0) {
                    alertMsg.textContent = 'Suhu kritis: ' + critical.map(r => `${r.name} (${parseFloat(r.temp).toFixed(1)}°C)`).join(', ');
                    banner.style.display = 'flex';
                } else {
                    banner.style.display = 'none';
                }
            }
            critical.forEach(r => sendTempNotification(r.name, parseFloat(r.temp).toFixed(1)));
        })
        .catch(() => {});
}

setInterval(refreshTemperature, 5000);

function setSystemStatus(online) {
    const el = document.getElementById('systemStatus');
    if (!el) return;
    el.className = `flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-all ${online ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400'}`;
    el.innerHTML = `<span class="w-1.5 h-1.5 ${online ? 'bg-green-400 animate-pulse' : 'bg-gray-400'} rounded-full"></span><span>${online ? 'Online' : 'Offline'}</span>`;
}
window.addEventListener('online',  () => setSystemStatus(true));
window.addEventListener('offline', () => setSystemStatus(false));

document.addEventListener('DOMContentLoaded', () => {
    initChart();
    setSystemStatus(navigator.onLine);
    updateNotifButton();
    setTimeout(refreshTemperature, 400);
});
</script>
@include('components.sidebar-scripts')
</body>
</html>
