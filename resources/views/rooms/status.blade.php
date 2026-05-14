<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Status AC — {{ $room->name }}</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="/js/chart.umd.js"></script>
    @vite('resources/js/app.js')
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

        @media (max-width: 640px) {
            .ac-card {
                padding: 12px;
            }
        }

        .temp-offline-badge {
            display: inline-flex;
            align-items: center;
            margin-left: 6px;
            padding: 3px 8px;
            background-color: var(--coral);
            color: white;
            font-size: 9px;
            font-weight: 600;
            border-radius: 4px;
            letter-spacing: 0.05em;
            vertical-align: middle;
            line-height: 1;
            white-space: nowrap;
        }

        /* Grid optimization — order matters: broader → narrower */
        /* Small phones / portrait phones (≤600px) — 2 columns */
        @media (max-width: 600px) {
            .grid[class*="grid-cols"] {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }

            .ac-card {
                padding: 12px;
            }
        }

        /* Very small phones (≤480px) — keep 2 col but tighter; switch to 1 col under 360px */
        @media (max-width: 480px) {
            .grid[class*="grid-cols"] {
                gap: 6px !important;
            }

            .ac-card .label-tag {
                font-size: 10px;
            }

            .ac-stat {
                font-size: 12px;
            }

            .ac-stat .label {
                font-size: 11px;
            }

            .ac-stat .value {
                font-size: 13px;
            }
        }

        /* Tiny phones (≤360px) — single column */
        @media (max-width: 360px) {
            .grid[class*="grid-cols"] {
                grid-template-columns: 1fr !important;
            }
        }

        /* Tablet portrait (769-1023px) — 3 columns instead of cramped 4 */
        @media (min-width: 769px) and (max-width: 1023px) {
            .grid[class*="grid-cols"] {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 12px !important;
            }
        }

        /* Touch targets optimization */
        @media (max-width: 640px) {
            .btn.btn-primary.btn-sm {
                min-height: 40px;
                padding: 8px 12px;
            }

            .btn-icon {
                width: 40px;
                height: 40px;
            }
        }

        /* Landscape mode (rotated phones) */
        @media (max-height: 600px) and (orientation: landscape) {
            .ac-card {
                padding: 10px;
            }

            .ac-stat .label {
                font-size: 10px;
            }

            .ac-stat .value {
                font-size: 12px;
            }
        }

        /* Header right cluster — wrap & shrink on tiny screens */
        .main-header {
            gap: 8px;
            flex-wrap: wrap;
        }

        @media (max-width: 480px) {
            .main-header .pill {
                padding: 4px 8px;
                font-size: 10px;
            }

            .main-header .btn-icon {
                width: 36px;
                height: 36px;
            }
        }

        @media (max-width: 360px) {
            .main-header .app-header-title h1 {
                font-size: 15px;
            }

            .main-header .app-header-title p {
                font-size: 10px;
            }
        }

        /* Ultra-wide (>1600px) — cap content width */
        @media (min-width: 1600px) {
            .app-content-inner {
                max-width: 1480px;
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (min-width: 1920px) {
            .app-content-inner {
                max-width: 1600px;
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
                    <h1>{{ $room->name }}</h1>
                    <p>AC status snapshot</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @include('components.notification-bell')
                <span id="systemStatus" class="pill pill-offline">
                    <span class="dot"></span><span>Offline</span>
                </span>
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
                                        <div class="btn-icon" style="background:var(--cyan-soft);color:var(--cyan);border-color:var(--cyan-soft-2);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
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

function setSystemStatus(online) {
    const el = document.getElementById('systemStatus');
    if (!el) return;
    el.className = 'pill ' + (online ? 'pill-online' : 'pill-offline');
    el.innerHTML = `<span class="dot"></span><span>${online ? 'Online' : 'Offline'}</span>`;
}
window.addEventListener('online',  () => setSystemStatus(true));
window.addEventListener('offline', () => setSystemStatus(false));

document.addEventListener('DOMContentLoaded', () => {
    loadStatus();
    setSystemStatus(navigator.onLine);

    // Real-time via Reverb: refresh segera saat AC/device berubah dari user/tab lain
    if (window.Echo) {
        window.Echo.channel('device-status')
            .listen('.AcStatusUpdated', () => loadStatus())
            .listen('.DeviceStatusUpdated', () => loadStatus());
    }
});
</script>
@include('components.sidebar-scripts')
</body>
</html>
