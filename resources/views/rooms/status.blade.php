<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AC Status</title>

    <link href="/css/app.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 256px;
            transition: all 0.3s ease;
            z-index: 50;
        }

        .sidebar.close {
            width: 80px;
        }

        .sidebar.close .menu-text {
            display: none;
        }

        .sidebar.close ul li a {
            justify-content: center;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 256px;
            transition: none !important;
        }

        .sidebar.close+.main-content {
            margin-left: 80px;
        }

        /* ===== CARD ===== */
        .ac-card {
            background: rgba(15, 23, 42, 0.85);
            color: white;
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .ac-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        /* ===== RESPONSIVE ===== */
        @media (min-width: 768px) {
            .ac-card {
                padding: 24px;
            }
        }

        @media (max-width: 640px) {
            .ac-card {
                padding: 12px;
                border-radius: 14px;
            }
        }

        /* ===== MOBILE ===== */
        @media(max-width:900px) {

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        /* ===== HEADER ===== */
        header {
            height: 72px;
        }

        /* ===== OVERLAY ===== */
        #overlay {
            backdrop-filter: blur(2px);
        }

        /* ===== BACKGROUND ===== */
        .custom-bg {
            background:
                linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                url('/images/wallpaper.jpeg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>

</head>

<body class="custom-bg overflow-x-hidden">

    <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

    <!-- SIDEBAR -->
    <div id="sidebar"
        class="sidebar fixed top-0 left-0 w-64 bg-slate-900 text-white shadow-lg h-full p-6 border-r border-white/10 z-50">

        <!-- HEADER -->
        <div class="flex justify-between items-center pb-5 mb-8 border-b border-white/10">
            <h2 class="text-xl font-bold text-blue-500 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>

            <button onclick="toggleSidebar()" class="md:hidden text-gray-300">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- MENU -->
        <ul class="space-y-4">

            <li>
                <a href="/dashboard"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                {{ request()->is('dashboard') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                <li>
                    <a href="/rooms"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                {{ request()->is('rooms*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-server"></i>
                        <span class="menu-text">Manage Rooms & Control Ac</span>
                    </a>
                </li>
            @endif

            @if (Auth::user()->role == 'admin')
                <li>
                    <a href="/users"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                {{ request()->is('users*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-users"></i>
                        <span class="menu-text">User Management</span>
                    </a>
                </li>

                <li>
                    <a href="/logs"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                {{ request()->is('logs*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="menu-text">Activity Log</span>
                    </a>
                </li>
            @endif

        </ul>

        <!-- PROFILE -->
        <div class="absolute bottom-6 left-6 right-6">

            <div class="profile-full">
                <button class="w-full flex items-center gap-3 px-3 py-2">

                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>

                    <div class="text-left menu-text">
                        <p class="text-sm font-semibold text-white">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ Auth::user()->role }}
                        </p>
                    </div>

                    <a href="/logout" class="ml-auto text-red-500 hover:text-red-600 text-lg">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>

                </button>
            </div>

            <div class="profile-collapse hidden text-center">
                <a href="/logout" class="text-red-500 text-xl">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>

        </div>
    </div>

    <!-- MAIN -->
    <div class="main-content min-h-screen flex flex-col">

        <!-- HEADER -->
        <header
            class="sticky top-0 bg-slate-900/70 backdrop-blur-md px-4 md:px-6 py-4 md:py-5 flex justify-between items-center">
            <div class="flex items-center gap-4">

                <button onclick="toggleSidebar()" class="md:hidden text-gray-600 text-lg">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <h1 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold text-white">
                    {{ strtoupper($room->name) }} AC Units
                </h1>

            </div>

            <div class="w-6"></div>

        </header>

        <!-- CONTENT -->
        <div class="px-4 py-4 md:px-6 md:py-6">
            <div class="max-w-7xl mx-auto">

                <!-- AC GRID -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    @foreach ($acs as $ac)
                        <div class="ac-card">
                            <div class="flex justify-between items-center mb-4">

                                <h2 class="text-lg font-semibold">

                                    AC {{ $ac->ac_number }}

                                </h2>

                                <i class="fa-solid fa-snowflake text-blue-500"></i>

                            </div>

                            <p class="text-gray-300 text-sm mb-4">

                                Brand : {{ $ac->brand }}

                            </p>

                            <!-- POWER -->
                            <div
                                class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-3 flex justify-between text-sm">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid fa-power-off"></i>

                                    Power

                                </span>

                                <span id="power-{{ $ac->id }}" class="font-semibold">
                                    {{ $ac->status?->power ?? 'OFF' }}
                                </span>

                            </div>

                            <!-- TEMP -->
                            <div class="bg-blue-500/20 text-blue-400 p-3 rounded-lg mb-3 flex justify-between text-sm">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid fa-temperature-half"></i>

                                    Temperature

                                </span>

                                <span id="temp-{{ $ac->id }}" class="font-semibold">
                                    {{ $ac->status?->set_temperature ?? 24 }}°C
                                </span>

                            </div>

                            <!-- MODE -->
                            <div class="bg-purple-500/20 text-purple-400 p-3 rounded-lg flex justify-between text-sm">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid fa-fan"></i>

                                    Mode

                                </span>

                                <span id="mode-{{ $ac->id }}" class="font-semibold">
                                    {{ $ac->status?->mode ?? 'AUTO' }}
                                </span>

                            </div>

                            <!-- TIMER -->
                            <div
                                class="bg-yellow-500/20 text-yellow-300 p-3 rounded-lg mt-3 flex justify-between text-sm">

                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock"></i>
                                    Timer
                                </span>

                                <span id="timer-{{ $ac->id }}"
                                    class="font-medium text-sm leading-tight text-right">
                                    @if ($ac->timer_on || $ac->timer_off)
                                        {{ $ac->timer_on ? 'ON ' . \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') : '' }}
                                        {{ $ac->timer_off ? '| OFF ' . \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') : '' }}
                                    @else
                                        OFF
                                    @endif
                                </span>

                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById("sidebar");
            const overlay = document.getElementById("overlay");

            if (window.innerWidth <= 900) {
                sidebar.classList.toggle("open");
                overlay.classList.toggle("hidden");
            } else {
                sidebar.classList.toggle("close");
            }
        }

        document.getElementById("overlay").onclick = function() {
            document.getElementById("sidebar").classList.remove("open");
            this.classList.add("hidden");
        };

        function loadStatus() {
            fetch('/api/ac-status')
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data)) return;

                    data.forEach(ac => {
                        if (!ac.acUnit) return;

                        let id = ac.acUnit.id;

                        let powerEl = document.getElementById('power-' + id);
                        let tempEl = document.getElementById('temp-' + id);
                        let modeEl = document.getElementById('mode-' + id);
                        let timerEl = document.getElementById('timer-' + id);

                        if (powerEl && powerEl.innerText !== (ac.power ?? 'OFF')) {
                            powerEl.innerText = ac.power ?? 'OFF';
                        }

                        if (tempEl && tempEl.innerText !== ((ac.set_temperature ?? 24) + '°C')) {
                            tempEl.innerText = (ac.set_temperature ?? 24) + '°C';
                        }

                        if (modeEl && modeEl.innerText !== (ac.mode ?? 'AUTO')) {
                            modeEl.innerText = ac.mode ?? 'AUTO';
                        }

                        if (timerEl) {
                            let onTime = ac.acUnit?.timer_on;
                            let offTime = ac.acUnit?.timer_off;

                            if (onTime || offTime) {
                                let text = '';

                                if (onTime && onTime !== "0000-00-00 00:00:00") {
                                    let t = formatTime(onTime);
                                    if (t !== '--:--') {
                                        text += 'ON ' + t;
                                    }
                                }

                                if (offTime && offTime !== "0000-00-00 00:00:00") {
                                    let t = formatTime(offTime);
                                    if (t !== '--:--') {
                                        text += (text ? '\n' : '') + 'OFF ' + t;
                                    }
                                }

                                if (!text) {
                                    timerEl.innerText = 'OFF';
                                    return;
                                }

                                if (timerEl.innerText !== text) {
                                    timerEl.innerText = text;
                                    timerEl.style.whiteSpace = "pre-line";
                                }

                            } else {
                                timerEl.innerText = 'OFF';
                            }
                        }
                    });
                })
                .catch(err => {
                    console.error("Fetch error:", err);

                    document.querySelectorAll('[id^="power-"]').forEach(el => {
                        el.innerText = 'OFF';
                    });
                });
        }

        function formatTime(t) {
            if (!t) return '';

            // FIX FORMAT untuk JS
            let fixed = t.replace(' ', 'T');

            let date = new Date(fixed);

            if (isNaN(date)) return '--:--';

            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta'
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            loadStatus();
            setInterval(loadStatus, 3000);
        });
    </script>

</body>

</html>
