<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Centralized AC Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        header {
            backdrop-filter: blur(10px);
            background: rgba(15, 23, 42, 0.7);
            color: white;
        }

        body {
            font-family: ui-sans-serif, system-ui;
        }

        header {
            border-bottom: none;
        }

        /* SIDEBAR */

        .sidebar.close .profile-full {
            display: none;
        }

        .sidebar.close .profile-collapse {
            display: block;
        }

        .sidebar.close .absolute .menu-text {
            display: none;
        }

        .sidebar {
            transition: transform 0.3s ease;
        }

        .sidebar.close {
            width: 80px;
        }

        .sidebar.close .menu-text {
            display: none;
        }

        .sidebar.close h2 span {
            display: none;
        }

        .sidebar.close ul li a {
            justify-content: center;
        }

        /* CONTENT SHIFT */

        .main-content {
            margin-left: 0;
            width: 100%;
        }

        /* collapse */
        .sidebar.close+.main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* MOBILE FIX */
        @media (max-width: 1024px) {
            .main-content {
                flex: 1;

            }
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: relative;
                width: 256px;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        @media (min-width: 1024px) {
            .main-content {
                margin-left: 256px;
                padding-left: 1px;
                width: calc(100% - 256px);
            }

            .sidebar.close+.main-content {
                margin-left: 80px;
                width: calc(100% - 80px);
            }
        }

        html,
        body {
            overflow-x: hidden;
        }

        .main-content {
            max-width: 100%;
        }

        /* CARD */

        .room-card {
            background: rgba(15, 23, 42, 0.8);
            color: white;
            border-radius: 20px;
            padding: 16px;
            backdrop-filter: blur(12px);
        }

        .stat-card:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        /* ROOM CARD */

        .room-card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 20px;
            padding: 16px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* ICON */

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* MOBILE */

        @media(max-width:900px) {

            .main-content {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                position: relative;
            }

            .sidebar.open {
                transform: translateX(0);
            }

        }

        @media (min-width: 1024px) {
            .main-content {
                overflow-x: hidden;
            }
        }

        html {
            scroll-behavior: smooth;
        }

        a:active {
            transform: scale(0.97);
        }

        #overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        #overlay.active {
            opacity: 1;
        }

        .stat-card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 16px;
            padding: 14px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        * {
            box-sizing: border-box;
        }

        body {
            -webkit-font-smoothing: antialiased;
        }

        .main-content {
            max-width: 1400px;
        }

        .custom-bg {
            background:
                linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                url('/images/wallpaper.jpeg') no-repeat center center fixed;
            background-size: cover;
        }
    </style>

</head>

