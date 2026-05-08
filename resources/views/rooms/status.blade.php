<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status AC — {{ ucwords($room->name) }}</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="/js/chart.umd.js"></script>
    @include('components.sidebar-styles')
    <style>
        .ac-card {
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-xl);
            box-shadow: var(--inset-hi);
            padding: 14px;
            display: flex; flex-direction: column; gap: 8px;
            transition: var(--t-base);
        }
        .ac-card:hover { background: var(--panel-2); border-color: var(--line); transform: translateY(-1px); box-shadow: var(--shadow); }
        .ac-card .ac-stat .label i { color: var(--icon-color, var(--ink-3)); }
        .ic-power  { --icon-color: var(--mint); }
        .ic-temp   { --icon-color: var(--cyan); }
        .ic-mode   { --icon-color: var(--lavender); }
        .ic-fan    { --icon-color: var(--cyan); }
        .ic-swing  { --icon-color: var(--lavender); }
        .ic-timer  { --icon-color: var(--amber); }

        @media (max-width: 640px) { .ac-card { padding: 12px; } }
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
                <a href="{{ route('rooms.overview') }}" class="hidden lg:inline-flex btn-icon" title="Back">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </a>
                <button onclick="window.history.back()" class="lg:hidden btn-icon" title="Back">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </button>
                <div class="app-header-title">
                    <h1>{{ ucwords($room->name) }}</h1>
                    <p>AC status snapshot</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="pill {{ ($room->device_status ?? 'offline') === 'online' ? 'pill-online' : 'pill-error' }}">
                    <span class="dot"></span>
                    <span>ESP {{ ($room->device_status ?? 'offline') === 'online' ? 'Online' : 'Offline' }}</span>
                </span>
                @if ($room->temperature !== null)
                    @php $t = $room->temperature; $tcls = $t > 30 ? 'hot' : ($t > 25 ? 'warm' : 'cool'); @endphp
                    <span class="temp-chip {{ $tcls }} hidden sm:inline-flex">
                        <i class="fa-solid fa-temperature-half text-[10px]"></i>{{ $t }}°C
                    </span>
                @endif
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-4">

                    @if ($acs->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach ($acs as $ac)
                                <div class="ac-card">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="label-tag">AC {{ $ac->ac_number }}</p>
                                            <p class="text-sm font-semibold mt-0.5" style="color:var(--ink-0);">{{ $ac->name ?: $ac->brand }}</p>
                                        </div>
                                        <div class="btn-icon" style="background:var(--cyan-soft);color:var(--cyan);border-color:var(--cyan-soft-2);">
                                            <i class="fa-solid fa-snowflake text-[11px]"></i>
                                        </div>
                                    </div>

                                    <div class="ac-stat ic-power">
                                        <span class="label"><i class="fa-solid fa-power-off"></i>Power</span>
                                        <span id="power-{{ $ac->id }}" class="value" style="color:var(--mint);">{{ $ac->status?->power ?? 'OFF' }}</span>
                                    </div>
                                    <div class="ac-stat ic-temp">
                                        <span class="label"><i class="fa-solid fa-temperature-half"></i>Temp</span>
                                        <span id="temp-{{ $ac->id }}" class="value" style="color:var(--cyan);">{{ $ac->status?->set_temperature ?? 24 }}°C</span>
                                    </div>
                                    <div class="ac-stat ic-mode">
                                        <span class="label"><i class="fa-solid fa-fan"></i>Mode</span>
                                        <span id="mode-{{ $ac->id }}" class="value" style="color:var(--lavender);">{{ strtoupper($ac->status?->mode ?? 'AUTO') }}</span>
                                    </div>
                                    <div class="ac-stat ic-fan">
                                        <span class="label"><i class="fa-solid fa-wind"></i>Fan</span>
                                        <span id="fan-{{ $ac->id }}" class="value" style="color:var(--cyan);">{{ strtoupper($ac->status?->fan_speed ?? 'AUTO') }}</span>
                                    </div>
                                    <div class="ac-stat ic-swing">
                                        <span class="label"><i class="fa-solid fa-arrows-up-down"></i>Swing</span>
                                        <span id="swing-{{ $ac->id }}" class="value" style="color:var(--lavender);">{{ strtoupper($ac->status?->swing ?? 'OFF') }}</span>
                                    </div>
                                    @if ($ac->timer_on || $ac->timer_off)
                                        <div class="ac-stat ic-timer">
                                            <span class="label"><i class="fa-solid fa-clock"></i>Timer</span>
                                            <span id="timer-{{ $ac->id }}" class="value text-mono text-right" style="color:var(--amber);font-size:11px;line-height:1.3;">
                                                @if ($ac->timer_on)<div>ON {{ \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') }}</div>@endif
                                                @if ($ac->timer_off)<div>OFF {{ \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') }}</div>@endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fa-solid fa-snowflake"></i></div>
                            <p class="empty-title">Belum ada AC unit</p>
                            <p class="empty-sub">Tambahkan AC unit di halaman manajemen ruangan</p>
                        </div>
                    @endif

                    {{-- Temperature history --}}
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-chart-line"></i> Histori</p>
                                <h3 class="panel-title">Suhu Ruangan</h3>
                                <p class="panel-subtitle">24 jam terakhir · rata-rata per jam</p>
                            </div>
                            <span id="histUpdated" class="panel-meta">—</span>
                        </div>
                        <div style="height:240px;">
                            <canvas id="tempHistChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
function updateElement(id, val) {
    const el = document.getElementById(id);
    if (el && el.textContent.trim() !== String(val).trim()) {
        el.style.transition = 'color .25s, transform .25s';
        const original = el.style.color;
        el.style.color = '#4dd4ff';
        el.style.transform = 'scale(1.05)';
        el.textContent = val;
        setTimeout(() => { el.style.color = original; el.style.transform = ''; }, 460);
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
    fetch('/api/ac-status', { headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' } })
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
                    const on  = formatTime(ac.timer_on  || item.timer_on);
                    const off = formatTime(ac.timer_off || item.timer_off);
                    if (on || off) {
                        timerEl.innerHTML = (on ? `<div>ON ${on}</div>` : '') + (off ? `<div>OFF ${off}</div>` : '');
                    }
                }
            });
        }).catch(() => {});
}
setInterval(loadStatus, 5000);
document.addEventListener('DOMContentLoaded', loadStatus);

/* ===== Temperature history chart ===== */
let histChart = null;
function tempColor(t) {
    if (t === null || isNaN(Number(t))) return 'rgba(100,116,139,0.55)';
    if (t > 30) return 'rgba(251,113,133,0.85)';
    if (t > 25) return 'rgba(251,191,36,0.85)';
    return 'rgba(77,212,255,0.85)';
}
function initHistChart(labels, temps) {
    const ctx = document.getElementById('tempHistChart');
    if (!ctx) return;
    if (histChart) histChart.destroy();
    histChart = new Chart(ctx, {
        data: {
            labels,
            datasets: [
                { type: 'bar', label: 'Suhu (°C)', data: temps,
                  backgroundColor: temps.map(tempColor), borderRadius: 6,
                  barPercentage: 0.75, categoryPercentage: 0.82 },
                { type: 'line', label: 'Tren', data: temps,
                  borderColor: 'rgba(245,247,251,0.28)', backgroundColor: 'transparent',
                  tension: 0.4, pointBackgroundColor: '#f5f7fb', pointRadius: 3,
                  borderWidth: 1.5, pointHoverRadius: 5 }
            ]
        },
        options: {
            maintainAspectRatio: false, responsive: true,
            plugins: {
                legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 }, boxWidth: 10, boxHeight: 10 } },
                tooltip: {
                    backgroundColor: 'rgba(7,16,31,0.96)',
                    titleColor: '#f5f7fb', bodyColor: '#cbd5e1',
                    borderColor: 'rgba(77,212,255,0.40)', borderWidth: 1,
                    padding: 10, cornerRadius: 10, displayColors: false,
                    callbacks: { label: c => ` ${c.parsed.y}°C` }
                }
            },
            scales: {
                x: { ticks: { color: '#64748b', font: { size: 10 }, maxRotation: 0 }, grid: { display: false } },
                y: { suggestedMin: 18, suggestedMax: 38,
                     ticks: { color: '#64748b', font: { size: 10 }, callback: v => v + '°C' },
                     grid: { color: 'rgba(255,255,255,0.04)' } }
            }
        }
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
                    c.fillStyle = '#64748b'; c.font = '13px Inter'; c.textAlign = 'center';
                    c.fillText('Belum ada data histori suhu', ctx.width / 2, 110);
                }
                return;
            }
            initHistChart(data.map(d => d.time), data.map(d => d.temp));
            const el = document.getElementById('histUpdated');
            if (el) el.textContent = 'Updated ' + new Date().toLocaleTimeString('id-ID');
        }).catch(() => {});
}
document.addEventListener('DOMContentLoaded', loadHistChart);
setInterval(loadHistChart, 60000);
</script>
@include('components.sidebar-scripts')
</body>
</html>
