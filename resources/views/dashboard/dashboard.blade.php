<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralized AC Dashboard</title>

    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="/js/chart.umd.js"></script>

    <style>
        /* ===== GLOBAL ===== */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== BACKGROUND ===== */
        .custom-bg {
            background:
                linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                url('/images/wallpaper.jpeg') no-repeat center center;
            background-size: cover;
        }

        /* ===== LAYOUT ===== */
        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 256px;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            z-index: 50;
            overflow: hidden;
            transition: width 0.25s ease;
        }

        .sidebar.close {
            width: 80px;
        }

        .sidebar.close .menu-text,
        .sidebar.close h2 span,
        .sidebar.close .profile-full {
            display: none;
        }

        .sidebar.close .profile-collapse {
            display: block;
        }

        .sidebar.close ul li a {
            justify-content: center;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 256px;
            width: calc(100% - 256px);
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease, width 0.3s ease;
            overflow: hidden;
        }

        .sidebar.close~.main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* ===== HEADER - diam di atas ===== */
        .main-header {
            flex-shrink: 0;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            color: white;
            z-index: 30;
        }

        /* ===== PAGE BODY - area yang scroll ===== */
        .page-body {
            flex: 1;
            overflow-y: auto;
            scroll-behavior: smooth;
            padding-bottom: 100px;
        }

        @media (min-width: 1024px) {
            .page-body {
                padding-bottom: 0;
            }
        }

        /* ===== CARD ===== */
        .room-card,
        .stat-card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 16px;
            padding: 14px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .room-card:hover,
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* ===== ICON BOX ===== */
        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
        }

        /* ===== OVERLAY ===== */
        #overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #overlay.active {
            opacity: 1;
        }

        /* ===== MOBILE ===== */
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 256px !important;
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        /* ===== SMALL SCREEN ===== */
        @media (max-width: 768px) {
            .stat-card {
                padding: 10px;
            }

            .stat-card h2 {
                font-size: 18px;
            }

            .stat-card p {
                font-size: 13px;
            }

            h1,
            h2,
            h3 {
                line-height: 1.2;
            }
        }

        /* ===== REMOVE TAP ZOOM ===== */
        a:active,
        button:active {
            transform: none !important;
        }
    </style>
</head>

