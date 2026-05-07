<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status AC — {{ ucwords($room->name) }}</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="/js/chart.umd.js"></script>
    @include('components.sidebar-styles')
    <style>
        .ac-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 16px;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            display: flex; flex-direction: column; gap: 8px;
        }
        .ac-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }
        .ac-stat {
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 10px;
            font-size: 12px;
        }
        .ac-stat-label { display: flex; align-items: center; gap: 6px; color: #64748b; }
        .ac-stat-value { font-weight: 700; font-size: 12px; }
        .toast {
            position: fixed; bottom: 80px; right: 20px;
            padding: 10px 20px; border-radius: 10px;
            color: white; font-size: 13px; font-weight: 500;
            z-index: 1000; animation: slideIn 0.3s ease;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        .toast.success { background: #22c55e; }
        .toast.error   { background: #ef4444; }
        .toast.info    { background: #3b82f6; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        @media (max-width: 640px) { .ac-card { padding: 12px; } }
        .hist-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 20px;
            backdrop-filter: blur(10px);
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
                <a href="{{ route('rooms.overview') }}"
                    class="hidden lg:flex w-8 h-8 items-center justify-center rounded-xl hover:bg-white/8 text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <button onclick="window.history.back()"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-gray-400 transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">{{ ucwords($room->name) }}</h1>
                    <p class="text-xs text-blue-300 font-medium hidden sm:block">Status AC System</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if (($room->device_status ?? 'offline') === 'online')
                    <div class="flex items-center gap-1.5 bg-green-500/10 text-green-400 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="hidden sm:inline">ESP</span> Online
                    </div>
                @else
                    <div class="flex items-center gap-1.5 bg-red-500/10 text-red-400 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                        <span class="hidden sm:inline">ESP</span> Offline
                    </div>
                @endif
                @if ($room->temperature !== null)
                    <div class="hidden sm:flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full
                        {{ $room->temperature > 30 ? 'bg-red-500/15 text-red-300' : ($room->temperature > 25 ? 'bg-yellow-500/15 text-yellow-300' : 'bg-blue-500/15 text-blue-300') }}">
                        <i class="fa-solid fa-temperature-half"></i>
                        {{ $room->temperature }}°C
                    </div>
                @endif
            </div>
        </header>

        <!-- PAGE BODY -->
        <div class="page-body">
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-5 space-y-4">
                @if ($acs->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                        @foreach ($acs as $ac)
                        <div class="ac-card">
                            <!-- Header -->
                            <div class="flex items-center justify-between mb-1">
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">AC {{ $ac->ac_number }}</p>
                                    <p class="text-sm font-bold text-white">{{ $ac->name ?: $ac->brand }}</p>
                                </div>
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 flex items-center justify-center">
                                    <i class="fa-solid fa-snowflake text-blue-400 text-xs"></i>
                                </div>
                            </div>

                            <!-- Power -->
                            <div class="ac-stat bg-green-500/8">
                                <span class="ac-stat-label"><i class="fa-solid fa-power-off text-green-400"></i>Power</span>
                                <span id="power-{{ $ac->id }}" class="ac-stat-value text-green-400">{{ $ac->status?->power ?? 'OFF' }}</span>
                            </div>
                            <!-- Temp -->
                            <div class="ac-stat bg-blue-500/8">
                                <span class="ac-stat-label"><i class="fa-solid fa-temperature-half text-blue-400"></i>Suhu</span>
                                <span id="temp-{{ $ac->id }}" class="ac-stat-value text-blue-400">{{ $ac->status?->set_temperature ?? 24 }}°C</span>
                            </div>
                            <!-- Mode -->
                            <div class="ac-stat bg-purple-500/8">
                                <span class="ac-stat-label"><i class="fa-solid fa-fan text-purple-400"></i>Mode</span>
                                <span id="mode-{{ $ac->id }}" class="ac-stat-value text-purple-400">{{ strtoupper($ac->status?->mode ?? 'AUTO') }}</span>
                            </div>
                            <!-- Fan -->
                            <div class="ac-stat bg-cyan-500/8">
                                <span class="ac-stat-label"><i class="fa-solid fa-wind text-cyan-400"></i>Fan</span>
                                <span id="fan-{{ $ac->id }}" class="ac-stat-value text-cyan-400">{{ strtoupper($ac->status?->fan_speed ?? 'AUTO') }}</span>
                            </div>
                            <!-- Swing -->
                            <div class="ac-stat bg-indigo-500/8">
                                <span class="ac-stat-label"><i class="fa-solid fa-arrows-up-down text-indigo-400"></i>Swing</span>
                                <span id="swing-{{ $ac->id }}" class="ac-stat-value text-indigo-400">{{ strtoupper($ac->status?->swing ?? 'OFF') }}</span>
                            </div>
                            <!-- Timer -->
                            @if ($ac->timer_on || $ac->timer_off)
                            <div class="ac-stat bg-amber-500/8">
                                <span class="ac-stat-label"><i class="fa-solid fa-clock text-amber-400"></i>Timer</span>
                                <span id="timer-{{ $ac->id }}" class="ac-stat-value text-amber-300 text-right leading-tight">
                                    @if ($ac->timer_on)<div>ON {{ \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') }}</div>@endif
                                    @if ($ac->timer_off)<div>OFF {{ \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') }}</div>@endif
                                </span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-20 text-gray-500">
                        <i class="fa-solid fa-snowflake text-5xl mb-4 opacity-20"></i>
                        <p class="text-lg font-medium text-white">Belum ada AC unit</p>
                        <p class="text-sm mt-1">Tambahkan AC unit pada halaman manajemen ruangan</p>
                    </div>
                @endif

                <!-- Temperature History Chart -->
                <div class="hist-card">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Histori Suhu Ruangan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">24 jam terakhir (rata-rata per jam)</p>
                        </div>
                        <span id="histUpdated" class="text-xs text-gray-600">--</span>
                    </div>
                    <div style="height:220px;">
                        <canvas id="tempHistChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
function showToast(msg, type = 'info') {
    document.querySelector('.toast')?.remove();
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.animation = 'slideOut 0.3s ease'; setTimeout(() => t.remove(), 300); }, 3000);
}

function updateElement(id, val) {
    const el = document.getElementById(id);
    if (el && el.innerText.trim() !== String(val).trim()) {
        el.style.color = '#60a5fa';
        el.style.transform = 'scale(1.05)';
        el.innerText = val;
        setTimeout(() => { el.style.color = ''; el.style.transform = ''; }, 500);
    }
}

function formatTime(t) {
    if (!t || t === '0000-00-00 00:00:00') return '';
    try {
        const d = new Date(t.replace(/-/g, '/'));
        return isNaN(d.getTime()) ? '' : d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
    } catch { return ''; }
}

function loadStatus() {
    fetch('/api/ac-status', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!Array.isArray(data)) return;
            data.forEach(item => {
                const ac = item.acUnit || item;
                if (!ac?.id) return;
                const id = ac.id;
                updateElement('power-' + id, item.power || 'OFF');
                updateElement('temp-'  + id, (item.set_temperature ?? 24) + '°C');
                updateElement('mode-'  + id, (item.mode || 'AUTO').toUpperCase());
                updateElement('fan-'   + id, (item.fan_speed || 'AUTO').toUpperCase());
                updateElement('swing-' + id, (item.swing || 'OFF').toUpperCase());
                const timerEl = document.getElementById('timer-' + id);
                if (timerEl) {
                    const on = formatTime(ac.timer_on || item.timer_on);
                    const off = formatTime(ac.timer_off || item.timer_off);
                    if (on || off) {
                        timerEl.innerHTML = (on ? `<div>ON ${on}</div>` : '') + (off ? `<div>OFF ${off}</div>` : '');
                    }
                }
            });
        })
        .catch(() => {});
}

setInterval(loadStatus, 5000);
document.addEventListener('DOMContentLoaded', loadStatus);

/* ===== TEMPERATURE HISTORY CHART ===== */
let histChart = null;

function tempColor(t) {
    if (t === null || isNaN(Number(t))) return 'rgba(100,116,139,0.6)';
    if (t > 30) return 'rgba(239,68,68,0.75)';
    if (t > 25) return 'rgba(250,204,21,0.75)';
    return 'rgba(59,130,246,0.75)';
}

function initHistChart(labels, temps) {
    const ctx = document.getElementById('tempHistChart');
    if (!ctx) return;
    if (histChart) histChart.destroy();
    histChart = new Chart(ctx, {
        data: {
            labels,
            datasets: [
                { type: 'bar',  label: 'Suhu (°C)', data: temps,
                  backgroundColor: temps.map(tempColor), borderRadius: 6,
                  barPercentage: 0.75, categoryPercentage: 0.85 },
                { type: 'line', label: 'Tren', data: temps,
                  borderColor: 'rgba(255,255,255,0.25)', backgroundColor: 'transparent',
                  tension: 0.4, pointBackgroundColor: '#fff', pointRadius: 3,
                  borderWidth: 1.5, pointHoverRadius: 5 },
            ],
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
                    callbacks: { label: c => ` ${c.parsed.y}°C` },
                },
            },
            scales: {
                x: { ticks: { color: '#64748b', font: { size: 10 }, maxRotation: 0 }, grid: { display: false } },
                y: { suggestedMin: 18, suggestedMax: 38,
                     ticks: { color: '#64748b', font: { size: 10 }, callback: v => v + '°C' },
                     grid: { color: 'rgba(255,255,255,0.05)' } },
            },
        },
    });
}

function loadHistChart() {
    fetch('/temperature/history/{{ $room->id }}')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                const ctx = document.getElementById('tempHistChart');
                if (ctx) {
                    const c = ctx.getContext('2d');
                    c.fillStyle = '#64748b';
                    c.font = '13px Inter';
                    c.textAlign = 'center';
                    c.fillText('Belum ada data histori suhu', ctx.width / 2, 110);
                }
                return;
            }
            initHistChart(data.map(d => d.time), data.map(d => d.temp));
            const el = document.getElementById('histUpdated');
            if (el) el.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', loadHistChart);
setInterval(loadHistChart, 60000);
</script>
@include('components.sidebar-scripts')
</body>
</html>
