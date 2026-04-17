<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms</title>

    <link href="/css/app.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 256px;
            transition: all 0.3s ease;
            will-change: transform;
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

        /* ===== ROOM CARD ===== */
        .room-card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 20px;
            padding: 16px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            min-height: 220px;
            height: 100%;

            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* ❌ HAPUS efek zoom */
        .room-card:active {
            transform: none;
        }

        /* ===== MODAL ===== */
        .modal-bg {
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(4px);
        }

        /* ===== INPUT ===== */
        input {
            transition: all 0.2s ease;
        }

        /* ===== HEADER ===== */
        header {
            height: 72px;
        }

        /* ===== BACKGROUND ===== */
        .custom-bg {
            background:
                linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                url('/images/wallpaper.jpeg') no-repeat center center fixed;
            background-size: cover;
        }

        /* ===== REMOVE CLICK ZOOM GLOBAL ===== */
        button:active {
            transform: none;
        }

        /* ===== RESPONSIVE ===== */
        @media (min-width: 768px) {
            .room-card {
                padding: 24px;
            }
        }

        @media (max-width: 640px) {
            .room-card {
                padding: 10px;
                border-radius: 10px;
            }
        }

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
    </style>

</head>

<body class="custom-bg">

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
        <header class="sticky top-0 bg-slate-900/70 backdrop-blur-md px-6 py-4 flex items-center justify-between">
            <!-- KIRI -->
            <div class="flex items-center gap-3 md:gap-5">
                <button class="lg:hidden text-gray-300 text-lg" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="flex flex-col leading-tight">

                    <h1 class="text-base md:text-xl font-bold text-white">
                        Room Management
                    </h1>

                    <p class="text-sm text-blue-200 font-medium">
                        Control Ac Unit
                    </p>
                </div>
        </header>

        <!-- CONTENT -->
        <div class="w-full max-w-7xl mx-auto px-4 md:px-6 mt-4 mb-4">
            <div class="flex items-center gap-3">

                <!-- SEARCH -->
                <form method="GET" class="flex-1">
                    <div
                        class="flex items-center bg-white/10 border border-white/20 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">

                        <!-- ICON -->
                        <span class="px-3 text-gray-300">
                            <i class="fa fa-search"></i>
                        </span>

                        <!-- INPUT -->
                        <input name="search" value="{{ request('search') }}" type="text"
                            placeholder="Search room..." autocomplete="off"
                            class="flex-1 bg-transparent text-white px-2 py-2 outline-none placeholder-gray-300">

                        <!-- BUTTON -->
                        <button type="submit" class="px-3 py-2 text-gray-300 hover:text-white transition">
                            <i class="fa fa-search"></i>
                        </button>

                    </div>
                </form>

                @auth
                    @if (in_array(Auth::user()->role, ['admin', 'operator']))
                        <button onclick="openModal()"
                            class="h-[40px] bg-blue-600 hover:bg-blue-700 text-white px-3 rounded-lg text-sm whitespace-nowrap">
                            + Add Room
                        </button>
                    @endif
                @endauth
            </div>
        </div>

        <!-- ROOM GRID -->
        <div class="w-full max-w-7xl mx-auto px-4 md:px-6 pb-8">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6 justify-center">
                @foreach ($rooms as $room)
                    <div
                        class="room-card border {{ $room->device_status == 'online' ? 'border-green-500/30' : 'border-red-500/30' }}">
                        <div class="flex justify-between items-start mb-2">

                            <div>
                                <h2 class="text-sm sm:text-base md:text-lg font-semibold break-words">
                                    {{ $room->name }}
                                </h2>

                                @php
                                    $status = $room->device_status ?? 'offline';
                                @endphp

                                @if ($status == 'online')
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-green-400 mt-1">
                                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                        ESP Online
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 mt-1">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                        ESP Offline
                                    </span>
                                @endif

                            </div>

                            <i class="fa-solid fa-server text-white/60 text-base md:text-lg"></i>

                        </div>

                        <p class="text-gray-400 text-xs md:text-sm mb-3">
                            Total : {{ $room->acUnits->count() }} units
                        </p>

                        <div
                            class="bg-blue-500/20 text-blue-300 px-3 py-2 rounded mb-3 text-xs md:text-sm flex justify-between">
                            <span>
                                Temp Ruangan
                            </span>
                            <span id="temp-{{ $room->id }}" class="font-semibold">
                                {{ $room->temperature ?? '--' }} °C
                            </span>
                        </div>

                        <div
                            class="bg-green-500/20 text-green-300 px-3 py-2 text-xs md:text-sm rounded-lg mb-2 flex justify-between">
                            <span>Active Units</span>
                            <span class="font-semibold">
                                {{ $room->acUnits->filter(function ($ac) {
                                        return $ac->status && $ac->status->power == 'ON';
                                    })->count() }}
                            </span>
                        </div>

                        <div
                            class="bg-white/10 text-gray-300 px-3 py-2 text-xs md:text-sm rounded-lg mb-4 flex justify-between">
                            <span>Inactive Units</span>
                            <span class="font-semibold">
                                {{ $room->acUnits->filter(function ($ac) {
                                        return !$ac->status || $ac->status->power == 'OFF';
                                    })->count() }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-2 mt-auto pt-3">
                            <a href="/rooms/{{ $room->id }}/ac"
                                class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg">

                                Control Ac Units

                            </a>

                            @auth
                                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                    <form action="/rooms/{{ $room->id }}" method="POST" class="flex-1">

                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this room?')"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">

                                            Delete Room

                                        </button>

                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MODAL -->
    @auth
        @if (in_array(Auth::user()->role, ['admin', 'operator']))
            <div id="modal" class="hidden fixed inset-0 modal-bg flex items-center justify-center">

                <div class="bg-slate-900 text-white p-5 sm:p-8 rounded-2xl w-[90%] sm:w-96 shadow-lg relative">

                    <!-- CLOSE BUTTON -->
                    <button onclick="closeModal()"
                        class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-xl z-50">
                        ✕
                    </button>

                    <h2 class="text-xl font-bold mb-5">
                        Add New Room
                    </h2>

                    <form method="POST" action="/rooms">
                        @csrf

                        <!-- ROOM NAME -->
                        <input type="text" name="name" placeholder="Room Name"
                            class="bg-white/10 border border-white/20 text-white p-3 w-full mb-3 rounded-lg" required>

                        <!-- TAMBAHAN WAJIB -->
                        <input type="text" name="device_id" placeholder="ESP ID (contoh: esp32_01)"
                            class="bg-white/10 border border-white/20 text-white p-3 w-full mb-4 rounded-lg" required>

                        <button
                            class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                            Create Room
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endauth

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

        document.querySelectorAll("#sidebar a").forEach(link => {
            link.addEventListener("click", () => {

                if (typeof source !== "undefined" && source) {
                    source.close();
                }

                document.getElementById("sidebar").classList.remove("open");
                document.getElementById("overlay").classList.add("hidden");
            });
        });

        function openModal() {
            document.getElementById("modal").classList.remove("hidden")
        }

        function closeModal() {
            document.getElementById("modal").classList.add("hidden");
        }
    </script>

    <script>
        let source;
        let lastTemps = [];

        function startSSE() {

            if (source) source.close();

            source = new EventSource('/temperature-stream');

            source.onmessage = function(event) {

                const data = JSON.parse(event.data);
                const temps = data.map(r => r.temperature ?? 0);

                if (isSame(temps, lastTemps)) return;

                lastTemps = temps;

                data.forEach((r) => {
                    let el = document.getElementById('temp-' + r.id);
                    if (el) {
                        el.innerText = (r.temperature ?? '--') + " °C";
                    }
                });
            };

            source.onerror = function() {
                console.log("Reconnect SSE...");
                source.close();

                if (navigator.onLine) {
                    setTimeout(startSSE, 2000);
                }
            };
        }

        function isSame(a, b) {
            if (a.length !== b.length) return false;
            for (let i = 0; i < a.length; i++) {
                if (a[i] !== b[i]) return false;
            }
            return true;
        }

        window.addEventListener("load", startSSE);

        window.addEventListener("beforeunload", () => {
            if (source) source.close();
        });

        document.addEventListener("visibilitychange", () => {
            if (document.hidden && source) {
                source.close();
            } else if (!source || source.readyState === 2) {
                startSSE();
            }
        });
    </script>

</body>

</html>
