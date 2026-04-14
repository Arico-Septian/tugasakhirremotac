<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AC Status</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== SIDEBAR ===== */

        .sidebar {
            transition: all .3s ease;
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

        /* ===== CONTENT SHIFT ===== */

        .main-content {
            margin-left: 256px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        /* ===== CARD STYLE ===== */

        .ac-card {
            background: white;
            border-radius: 20px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all .25s ease;
        }

        .ac-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        @media (min-width: 768px) {
            .ac-card {
                padding: 24px;
            }
        }

        @media(max-width:900px) {
            .main-content {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                position: fixed;
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        .sidebar {
            transition: all .3s ease;
            z-index: 50;
        }

        @media(max-width:900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        @media (max-width: 640px) {
            .ac-card {
                padding: 12px;
                border-radius: 14px;
            }
        }

        #overlay {
            backdrop-filter: blur(2px);
        }

        header {
                height: 72px;
            }
    </style>

</head>

<body class="bg-gray-50">

    <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

    <!-- SIDEBAR -->
    @auth
        <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 border-r z-50">
            <div class="flex justify-between items-center pb-5 mb-8 border-b">

                <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">

                    <i class="fa-solid fa-layer-group"></i>
                    <span class="menu-text">AC System</span>

                </h2>

                <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-blue-500">
                    <i class="fa-solid fa-bars"></i>
                </button>

            </div>

            <ul class="space-y-3">

                {{-- Dashboard --}}
                <li>
                    <a href="/dashboard"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                {{-- Rooms --}}
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms</span>
                        </a>
                    </li>

                    {{-- User Management --}}
                    @if (Auth::user()->role == 'admin')
                        <li>
                            <a href="/users" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                                <i class="fa-solid fa-users"></i>
                                <span class="menu-text">User Management</span>
                            </a>
                        </li>
                    @endif

                    {{-- Admin only --}}
                    @if (Auth::user()->role == 'admin')
                        <li>
                            <a href="/logs" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span class="menu-text">Activity Log</span>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>

            <!-- PROFILE PINDAH KE BAWAH -->
            @auth
                <div class="absolute bottom-6 left-6 right-6">

                    <!-- MODE NORMAL -->
                    <div class="profile-full">
                        <button class="w-full flex items-center gap-3 px-3 py-2">

                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            <div class="text-left menu-text">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ Auth::user()->role ?? 'Administrator' }}
                                </p>
                            </div>

                            <a href="/logout" class="ml-auto text-red-500 hover:text-red-600 text-lg"
                                onclick="event.stopPropagation()">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>

                        </button>
                    </div>

                    <!-- MODE COLLAPSE -->
                    <div class="profile-collapse hidden text-center">
                        <a href="/logout" class="text-red-500 text-xl">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>

                </div>
            @endauth
        </div>
    @endauth

    <!-- MAIN -->
    <div class="main-content min-h-screen flex flex-col">

        <!-- HEADER -->
        <header class="sticky top-0 bg-white border-b px-4 md:px-6 py-4 md:py-5 flex justify-between items-center ">
            <div class="flex items-center gap-4">

                <button onclick="toggleSidebar()" class="md:hidden text-gray-600 text-lg">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <h1 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold text-gray-800">
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

                            <p class="text-gray-500 text-sm mb-4">

                                Brand : {{ $ac->brand }}

                            </p>

                            <!-- POWER -->
                            <div class="bg-green-50 text-green-700 p-3 rounded-lg mb-3 flex justify-between text-sm">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid fa-power-off"></i>

                                    Power

                                </span>

                                <span id="power-{{ $ac->id }}" class="font-semibold">
                                    {{ $ac->status?->power ?? 'OFF' }}
                                </span>

                            </div>

                            <!-- TEMP -->
                            <div class="bg-blue-50 text-blue-700 p-3 rounded-lg mb-3 flex justify-between text-sm">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid fa-temperature-half"></i>

                                    Temperature

                                </span>

                                <span id="temp-{{ $ac->id }}" class="font-semibold">
                                    {{ $ac->status?->set_temperature ?? 24 }}°C
                                </span>

                            </div>

                            <!-- MODE -->
                            <div class="bg-purple-50 text-purple-700 p-3 rounded-lg flex justify-between text-sm">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid fa-fan"></i>

                                    Mode

                                </span>

                                <span id="mode-{{ $ac->id }}" class="font-semibold">
                                    {{ $ac->status?->mode ?? 'AUTO' }}
                                </span>

                            </div>

                            <!-- TIMER -->
                            <div class="bg-yellow-50 text-yellow-700 p-3 rounded-lg mt-3 flex justify-between text-sm">

                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-clock"></i>
                                    Timer
                                </span>

                                <span id="timer-{{ $ac->id }}" class="font-semibold">
                                    @if ($ac->timer_on || $ac->timer_off)
                                        {{ $ac->timer_on ? 'ON ' . substr($ac->timer_on, 0, 5) : '' }}
                                        {{ $ac->timer_off ? '| OFF ' . substr($ac->timer_off, 0, 5) : '' }}
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
                        if (!ac.ac_unit) return;

                        let id = ac.ac_unit.id;

                        let powerEl = document.getElementById('power-' + id);
                        let tempEl = document.getElementById('temp-' + id);
                        let modeEl = document.getElementById('mode-' + id);
                        let timerEl = document.getElementById('timer-' + id);

                        if (powerEl) {
                            powerEl.innerText = ac.power ?? 'OFF';
                        }

                        if (tempEl) {
                            tempEl.innerText = (ac.set_temperature ?? 24) + '°C';
                        }

                        if (modeEl) {
                            modeEl.innerText = ac.mode ?? 'AUTO';
                        }

                        if (timerEl) {
                            let onTime = ac.ac_unit?.timer_on;
                            let offTime = ac.ac_unit?.timer_off;

                            if (onTime || offTime) {
                                let onText = onTime ? 'ON ' + formatTime(onTime) : '';
                                let offText = offTime ? 'OFF ' + formatTime(offTime) : '';

                                timerEl.innerText = [onText, offText].filter(Boolean).join(' | ');

                                timerEl.classList.add('text-green-600');
                                timerEl.classList.remove('text-gray-500');
                            } else {
                                timerEl.innerText = 'OFF';

                                timerEl.classList.add('text-gray-500');
                                timerEl.classList.remove('text-green-600');
                            }

                            timerEl.innerText = timer;
                        }
                    });
                })
                .catch(err => console.error("Fetch error:", err));
        }

        function formatTime(t) {
            return t ? t.substring(0, 5) : '';
        }

        document.addEventListener("DOMContentLoaded", function() {
            loadStatus();
            setInterval(loadStatus, 3000);
        });
    </script>

</body>

</html>