<body class="custom-bg">
    <div class="flex">

        <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

        <div id="loader" class="fixed inset-0 bg-white flex items-center justify-center hidden z-50">
            <span class="text-blue-500 font-semibold text-lg">Loading...</span>
        </div>

        <!-- SIDEBAR -->
        <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-slate-900 text-white shadow-lg h-full p-6 z-50">
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
                @auth
                    <li>
                        <a href="/dashboard"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('dashboard') ? 'bg-blue-100 text-blue-600 font-semibold' : 'hover:bg-gray-100' }}">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>

                    {{-- Admin + Operator --}}
                    @if (in_array(Auth::user()->role, ['admin', 'operator']))
                        <li>
                            <a href="/rooms"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl  {{ request()->is('rooms*') ? 'bg-blue-100 text-blue-600 font-semibold' : 'hover:bg-gray-100' }}">
                                <i class="fa-solid fa-server"></i>
                                <span class="menu-text">Manage Rooms</span>
                            </a>
                        </li>
                    @endif

                    {{-- Admin only --}}
                    @if (Auth::user()->role == 'admin')
                        <li>
                            <a href="/users"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('users*') ? 'bg-blue-100 text-blue-600 font-semibold' : 'hover:bg-gray-100' }}">
                                <i class="fa-solid fa-users"></i>
                                <span class="menu-text">User Management</span>
                            </a>
                        </li>
                    @endif

                    {{-- Admin only --}}
                    @if (Auth::user()->role == 'admin')
                        <li>
                            <a href="/logs"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('logs*') ? 'bg-blue-100 text-blue-600 font-semibold' : 'hover:bg-gray-100' }}">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                                <span class="menu-text">Activity Log</span>
                            </a>
                        </li>
                    @endif
                @endauth
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

        <!-- MAIN -->
        <div class="main-content min-h-screen flex flex-col">

            <!-- HEADER -->
            <header
                class="sticky top-0 bg-slate-900/70 backdrop-blur-md px-4 md:px-6 py-3 flex items-center justify-between shadow-sm">
                @auth
                    <div class="flex items-center gap-3 md:gap-6">

                        <button class="text-gray-300 text-lg p-2 rounded-lg hover:bg-gray-100 transition"
                            onclick="toggleSidebar()">
                            <i class="fa-solid fa-bars"></i>
                        </button>

                        <div>

                            <h1 class="text-sm md:text-lg font-semibold text-white">
                                Centralized AC Management
                            </h1>

                            <p class="text-xs font-bold text-gray-300">
                                Server Room Cooling Control System
                            </p>

                        </div>
                    </div>

                    <div class="flex items-center gap-2 md:gap-6">
                        <div id="systemStatus"
                            class="flex items-center gap-2 bg-green-50 text-green-600 px-3 py-1 rounded-full text-xs md:text-sm font-medium">

                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>

                            Online

                        </div>
                    </div>
                @endauth
            </header>

            <!-- CONTENT -->
            <div class="w-full max-w-7xl mx-auto px-4 md:px-6 py-6">

                <!-- STATISTICS -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8 md:mb-12">
                    <div class="stat-card">
                        <div class="flex justify-between items-center">
                            <div>

                                <p class="font-semibold text-lg text-white">Rooms</p>
                                <h2 class="text-xl md:text-2xl font-bold text-white">{{ $rooms->count() }}</h2>

                            </div>

                            <div class="icon-box text-blue-500">
                                <i class="fa-solid fa-server"></i>
                            </div>

                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex justify-between items-center">
                            <div>

                                <p class="font-semibold text-lg text-white">AC Units</p>
                                <h2 class="text-2xl font-bold text-white">{{ $totalAc }}</h2>

                            </div>

                            <div class="icon-box text-indigo-500">
                                <i class="fa-solid fa-snowflake"></i>
                            </div>

                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex justify-between items-center">

                            <div>

                                <p class="font-semibold text-lg text-white">Active AC Units</p>
                                <h2 class="text-2xl font-bold">{{ $activeAc }}</h2>

                            </div>

                            <div class="icon-box text-green-500">
                                <i class="fa-solid fa-wind"></i>
                            </div>

                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex justify-between items-center">

                            <div>

                                <h3 class="font-semibold text-lg text-white">Users</p>
                                    <h2 class="text-2xl font-bold">{{ $users }}</h2>

                            </div>

                            <div class="icon-box text-purple-500">
                                <i class="fa-solid fa-users"></i>
                            </div>

                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex justify-between items-center">

                            <div>

                                <h3 class="font-semibold text-lg text-white">Users Online</p>
                                    <h2 id="usersOnlineCount" class="text-2xl font-bold">
                                        {{ $usersOnline }}
                                    </h2>
                            </div>

                            <div class="icon-box text-orange-500">
                                <i class="fa-solid fa-user-check"></i>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- SERVER ROOMS -->
                <h2 class="text-2xl font-bold mb-6 text-white drop-shadow-lg">
                    Server Rooms
                </h2>

                <div class="w-full">
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 gap-6 justify-items-start lg:justify-items-start">
                        @foreach ($rooms as $room)
                            <div class="room-card w-full">

                                <div class="flex justify-between mb-3">

                                    <h3 class="font-semibold text-lg text-white">{{ $room->name }}</h3>

                                    <i class="fa-solid fa-server text-gray-400"></i>

                                </div>

                                <p class="text-gray-500 text-sm mb-4">
                                    Total : {{ $room->acUnits->count() }} units
                                </p>

                                <!-- SUHU RUANGAN -->
                                @php
                                    $temp = $room->temperature;
                                @endphp

                                <div
                                    class="p-2.5 md:p-3 rounded mb-3 text-sm flex justify-between
                                    {{ $temp > 30 ? 'bg-red-500/20 text-red-300' : ($temp > 25 ? 'bg-yellow-500/20 text-yellow-300' : 'bg-blue-500/20 text-blue-300') }}">

                                    <span>
                                        Temp Ruangan
                                    </span>

                                    <span id="temp-{{ $room->id }}" class="font-semibold">
                                        {{ $temp ?? '--' }} °C
                                    </span>
                                </div>

                                <div
                                    class="bg-green-500/20 text-green-300 p-3 rounded-lg mb-2 flex justify-between text-sm">

                                    <span>Active Units</span>

                                    <span class="font-semibold">
                                        {{ $room->acUnits->where('status.power', 'ON')->count() }}
                                    </span>

                                </div>

                                <div
                                    class="bg-white/10 text-gray-300 p-3 rounded-lg mb-4 flex justify-between text-sm">

                                    <span>Inactive Units</span>

                                    <span class="font-semibold">
                                        {{ $room->acUnits->where('status.power', 'OFF')->count() }}
                                    </span>

                                </div>

                                <a href="/rooms/{{ $room->id }}/status">

                                    <button
                                        class="w-full py-3 md:py-2 text-sm md:text-base rounded-lg bg-blue-600 hover:bg-blue-700 text-white hover:bg-black transition active:scale-95 hover:scale-[1.02]">

                                        View Details

                                    </button>

                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleSidebar() {
                let sidebar = document.getElementById("sidebar");
                let overlay = document.getElementById("overlay");

                sidebar.classList.toggle("open");
                overlay.classList.toggle("hidden");
                overlay.classList.toggle("active");
            }

            document.getElementById("overlay").onclick = function() {
                document.getElementById("sidebar").classList.remove("open");
                this.classList.add("hidden");
            };

            document.querySelectorAll("#sidebar a").forEach(link => {
                link.addEventListener("click", () => {
                    document.getElementById("sidebar").classList.remove("open");
                    document.getElementById("overlay").classList.add("hidden");
                });
            });

            function toggleProfile() {

                document.getElementById("profileMenu").classList.toggle("hidden")

            }
            setInterval(() => {
                fetch('/users-online')
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('usersOnlineCount').innerText = data.count;
                    });
            }, 5000);

            let role = "{{ Auth::check() ? Auth::user()->role : '' }}";

            let idleTime;

            if (role === 'admin') {
                idleTime = 10 * 60 * 1000;
            } else if (role === 'operator') {
                idleTime = 5 * 60 * 1000;
            } else {
                idleTime = 2 * 60 * 1000;
            }

            let timeout;

            function resetTimer() {
                clearTimeout(timeout);

                timeout = setTimeout(() => {
                    window.location.href = "/logout";
                }, idleTime);
            }

            window.onload = resetTimer;

            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.onclick = resetTimer;
            document.onscroll = resetTimer;

            document.addEventListener("visibilitychange", function() {
                if (!document.hidden) {
                    resetTimer();
                }
            });
        </script>
    </div>
</body>

</html>