<body class="custom-bg">

    <!-- ==================== LAYOUT WRAPPER ==================== -->
    <div class="layout">

        <!-- OVERLAY (mobile) -->
        <div id="overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden z-40"></div>

        <!-- ==================== SIDEBAR ==================== -->
        <div id="sidebar" class="sidebar bg-slate-900 text-white shadow-lg p-6 border-r border-white/10">

            <!-- Logo -->
            <div class="flex justify-between items-center pb-5 mb-8 border-b border-white/10">
                <h2 class="text-xl font-bold text-blue-500 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="menu-text">AC System</span>
                </h2>
                <button onclick="toggleSidebar()" class="md:hidden text-gray-300">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Menu -->
            <ul class="space-y-4">
                <li>
                    <a href="/dashboard"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->is('dashboard') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('rooms*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms & Ac Unit</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/users"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('users*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-users"></i>
                            <span class="menu-text">User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="/logs"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('logs*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span class="menu-text">Activity Log</span>
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Profile -->
            <div class="absolute bottom-6 left-6 right-6">
                <div class="profile-full">
                    <div class="w-full flex items-center gap-3 px-3 py-2">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left menu-text">
                            <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->role }}</p>
                        </div>
                        <!-- PERBAIKAN: Form logout dengan POST -->
                        <form action="/logout" method="POST" class="ml-auto">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-600 text-lg">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="profile-collapse hidden text-center">
                    <form action="/logout" method="POST">
                        @csrf
                        <button class="text-red-500 text-xl">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
        <!-- ==================== END SIDEBAR ==================== -->

        <!-- ==================== MAIN CONTENT ==================== -->
        <div class="main-content">

            <!-- HEADER -->
            <header class="main-header">
                @auth
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden text-gray-300 text-lg p-1 rounded-lg hover:bg-white/10 transition"
                            onclick="toggleSidebar()">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <div>
                            <h1 class="text-base md:text-xl font-bold text-white leading-tight">
                                Centralized AC Management
                            </h1>
                            <p class="text-sm text-blue-200 font-medium">
                                Server Room Cooling Control System
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 md:gap-6">
                        <div id="systemStatus"
                            class="flex items-center gap-2 bg-gray-500/10 text-gray-400 px-3 py-1 rounded-full text-sm font-medium">
                            <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                            Offline
                        </div>
                    </div>
                @endauth
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY -->
            <div class="page-body">
                <div class="w-full max-w-7xl mx-auto px-4 md:px-6 py-6">

                    <!-- STATISTICS -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8 md:mb-12">

                        <div class="stat-card">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs md:text-sm text-gray-300 font-bold">Rooms</p>
                                    <h2 class="text-lg md:text-2xl font-bold text-white leading-tight">
                                        {{ $rooms->count() }}
                                    </h2>
                                </div>
                                <div class="icon-box text-blue-500">
                                    <i class="fa-solid fa-server"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs md:text-sm text-gray-300 font-bold">AC Units</p>
                                    <h2 class="text-lg md:text-2xl font-bold text-white leading-tight">
                                        {{ $totalAc }}
                                    </h2>
                                </div>
                                <div class="icon-box text-indigo-500">
                                    <i class="fa-solid fa-snowflake"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs md:text-sm text-gray-300 font-bold">AC Active</p>
                                    <h2 class="text-lg md:text-2xl font-bold text-white leading-tight">
                                        {{ $activeAc }}
                                    </h2>
                                </div>
                                <div class="icon-box text-green-500">
                                    <i class="fa-solid fa-power-off"></i>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-xs md:text-sm text-gray-300 font-bold">AC Nonactive</p>
                                    <h2 class="text-lg md:text-2xl font-bold text-white leading-tight">
                                        {{ $inactiveAc }}
                                    </h2>
                                </div>
                                <div class="icon-box text-gray-400">
                                    <i class="fa-solid fa-plug-circle-xmark"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- END STATISTICS -->

                    <!-- TEMPERATURE CHART -->
                    <div class="bg-slate-900/70 rounded-xl p-3 md:p-4 max-w-3xl mx-auto">
                        <h2 class="text-lg font-semibold text-white mb-4">Room Temperature Overview</h2>
                        <div style="height: 300px;">
                            <canvas id="tempChart"></canvas>
                        </div>
                    </div>
                    <!-- END TEMPERATURE CHART -->

                    <!-- SERVER ROOMS -->
                    <h2 class="text-xl md:text-2xl font-bold text-white text-center mt-8 mb-6">
                        Server Rooms
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2">
                        @foreach ($rooms as $room)
                            <div class="room-card w-full">

                                <div class="flex justify-between mb-3">
                                    <h3 class="font-semibold text-sm md:text-lg text-white leading-tight">
                                        {{ ucfirst($room->name) }}
                                    </h3>
                                    <i class="fa-solid fa-server text-gray-400"></i>
                                </div>

                                <p class="text-gray-400 -mt-1 text-sm">
                                    Total : {{ $room->acUnits->count() }} units
                                </p>

                                @php $temp = $room->temperature; @endphp

                                <div
                                    class="p-2.5 md:p-3 rounded mb-3 text-sm flex justify-between
                                    {{ is_null($temp) ? 'bg-white/10 text-gray-300' : ($temp > 30 ? 'bg-red-500/20 text-red-300' : ($temp > 25 ? 'bg-yellow-500/20 text-yellow-300' : 'bg-blue-500/20 text-blue-300')) }}">
                                    <span>Room Temperature</span>
                                    <span id="temp-{{ $room->id }}" class="font-semibold">
                                        {{ $temp ?? '--' }} &deg;C
                                    </span>
                                </div>

                                <div
                                    class="bg-green-500/20 text-green-300 p-3 rounded-lg mb-2 flex justify-between text-sm">
                                    <span>Active Units</span>
                                    <span class="font-semibold">
                                        {{ $room->acUnits->filter(fn($ac) => optional($ac->status)->power === 'ON')->count() }}
                                    </span>
                                </div>

                                <div
                                    class="bg-white/10 text-gray-300 p-3 rounded-lg mb-4 flex justify-between text-sm">
                                    <span>Inactive Units</span>
                                    <span class="font-semibold">
                                        {{ $room->acUnits->filter(fn($ac) => optional($ac->status)->power !== 'ON')->count() }}
                                    </span>
                                </div>

                                <a href="/rooms/{{ $room->id }}/status">
                                    <button
                                        class="w-full py-3 md:py-2 text-sm md:text-base rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition">
                                        View Details
                                    </button>
                                </a>

                            </div>
                        @endforeach
                    </div>
                    <!-- END SERVER ROOMS -->

                </div>
            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->

    <script>
        // ---- Chart ----
        const roomNames = @json($rooms->pluck('name')->map(fn($n) => str_replace('server ', 'srv ', $n)));
        const roomTemps = @json($rooms->pluck('temperature')->map(fn($t) => is_null($t) ? null : (float) $t)->values());
        const ctx = document.getElementById('tempChart');

        function tempColor(temp) {
            if (temp === null || Number.isNaN(Number(temp))) return '#64748b';
            if (temp > 30) return '#ef4444';
            if (temp > 25) return '#facc15';

            return '#3b82f6';
        }

        const valueLabelPlugin = {
            id: 'valueLabel',
            afterDatasetsDraw(chart) {
                const {
                    ctx
                } = chart;
                const isMobile = window.innerWidth < 768;

                chart.data.datasets.forEach((dataset, i) => {
                    if (dataset.type !== 'bar') return;
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const value = dataset.data[index];
                        if (Number.isFinite(value) && value > 0) {
                            ctx.save();
                            ctx.fillStyle = '#ffffff';
                            ctx.font = isMobile ? 'bold 10px "Inter"' : 'bold 12px "Inter"';
                            ctx.textAlign = 'center';
                            ctx.fillText(value + '\u00B0C', bar.x, bar.y - 6);
                            ctx.restore();
                        }
                    });
                });
            }
        };

        let tempChart;

        function initChart() {
            if (!ctx) return;

            tempChart = new Chart(ctx, {
                plugins: [valueLabelPlugin],
                data: {
                    labels: roomNames,
                    datasets: [{
                            type: 'bar',
                            label: 'Temperature (\u00B0C)',
                            data: roomTemps,
                            backgroundColor: roomTemps.map(tempColor),
                            borderRadius: 6,
                            barPercentage: 0.75,
                            categoryPercentage: 0.85
                        },
                        {
                            type: 'line',
                            label: 'Trend',
                            data: roomTemps,
                            borderColor: 'rgba(255,255,255,0.6)',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            pointBackgroundColor: '#ffffff',
                            pointRadius: 2,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#94a3b8',
                                font: {
                                    family: 'Inter',
                                    size: 11,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            titleColor: '#fff',
                            bodyColor: '#94a3b8',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            padding: 8,
                            cornerRadius: 8,
                            bodyFont: {
                                family: 'Inter',
                                size: 12
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#94a3b8',
                                maxRotation: 0,
                                minRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 5,
                                font: {
                                    family: 'Inter',
                                    size: 10,
                                    weight: '500'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            suggestedMin: 20,
                            suggestedMax: 40,
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    family: 'Inter',
                                    size: 10
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.1)'
                            }
                        }
                    }
                }
            });
        }

        // PERBAIKAN: Temperature Auto-Refresh dengan pengecekan chart
        setInterval(() => {
            if (!tempChart) return;

            fetch('/temperature')
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    if (!tempChart) return;

                    const safeData = data.map(room => {
                        const temp = parseFloat(room.temp);
                        return isNaN(temp) ? null : temp;
                    });

                    data.forEach(room => {
                        const el = document.getElementById(`temp-${room.id}`);
                        if (!el) return;

                        const temp = parseFloat(room.temp);
                        el.innerText = isNaN(temp) ? '-- \u00B0C' : `${temp} \u00B0C`;
                    });

                    tempChart.data.datasets[0].data = safeData;
                    tempChart.data.datasets[1].data = safeData;
                    tempChart.data.datasets[0].backgroundColor = safeData.map(tempColor);
                    tempChart.update();
                })
                .catch(err => console.error('Error fetching temperature:', err));
        }, 5000);
    </script>

    <script>
        // ---- Sidebar Toggle ----
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (window.innerWidth <= 1024) {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('hidden');
            } else {
                sidebar.classList.toggle('close');
            }
        }

        document.getElementById('overlay').onclick = function() {
            document.getElementById('sidebar').classList.remove('open');
            this.classList.add('hidden');
        };

        // PERBAIKAN: Idle Timeout dengan POST request
        const role = "{{ Auth::check() ? Auth::user()->role : '' }}";

        const idleTime = role === 'admin' ? 10 * 60 * 1000 :
            role === 'operator' ? 5 * 60 * 1000 :
            2 * 60 * 1000;

        let timeout;

        function resetTimer() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                // Buat form untuk logout via POST
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }, idleTime);
        }

        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onclick = resetTimer;
        document.onscroll = resetTimer;

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) resetTimer();
        });

        // ---- Network Status ----
        function setSystemStatus(isOnline) {
            const el = document.getElementById('systemStatus');
            if (!el) return;

            const wrapperClass = isOnline ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400';
            const dotClass = isOnline ? 'bg-green-500 animate-pulse' : 'bg-gray-400';
            const label = isOnline ? 'Online' : 'Offline';

            el.className = `flex items-center gap-2 ${wrapperClass} px-3 py-1 rounded-full text-sm font-medium`;
            el.innerHTML = `<span class="w-2 h-2 ${dotClass} rounded-full"></span> ${label}`;
        }

        function updateSystemStatus() {
            setSystemStatus(navigator.onLine);
        }

        window.addEventListener('online', updateSystemStatus);
        window.addEventListener('offline', updateSystemStatus);

        // ---- Init on DOM Ready ----
        document.addEventListener('DOMContentLoaded', () => {
            initChart();
            updateSystemStatus();
            resetTimer();
        });
    </script>

    <script>
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function(e) {

                if (window.innerWidth <= 1024) {
                    e.preventDefault();

                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('overlay');

                    sidebar.classList.remove('open');
                    overlay.classList.add('hidden');

                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 250);
                }
            });
        });
    </script>

</body>

</html>

