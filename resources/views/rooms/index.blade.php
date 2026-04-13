<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== SIDEBAR ===== */

        .sidebar {
            transition: transform 0.3s ease;
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

        /* ===== CONTENT SHIFT ===== */

        .main-content {
            margin-left: 256px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        /* ===== ROOM CARD ===== */

        .room-card {
            background: white;
            border-radius: 20px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
            backdrop-filter: blur(6px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.25s ease, transform 0.2s ease;
            min-height: 220px;
        }

        .room-card:hover {
            transform: translateY(-4px) scale(1.01);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .room-card:active {
            transform: scale(0.98);
        }

        /* ===== MODAL ===== */

        .modal-bg {
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(4px);
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

        button:active {
            transform: scale(0.97);
        }

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

        .room-card {
            height: 100%;
        }
    </style>

</head>

<body class="bg-gray-50">

    <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

    <!-- SIDEBAR -->
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
            @auth
                <li>
                    <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms</span>
                        </a>
                    </li>
                @endif

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
            </ul>

            <!-- PROFILE PINDAH KE BAWAH -->
            @auth
                <div class="absolute bottom-6 left-6 right-6">

                    <!-- MODE NORMAL -->
                    <div class="profile-full">
                        <button class="w-full flex items-center gap-3 px-3 py-2">

                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-bold text-xs md:text-sm">
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
        @endauth
    </div>

    <!-- MAIN -->
    <div class="main-content min-h-screen flex flex-col">
        <header class="sticky top-0 bg-white px-4 md:px-6 py-3 md:py-4 flex items-center justify-between shadow-sm">
            <!-- KIRI -->
            <div class="flex items-center gap-3 md:gap-6">
                <button class="lg:hidden text-sm md:text-lg text-gray-600" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <h1 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold leading-tight text-gray-800">
                    Room Management
                </h1>
            </div>

            @auth
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <button onclick="openModal()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 md:px-4 md:py-2.5 text-sm rounded-lg shadow flex items-center gap-1">
                        + Add Room
                    </button>
                @endif
            @endauth
        </header>

        <!-- CONTENT -->
        <div class="w-full max-w-7xl mx-auto p-4 md:p-8">

            <!-- ROOM GRID -->
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6 justify-items-stretch">
                @foreach ($rooms as $room)
                    <div
                        class="room-card border {{ $room->device_status == 'online' ? 'border-green-200' : 'border-red-200' }}">
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
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 mt-1">
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

                            <i class="fa-solid fa-server text-gray-400 text-base md:text-lg"></i>

                        </div>

                        <p class="text-gray-500 text-sm mb-4">
                            Total : {{ $room->acUnits->count() }} units
                        </p>

                        @if ($room->temperature)
                            <div
                                class="bg-blue-50 text-blue-700 p-2.5 md:p-3 rounded mb-3 text-sm flex justify-between">
                                <span>🌡️ Temperature</span>
                                <span class="font-semibold">{{ $room->temperature }} °C</span>
                            </div>
                        @endif

                        <div class="bg-green-50 text-green-700 p-3 rounded-lg mb-2 flex justify-between text-sm">
                            <span>Active Units</span>
                            <span class="font-semibold">
                                {{ $room->acUnits->filter(function ($ac) {
                                        return $ac->status && $ac->status->power == 'ON';
                                    })->count() }}
                            </span>
                        </div>

                        <div class="bg-gray-100 text-gray-600 p-3 rounded-lg mb-4 flex justify-between text-sm">
                            <span>Inactive Units</span>
                            <span class="font-semibold">
                                {{ $room->acUnits->filter(function ($ac) {
                                        return !$ac->status || $ac->status->power == 'OFF';
                                    })->count() }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-2 mt-auto pt-3">
                            <a href="/rooms/{{ $room->id }}/ac"
                                class="flex-1 text-center bg-gray-900 text-white py-2 rounded-lg hover:bg-black">

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

                <div class="bg-white p-5 sm:p-8 rounded-2xl w-[90%] sm:w-96 shadow-lg">

                    <h2 class="text-xl font-bold mb-5">
                        Add New Room
                    </h2>

                    <form method="POST" action="/rooms">
                        @csrf

                        <!-- ROOM NAME -->
                        <input type="text" name="name" placeholder="Room Name"
                            class="border p-3 w-full mb-3 rounded-lg" required>

                        <!-- TAMBAHAN WAJIB -->
                        <input type="text" name="device_id" placeholder="ESP ID (contoh: esp32_01)"
                            class="border p-3 w-full mb-4 rounded-lg" required>

                        <button class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2 rounded-lg">
                            Create Room
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <script>
        function toggleSidebar() {
            let sidebar = document.getElementById("sidebar");
            let overlay = document.getElementById("overlay");

            sidebar.classList.toggle("open");
            overlay.classList.toggle("hidden");
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

        function openModal() {
            document.getElementById("modal").classList.remove("hidden")
        }
    </script>

</body>

</html>
