<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>

    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== GLOBAL ===== */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: ui-sans-serif, system-ui;
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
        }

        .sidebar.close~.main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* ===== HEADER — diam di atas ===== */
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

        /* ===== PAGE BODY — area yang scroll ===== */
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
        .card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }

        .card:active {
            transform: none;
        }

        /* ===== TABLE ===== */
        tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* ===== OVERLAY ===== */
        #overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
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

        /* ===== REMOVE TAP ZOOM ===== */
        button:active,
        a:active {
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
                    <button class="w-full flex items-center gap-3 px-3 py-2">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left menu-text">
                            <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->role }}</p>
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
        <!-- ==================== END SIDEBAR ==================== -->

        <!-- ==================== MAIN CONTENT ==================== -->
        <div class="main-content">

            <!-- HEADER — tidak ikut scroll -->
            <header class="main-header">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden text-white text-lg p-1 rounded-md hover:bg-white/10 transition"
                        onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="flex flex-col leading-tight">
                        <h1 class="text-base md:text-xl font-bold text-white">Activity Log</h1>
                        <p class="text-sm text-blue-200 font-medium">System & User Activity Monitoring</p>
                    </div>
                </div>
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY — hanya bagian ini yang scroll -->
            <div class="page-body">
                <div class="pt-2 px-4 md:px-6 pb-8">

                    @php
                        function activityBadge($activity)
                        {
                            return match ($activity) {
                                'add_room' => ['ADD ROOM', 'bg-emerald-500/20 text-emerald-300'],
                                'delete_room' => ['DELETE ROOM', 'bg-red-500/20 text-red-300'],
                                'add_ac' => ['ADD AC', 'bg-blue-500/20 text-blue-300'],
                                'delete_ac' => ['DELETE AC', 'bg-orange-500/20 text-orange-300'],
                                'on' => ['ON', 'bg-green-500/20 text-green-300'],
                                'off' => ['OFF', 'bg-gray-500/20 text-gray-300'],
                                'mode' => ['MODE', 'bg-cyan-500/20 text-cyan-300'],
                                'set_timer' => ['SET TIMER', 'bg-yellow-500/20 text-yellow-300'],
                                default => [strtoupper($activity), 'bg-purple-500/20 text-purple-300'],
                            };
                        }
                    @endphp

                    <!-- STATS BAR -->
                    <div class="card mb-6 flex items-center justify-between px-6 py-5">
                        <div>
                            <p class="text-gray-300 text-sm mb-1">Total Activity</p>
                            <h2 class="text-4xl font-bold text-white">{{ $logs->total() }}</h2>
                        </div>

                        @if (Auth::user()->role == 'admin')
                            <form action="/logs/delete-all" method="POST"
                                onsubmit="return confirm('Hapus SEMUA log?')">
                                @csrf
                                @method('DELETE')
                                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fa-solid fa-trash"></i> Delete All Logs
                                </button>
                            </form>
                        @endif
                    </div>
                    <!-- END STATS BAR -->

                    <!-- TABLE CARD -->
                    <div class="card">

                        <!-- MOBILE VIEW -->
                        <div class="block md:hidden space-y-4 px-2">
                            @foreach ($logs as $log)
                                <div
                                    class="bg-slate-800/70 border border-white/10 rounded-xl p-4 space-y-3 shadow-sm hover:shadow-md transition w-full max-w-sm mx-auto">
                                    <div class="text-sm font-semibold text-white">
                                        {{ $log->user->name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-300">
                                        Room: {{ $log->room }} | AC: {{ $log->ac }}
                                    </div>
                                    <div>
                                        @php [$label, $class] = activityBadge($log->activity); @endphp
                                        <span
                                            class="{{ $class }} px-2 py-1 rounded text-xs">{{ $label }}</span>
                                        <div class="mt-2 text-xs text-gray-300">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <!-- END MOBILE VIEW -->

                        <!-- DESKTOP VIEW -->
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
                                            <td class="p-3">{{ $log->user->name ?? '-' }}</td>
                                            <td class="p-3">{{ $log->room }}</td>
                                            <td class="p-3">{{ $log->ac }}</td>
                                            <td class="p-3">
                                                @php [$label, $class] = activityBadge($log->activity); @endphp
                                                <span
                                                    class="{{ $class }} px-2 py-1 rounded text-xs">{{ $label }}</span>
                                            </td>
                                            <td class="p-3 whitespace-nowrap">
                                                {{ $log->created_at->format('d M Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- END DESKTOP VIEW -->

                        <!-- PAGINATION -->
                        <div class="flex justify-between items-center mt-6 flex-wrap gap-3">
                            <p class="text-sm text-gray-400">
                                Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }}
                                of {{ $logs->total() }} results
                            </p>
                            <div class="flex items-center gap-2">
                                @if ($logs->onFirstPage())
                                    <span class="px-3 py-2 bg-white/10 text-gray-400 rounded-lg text-sm">«</span>
                                @else
                                    <a href="{{ $logs->previousPageUrl() }}"
                                        class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm">«</a>
                                @endif

                                @if ($logs->hasMorePages())
                                    <a href="{{ $logs->nextPageUrl() }}"
                                        class="px-3 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm">»</a>
                                @else
                                    <span class="px-3 py-2 bg-white/10 text-gray-400 rounded-lg text-sm">»</span>
                                @endif
                            </div>
                        </div>
                        <!-- END PAGINATION -->

                    </div>
                    <!-- END TABLE CARD -->

                </div>
            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->


    <!-- ===== SCRIPTS — di luar semua div, sebelum </body> ===== -->
    <script>
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
