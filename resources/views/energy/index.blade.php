<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Energy Analytics — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="/js/chart.umd.js"></script>
    @include('components.sidebar-styles')
    <style>
        .nrg-bar {
            position: relative;
            height: 6px;
            border-radius: 999px;
            background: var(--panel-2);
            overflow: hidden;
        }
        .nrg-bar > span {
            position: absolute; left: 0; top: 0; bottom: 0;
            background: linear-gradient(90deg, var(--cyan), var(--lavender));
            border-radius: 999px;
            transition: width .35s ease;
        }
        .formula-card {
            padding: 14px 16px;
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-lg);
            display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;
        }
        .formula-card > div { display: flex; flex-direction: column; gap: 3px; }
        .formula-card .lbl { font-size: 10.5px; color: var(--ink-3); letter-spacing: 0.06em; text-transform: uppercase; font-weight: 700; }
        .formula-card .val { font-size: 14px; font-weight: 600; color: var(--ink-0); font-family: 'JetBrains Mono', monospace; }
        @media (max-width: 720px) { .formula-card { grid-template-columns: 1fr; } }
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
                <div class="app-header-title">
                    <h1>Energy Analytics</h1>
                    <p>Estimasi konsumsi listrik &amp; biaya</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @include('components.notification-bell')
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-4">

                    {{-- Period selector --}}
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="segmented">
                            <a class="seg {{ $period === 'day' ? 'active' : '' }}" href="?period=day">Hari ini</a>
                            <a class="seg {{ $period === 'week' ? 'active' : '' }}" href="?period=week">Minggu ini</a>
                            <a class="seg {{ $period === 'month' ? 'active' : '' }}" href="?period=month">Bulan ini</a>
                        </div>
                        <p class="text-xs text-mono" style="color:var(--ink-3);">
                            <i class="fa-regular fa-calendar text-[10px]"></i>
                            {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                        </p>
                    </div>

                    {{-- Stat cards --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                        <div class="stat-card acc-cyan">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Total Konsumsi</p>
                                    <p class="stat-value">{{ number_format($totals['kwh'], 1) }}<span class="text-mono" style="font-size:14px;color:var(--ink-3);"> kWh</span></p>
                                    <p class="stat-meta">{{ $periodLabel }}</p>
                                </div>
                                <div class="stat-icon"><i class="fa-solid fa-bolt"></i></div>
                            </div>
                        </div>
                        <div class="stat-card acc-lavender">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Estimasi Biaya</p>
                                    <p class="stat-value text-mono" style="font-size:22px;">{{ $currency }} {{ number_format($totals['cost'], 0, ',', '.') }}</p>
                                    <p class="stat-meta">@ {{ $currency }} {{ number_format($tariff, 0, ',', '.') }}/kWh</p>
                                </div>
                                <div class="stat-icon"><i class="fa-solid fa-wallet"></i></div>
                            </div>
                        </div>
                        <div class="stat-card acc-mint">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Total Runtime</p>
                                    <p class="stat-value">{{ number_format($totals['hours'], 1) }}<span class="text-mono" style="font-size:14px;color:var(--ink-3);"> jam</span></p>
                                    <p class="stat-meta">Akumulasi semua AC</p>
                                </div>
                                <div class="stat-icon"><i class="fa-regular fa-clock"></i></div>
                            </div>
                        </div>
                        <div class="stat-card acc-coral">
                            <span class="accent-bar"></span>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="stat-label">Power Events</p>
                                    <p class="stat-value">{{ $totals['events'] }}</p>
                                    <p class="stat-meta">Aksi nyalakan AC</p>
                                </div>
                                <div class="stat-icon"><i class="fa-solid fa-power-off"></i></div>
                            </div>
                        </div>
                    </div>

                    {{-- Chart --}}
                    <div class="panel">
                        <div class="panel-header" style="margin-bottom:14px;">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-chart-area"></i> Konsumsi</p>
                                <h3 class="panel-title">Trend kWh per {{ $period === 'day' ? 'jam' : 'hari' }}</h3>
                            </div>
                        </div>
                        <div style="height:280px;position:relative;">
                            <canvas id="energyChart"></canvas>
                        </div>
                    </div>

                    {{-- Per Room breakdown --}}
                    <div class="panel">
                        <div class="panel-header" style="margin-bottom:14px;">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-server"></i> Per Ruangan</p>
                                <h3 class="panel-title">Konsumsi berdasarkan ruangan</h3>
                            </div>
                        </div>
                        @if ($perRoomStats->isEmpty())
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-chart-pie"></i></div>
                                <p class="empty-title">Belum ada data konsumsi</p>
                                <p class="empty-sub">Data akan muncul saat ada aktivitas power on/off AC</p>
                            </div>
                        @else
                            @php $maxKwh = $perRoomStats->max('kwh') ?: 1; @endphp
                            <div class="space-y-3">
                                @foreach ($perRoomStats as $r)
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5 text-sm">
                                            <span style="color:var(--ink-0);font-weight:600;">{{ ucwords($r['room']) }}</span>
                                            <span class="text-mono" style="color:var(--ink-1);">
                                                {{ number_format($r['kwh'], 1) }} kWh
                                                <span style="color:var(--ink-3);"> · {{ $currency }} {{ number_format($r['cost'], 0, ',', '.') }}</span>
                                            </span>
                                        </div>
                                        <div class="nrg-bar">
                                            <span style="width: {{ ($r['kwh'] / $maxKwh) * 100 }}%;"></span>
                                        </div>
                                        <div class="flex items-center justify-between mt-1 text-mono" style="font-size:10.5px;color:var(--ink-4);">
                                            <span>{{ $r['ac_count'] }} unit · {{ number_format($r['hours'], 1) }} jam</span>
                                            <span>{{ number_format(($r['kwh'] / $maxKwh) * 100, 0) }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Per AC table --}}
                    <div class="tbl-wrap">
                        <div class="tbl-toolbar">
                            <p class="text-xs" style="color:var(--ink-3);">
                                <i class="fa-solid fa-table"></i>
                                Detail per AC unit
                            </p>
                        </div>
                        <div class="hidden md:block" style="overflow-x:auto;">
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th>Ruangan</th>
                                        <th>AC</th>
                                        <th class="text-right">Runtime</th>
                                        <th class="text-right">kWh</th>
                                        <th class="text-right">Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($perAcStats as $ac)
                                        <tr>
                                            <td>{{ ucwords($ac['room']) }}</td>
                                            <td>
                                                <span class="text-mono" style="color:var(--ink-3);">#{{ $ac['ac_number'] }}</span>
                                                <span style="color:var(--ink-1);">· {{ $ac['name'] }}</span>
                                            </td>
                                            <td class="num text-mono">{{ number_format($ac['hours'], 1) }} jam</td>
                                            <td class="num text-mono" style="color:var(--cyan);">{{ number_format($ac['kwh'], 2) }}</td>
                                            <td class="num text-mono" style="color:var(--lavender);">{{ $currency }} {{ number_format($ac['cost'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5">
                                            <div class="empty-state">
                                                <div class="empty-icon"><i class="fa-solid fa-bolt"></i></div>
                                                <p class="empty-title">Tidak ada data konsumsi</p>
                                            </div>
                                        </td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="md:hidden">
                            @forelse ($perAcStats as $ac)
                                <div style="padding:14px 16px;border-bottom:1px solid var(--line-soft);">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold" style="color:var(--ink-0);">
                                            {{ ucwords($ac['room']) }} · #{{ $ac['ac_number'] }}
                                        </span>
                                        <span class="text-mono" style="font-size:11.5px;color:var(--cyan);">{{ number_format($ac['kwh'], 2) }} kWh</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-mono" style="color:var(--ink-3);">
                                        <span><i class="fa-regular fa-clock text-[10px]"></i> {{ number_format($ac['hours'], 1) }} jam</span>
                                        <span style="color:var(--lavender);">{{ $currency }} {{ number_format($ac['cost'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-bolt"></i></div>
                                    <p class="empty-title">Tidak ada data konsumsi</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Formula card --}}
                    <div class="formula-card">
                        <div>
                            <span class="lbl">Power Rating</span>
                            <span class="val">{{ $powerKw }} kW / unit</span>
                        </div>
                        <div>
                            <span class="lbl">Tarif Listrik</span>
                            <span class="val">{{ $currency }} {{ number_format($tariff, 2, ',', '.') }} / kWh</span>
                        </div>
                        <div>
                            <span class="lbl">Formula</span>
                            <span class="val" style="font-size:11.5px;">jam × kW × tarif</span>
                        </div>
                    </div>
                    <p class="text-xs" style="color:var(--ink-4);text-align:center;font-style:italic;">
                        <i class="fa-solid fa-circle-info text-[10px]"></i>
                        Estimasi berdasarkan pasangan event ON/OFF dari activity log. Atur konstanta di <span class="text-mono">config/smartac.php</span>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
const ctx = document.getElementById('energyChart');
if (ctx) {
    const labels = @json($timeSeries['labels']);
    const data = @json($timeSeries['kwh']);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'kWh',
                data: data,
                backgroundColor: data.map(v => {
                    if (v > Math.max(...data) * 0.7) return 'rgba(248, 113, 113, 0.55)';
                    if (v > Math.max(...data) * 0.4) return 'rgba(251, 191, 36, 0.55)';
                    return 'rgba(77, 212, 255, 0.55)';
                }),
                borderColor: 'transparent',
                borderRadius: 6,
                barThickness: 'flex',
                maxBarThickness: 40,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#94a3b8', font: { family: 'JetBrains Mono', size: 10 } },
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { family: 'JetBrains Mono', size: 10 } },
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0c1832',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    padding: 10,
                    callbacks: {
                        label: (ctx) => `${ctx.parsed.y.toFixed(2)} kWh`,
                    }
                }
            }
        }
    });
}
</script>

@include('components.sidebar-scripts')
</body>
</html>
