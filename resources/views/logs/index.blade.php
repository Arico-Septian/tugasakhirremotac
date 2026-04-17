<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>

    <link href="/css/app.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 256px;
            transition: all 0.3s ease;
        }

        .sidebar.close {
            width: 80px;
        }

        .sidebar.close .menu-text,
        .sidebar.close h2 span {
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
        .card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* ❌ HAPUS efek zoom klik */
        .card:active {
            transform: none;
        }

        /* ===== TABLE ===== */
        tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        tbody tr:active {
            background: #eef2ff;
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
    </style>
</head>

<body class="custom-bg">
    <div id="overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden z-40"></div>

    <!-- SIDEBAR -->
    <div id="sidebar"
        class="sidebar fixed top-0 left-0 w-64 bg-slate-900 text-white shadow-lg h-full p-6 border-r border-slate-900 z-50">
        <div class="flex justify-between items-center pb-5 mb-8 border-b">

            <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>

            <button onclick="toggleSidebar()" class="md:hidden text-gray-300">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <ul class="space-y-4">
            @auth
                <li>
                    <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                {{-- Admin + Operator --}}
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms</span>
                        </a>
                    </li>
                @endif

                {{-- Admin only --}}
                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/users" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10">
                            <i class="fa-solid fa-users"></i>
                            <span class="menu-text">User Management</span>
                        </a>
                    </li>
                @endif

                {{-- Admin only --}}
                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/logs"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->is('logs*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
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
                            <p class="text-sm font-semibold text-white">
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

        <header
            class="sticky top-0 z-40 bg-slate-900/70 backdrop-blur-md px-4 md:px-6 py-3 md:py-3.5 flex items-center justify-between shadow-sm">

            <!-- LEFT -->
            <div class="flex items-center gap-3">

                <!-- HAMBURGER -->
                <button
                    class="md:hidden text-white text-lg p-2 rounded-md hover:bg-white/10 active:opacity-80 transition"
                    onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="flex flex-col leading-tight">
                    <h1 class="text-base md:text-xl font-bold text-white">
                        Activity Log
                    </h1>
                    <p class="text-sm text-blue-200 font-medium">
                        System & User Activity Monitoring
                    </p>
                </div>
            </div>
        </header>

        <div class="pt-2 px-4 md:px-6 pb-4 md:pb-8">

            <!-- STATS -->
            <div class="card mb-6 flex items-center justify-between px-6 py-5">

                <div>
                    <p class="text-gray-300 text-sm mb-1">
                        Total Activity
                    </p>

                    <h2 class="text-4xl font-bold text-white">
                        {{ $logs->count() }}
                    </h2>
                </div>

                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-500/20 text-blue-300">
                    <i class="fa-solid fa-clock text-lg"></i>
                </div>

            </div>

            <!-- TABLE -->
            <div class="card">

                <!-- 📱 MOBILE -->
                <div class="block md:hidden space-y-4 px-2">

                    @foreach ($logs as $log)
                        <div
                            class="bg-slate-800/70 border border-white/10 rounded-xl p-4 space-y-3 shadow-sm hover:shadow-md transition w-full max-w-sm mx-auto">

                            <div class="text-sm font-semibold text-white">
                                {{ $log->user->name ?? '-' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-300">
                                Room: {{ $log->room }} | AC: {{ $log->ac }}
                            </div>

                            <div class="mt-2">
                                @if ($log->activity == 'add_room')
                                    <span class="bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded text-xs">ADD
                                        ROOM</span>
                                @elseif($log->activity == 'delete_room')
                                    <span class="bg-red-500/20 text-red-300 px-2 py-1 rounded text-xs">DELETE
                                        ROOM</span>
                                @elseif($log->activity == 'add_ac')
                                    <span class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded text-xs">ADD AC</span>
                                @elseif($log->activity == 'delete_ac')
                                    <span class="bg-orange-500/20 text-orange-300 px-2 py-1 rounded text-xs">DELETE
                                        AC</span>
                                @elseif($log->activity == 'on')
                                    <span class="bg-green-500/20 text-green-300 px-2 py-1 rounded text-xs">ON</span>
                                @elseif($log->activity == 'off')
                                    <span class="bg-gray-500/20 text-gray-300 px-2 py-1 rounded text-xs">OFF</span>
                                @elseif($log->activity == 'mode')
                                    <span class="bg-cyan-500/20 text-cyan-300 px-2 py-1 rounded text-xs">MODE</span>
                                @elseif($log->activity == 'set_timer')
                                    <span class="bg-yellow-500/20 text-yellow-300 px-2 py-1 rounded text-xs">SET
                                        TIMER</span>
                                @else
                                    <span class="bg-purple-500/20 text-purple-300 px-2 py-1 rounded text-xs">
                                        {{ strtoupper($log->activity) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 text-xs text-gray-300">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </div>

                        </div>
                    @endforeach

                </div>

                <!-- 💻 DESKTOP -->
                <div class="hidden md:block overflow-x-auto">

                    <table class="w-full text-xs md:text-sm">

                        <thead class="border-b border-white/10 bg-white/5">
                            <tr class="text-left text-gray-300">
                                <th class="p-3">User</th>
                                <th class="p-3">Room</th>
                                <th class="p-3">AC</th>
                                <th class="p-3">Activity</th>
                                <th class="p-3">Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($logs as $log)
                                <tr class="border-b hover:bg-white/5 transition">

                                    <td class="p-3">
                                        {{ $log->user->name ?? '-' }}
                                    </td>

                                    <td class="p-3">
                                        {{ $log->room }}
                                    </td>

                                    <td class="p-3">
                                        {{ $log->ac }}
                                    </td>

                                    <td class="p-3">

                                        @if ($log->activity == 'add_room')
                                            <span
                                                class="bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded text-xs">ADD
                                                ROOM</span>
                                        @elseif($log->activity == 'delete_room')
                                            <span class="bg-red-500/20 text-red-300 px-2 py-1 rounded text-xs">DELETE
                                                ROOM</span>
                                        @elseif($log->activity == 'add_ac')
                                            <span class="bg-blue-500/20 text-blue-300 px-2 py-1 rounded text-xs">ADD
                                                AC</span>
                                        @elseif($log->activity == 'delete_ac')
                                            <span
                                                class="bg-orange-500/20 text-orange-300 px-2 py-1 rounded text-xs">DELETE
                                                AC</span>
                                        @elseif($log->activity == 'on')
                                            <span
                                                class="bg-green-500/20 text-green-300 px-2 py-1 rounded text-xs">ON</span>
                                        @elseif($log->activity == 'off')
                                            <span
                                                class="bg-gray-500/20 text-gray-300 px-2 py-1 rounded text-xs">OFF</span>
                                        @elseif($log->activity == 'mode')
                                            <span
                                                class="bg-cyan-500/20 text-cyan-300 px-2 py-1 rounded text-xs">MODE</span>
                                        @elseif($log->activity == 'set_timer')
                                            <span
                                                class="bg-yellow-500/20 text-yellow-300 px-2 py-1 rounded text-xs">SET
                                                TIMER</span>
                                        @else
                                            <span class="bg-purple-500/20 text-purple-300 px-2 py-1 rounded text-xs">
                                                {{ strtoupper($log->activity) }}
                                            </span>
                                        @endif

                                    </td>

                                    <td class="p-3 whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y H:i') }}
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                </div>

                <!-- PAGINATION -->
                <div class="flex justify-between items-center mt-6 flex-wrap gap-3">

                    <!-- INFO -->
                    <p class="text-sm text-gray-400">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} results
                    </p>

                    <!-- PAGE NUMBERS -->
                    <div class="flex items-center gap-2">

                        <!-- PREV -->
                        @if ($logs->onFirstPage())
                            <span class="px-3 py-2 bg-white/10 text-gray-400 rounded-lg text-sm">«</span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}"
                                class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm">
                                «
                            </a>
                        @endif

                        <!-- NEXT -->
                        @if ($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}"
                                class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm">
                                »
                            </a>
                        @else
                            <span class="px-3 py-2 bg-white/10 text-gray-400 rounded-lg text-sm">»</span>
                        @endif

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
            </script>

</body>

</html>
