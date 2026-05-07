<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Overview — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="/js/chart.umd.js"></script>
    @include('components.sidebar-styles')
    <style>
        /* ===== SEARCH BAR ===== */
        .search-wrap {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            display: flex; align-items: center;
            gap: 10px; padding: 0 14px;
            transition: border-color 0.2s;
        }
        .search-wrap:focus-within {
            border-color: rgba(59,130,246,0.5);
            background: rgba(255,255,255,0.07);
        }
        .search-wrap input {
            background: transparent; border: none;
            outline: none; color: white;
            font-size: 14px; font-family: 'Inter', sans-serif;
            padding: 10px 0; flex: 1;
        }
        .search-wrap input::placeholder { color: #64748b; }

        /* ===== FILTER PILLS ===== */
        .filter-pill {
            padding: 7px 16px; border-radius: 999px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            color: #64748b; transition: all 0.2s;
            white-space: nowrap;
        }
        .filter-pill.active, .filter-pill:hover {
            background: rgba(59,130,246,0.15);
            border-color: rgba(59,130,246,0.4);
            color: #93c5fd;
        }

        /* ===== ROOM CARD ===== */
        .room-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 16px;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            display: flex; flex-direction: column; gap: 10px;
        }
        .room-card:hover {
            background: rgba(255,255,255,0.07);
            border-color: rgba(255,255,255,0.12);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }
        .room-card[data-status="online"] { border-top: 2px solid rgba(34,197,94,0.5); }
        .room-card[data-status="offline"] { border-top: 2px solid rgba(239,68,68,0.3); }

        /* ===== MODAL ===== */
        .modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(6px);
            z-index: 100; display: flex; align-items: center;
            justify-content: center; padding: 16px;
        }
        .modal-box {
            background: #0c1628;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 24px;
            width: 100%; max-width: 640px;
            max-height: 90vh; overflow-y: auto;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center; padding: 64px 16px; color: #475569;
        }

        @media (max-width: 640px) {
            .room-card { padding: 14px; }
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
                <a href="{{ route('dashboard') }}"
                    class="hidden lg:flex w-8 h-8 items-center justify-center rounded-xl hover:bg-white/8 text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Server Rooms</h1>
                    <p class="text-xs text-blue-300 font-medium hidden sm:block">
                        {{ $rooms->count() }} ruangan — AC monitoring
                    </p>
                </div>
            </div>
        </header>

        <!-- PAGE BODY -->
        <div class="page-body">
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-5">

                <!-- SEARCH & FILTER -->
                <div class="flex flex-col sm:flex-row gap-3 mb-5">
                    <div class="search-wrap flex-1">
                        <i class="fa-solid fa-magnifying-glass text-gray-500 text-sm"></i>
                        <input id="searchInput" type="text" placeholder="Cari nama ruangan..."
                            autocomplete="off">
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button class="filter-pill active" data-filter="all">Semua</button>
                        <button class="filter-pill" data-filter="online">
                            <i class="fa-solid fa-circle text-green-400 text-[9px] mr-1"></i>Online
                        </button>
                        <button class="filter-pill" data-filter="offline">
                            <i class="fa-solid fa-circle text-red-400 text-[9px] mr-1"></i>Offline
                        </button>
                    </div>
                </div>

                <!-- COUNT -->
                <p id="roomCount" class="text-xs text-gray-500 mb-4"></p>

                @if ($rooms->count() > 0)
                <div id="roomGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    @foreach ($rooms as $room)
                    @php
                        $activeCount   = $room->acUnits->filter(fn($ac) => optional($ac->status)->power === 'ON')->count();
                        $inactiveCount = $room->acUnits->filter(fn($ac) => optional($ac->status)->power !== 'ON')->count();
                        $temp          = $room->temperature;
                        $status        = $room->device_status ?? 'offline';
                        $tempClass     = $temp === null ? 'text-gray-400' : ($temp > 30 ? 'text-red-400' : ($temp > 25 ? 'text-yellow-400' : 'text-blue-400'));
                        $tempBg        = $temp === null ? 'bg-white/5' : ($temp > 30 ? 'bg-red-500/10' : ($temp > 25 ? 'bg-yellow-500/10' : 'bg-blue-500/10'));
                    @endphp
                    <div class="room-card"
                         data-name="{{ strtolower($room->name) }}"
                         data-status="{{ $status }}">

                        <!-- Header -->
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-sm text-white leading-tight">
                                {{ ucfirst($room->name) }}
                            </h3>
                            @if ($status === 'online')
                            <span class="flex items-center gap-1 flex-shrink-0">
                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                <span class="text-[10px] text-green-400 font-semibold hidden sm:inline">Online</span>
                            </span>
                            @else
                            <span class="flex items-center gap-1 flex-shrink-0">
                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                <span class="text-[10px] text-red-400 font-semibold hidden sm:inline">Offline</span>
                            </span>
                            @endif
                        </div>

                        <!-- Temperature -->
                        <div class="{{ $tempBg }} {{ $tempClass }} rounded-lg px-3 py-2 flex items-center justify-between text-xs">
                            <span class="text-gray-400">Suhu</span>
                            <span id="temp-{{ $room->id }}" class="font-bold">
                                {{ $temp !== null ? $temp.'°C' : '--' }}
                            </span>
                        </div>

                        <!-- AC Count -->
                        <div class="grid grid-cols-2 gap-1.5 text-xs">
                            <div class="bg-green-500/10 text-green-400 rounded-lg px-2 py-1.5 text-center">
                                <p class="text-[10px] text-gray-500">Aktif</p>
                                <p class="font-bold">{{ $activeCount }}</p>
                            </div>
                            <div class="bg-white/5 text-gray-400 rounded-lg px-2 py-1.5 text-center">
                                <p class="text-[10px] text-gray-500">Mati</p>
                                <p class="font-bold">{{ $inactiveCount }}</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-1.5 mt-auto pt-1">
                            <a href="/rooms/{{ $room->id }}/status" class="flex-1">
                                <button class="w-full py-2 text-xs font-semibold rounded-lg
                                    bg-blue-600 hover:bg-blue-500 text-white transition">
                                    Detail
                                </button>
                            </a>
                            <button onclick="openHistory({{ $room->id }}, '{{ ucfirst($room->name) }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg
                                       bg-white/5 hover:bg-indigo-600 text-gray-400 hover:text-white transition"
                                title="Histori suhu 24 jam">
                                <i class="fa-solid fa-chart-line text-xs"></i>
                            </button>
                        </div>

                    </div>
                    @endforeach
                </div>

                <!-- EMPTY STATE (filtered) -->
                <div id="emptyState" class="hidden empty-state">
                    <i class="fa-solid fa-magnifying-glass text-4xl mb-3 opacity-20"></i>
                    <p class="text-base font-medium text-white">Tidak ditemukan</p>
                    <p class="text-sm mt-1">Coba kata kunci atau filter lain</p>
                </div>
                @else
                <div class="empty-state">
                    <i class="fa-solid fa-server text-5xl mb-4 opacity-20"></i>
                    <p class="text-lg font-medium text-white">Belum ada ruangan</p>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- HISTORY MODAL -->
<div id="historyModal" class="modal-backdrop" style="display:none;">
    <div class="modal-box">
        <div class="flex justify-between items-start mb-5">
            <div>
                <p class="text-[10px] text-teal-400 font-bold tracking-widest mb-1">HISTORI SUHU</p>
                <h2 id="historyTitle" class="text-lg font-bold text-white">Ruangan</h2>
                <p class="text-xs text-gray-500">24 jam terakhir</p>
            </div>
            <button onclick="closeHistory()"
                class="w-8 h-8 flex items-center justify-center rounded-xl bg-white/6
                       hover:bg-red-500/20 text-gray-400 hover:text-red-300 transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        <div id="historyLoading" class="text-center py-12 text-gray-500">
            <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
            <p class="text-sm">Memuat data...</p>
        </div>
        <div id="historyEmpty" class="hidden text-center py-12 text-gray-500">
            <i class="fa-solid fa-temperature-empty text-3xl mb-2 opacity-30"></i>
            <p class="text-sm">Tidak ada data suhu dalam 24 jam terakhir</p>
        </div>
        <div id="historyChartWrap" class="hidden" style="height:260px;">
            <canvas id="historyChart"></canvas>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
// ===== SEARCH & FILTER =====
const cards       = Array.from(document.querySelectorAll('#roomGrid .room-card'));
const emptyState  = document.getElementById('emptyState');
const countEl     = document.getElementById('roomCount');
const grid        = document.getElementById('roomGrid');
let   activeFilter = 'all';

function applyFilter() {
    const q = (document.getElementById('searchInput')?.value || '').toLowerCase().trim();
    let visible = 0;
    cards.forEach(card => {
        const match = (!q || card.dataset.name.includes(q)) &&
                      (activeFilter === 'all' || card.dataset.status === activeFilter);
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    countEl.textContent = visible === cards.length
        ? `Menampilkan ${cards.length} ruangan`
        : `${visible} dari ${cards.length} ruangan`;
    if (emptyState) emptyState.classList.toggle('hidden', visible > 0);
    if (grid)       grid.classList.toggle('hidden', visible === 0);
}
document.getElementById('searchInput')?.addEventListener('input', applyFilter);
document.querySelectorAll('.filter-pill').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        activeFilter = this.dataset.filter;
        applyFilter();
    });
});
document.addEventListener('DOMContentLoaded', applyFilter);

// ===== HISTORY MODAL =====
let historyChartInstance = null;

function openHistory(roomId, roomName) {
    document.getElementById('historyTitle').textContent = roomName;
    document.getElementById('historyModal').style.display = 'flex';
    document.getElementById('historyLoading').classList.remove('hidden');
    document.getElementById('historyEmpty').classList.add('hidden');
    document.getElementById('historyChartWrap').classList.add('hidden');

    if (historyChartInstance) { historyChartInstance.destroy(); historyChartInstance = null; }

    fetch(`/temperature/history/${roomId}`)
        .then(r => r.ok ? r.json() : [])
        .then(data => {
            document.getElementById('historyLoading').classList.add('hidden');
            if (!data || data.length === 0) {
                document.getElementById('historyEmpty').classList.remove('hidden');
                return;
            }
            document.getElementById('historyChartWrap').classList.remove('hidden');
            const labels = data.map(d => d.time);
            const temps  = data.map(d => d.temp);
            const pointColor = t => t > 30 ? '#ef4444' : t > 25 ? '#facc15' : '#3b82f6';
            const ctx = document.getElementById('historyChart').getContext('2d');
            historyChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Suhu (°C)', data: temps,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.08)',
                        pointBackgroundColor: temps.map(pointColor),
                        pointRadius: 4, pointHoverRadius: 6,
                        tension: 0.4, fill: true, borderWidth: 2
                    }]
                },
                options: {
                    maintainAspectRatio: false, responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(12,22,40,0.95)',
                            titleColor: '#fff', bodyColor: '#94a3b8',
                            borderColor: 'rgba(59,130,246,0.4)', borderWidth: 1,
                            padding: 10, cornerRadius: 10,
                            callbacks: { label: c => ` ${c.parsed.y}°C` }
                        }
                    },
                    scales: {
                        x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
                        y: { suggestedMin: 18, suggestedMax: 35,
                             ticks: { color: '#64748b', font: { size: 10 }, callback: v => v + '°C' },
                             grid: { color: 'rgba(255,255,255,0.04)' } }
                    }
                }
            });
        })
        .catch(() => {
            document.getElementById('historyLoading').classList.add('hidden');
            document.getElementById('historyEmpty').classList.remove('hidden');
        });
}
function closeHistory() {
    document.getElementById('historyModal').style.display = 'none';
    if (historyChartInstance) { historyChartInstance.destroy(); historyChartInstance = null; }
}
document.getElementById('historyModal')?.addEventListener('click', e => { if (e.target === e.currentTarget) closeHistory(); });

// ===== LIVE TEMP =====
setInterval(() => {
    fetch('/temperature')
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            data.forEach(room => {
                const el = document.getElementById(`temp-${room.id}`);
                if (!el) return;
                const t = parseFloat(room.temp);
                el.textContent = isNaN(t) ? '--' : `${t}°C`;
            });
        })
        .catch(() => {});
}, 5000);
</script>
@include('components.sidebar-scripts')
</body>
</html>
