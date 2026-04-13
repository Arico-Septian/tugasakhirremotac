<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .sidebar {
            transition: all .3s ease;
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

        .main-content {
            margin-left: 256px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            -webkit-overflow-scrolling: touch;
        }

        table tr:active {
            background: #f3f4f6;
        }

        tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:active {
            background: #eef2ff;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .card:active {
            transform: scale(0.98);
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

        @media (min-width: 768px) {
            .card {
                padding: 20px;
            }
        }
    </style>
</head>

<body class="bg-gray-50">

    <!-- SIDEBAR -->
    <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 border-r z-50">
        <div class="flex justify-between items-center pb-5 mb-8 border-b">

            <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>

            <button onclick="toggleSidebar()" class="text-gray-500">
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

                {{-- Admin + Operator --}}
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms</span>
                        </a>
                    </li>
                @endif

                {{-- Admin only --}}
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
                        <a href="/logs"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold">
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

        <header class="sticky top-0 bg-white h-[64px] px-4 md:px-6 flex items-center justify-between shadow-sm">
            <div>
                <h1 class="text-base md:text-xl font-semibold text-gray-800 leading-none">Activity Log</h1>
                <p class="text-xs text-gray-400 hidden sm:block">System & User Activity Monitoring</p>
            </div>
        </header>

        <div class="pt-2 px-4 md:px-6 pb-4 md:pb-8">

            <!-- STATS -->
            <div class="card mb-4 flex justify-between items-center gap-2">
                <div>
                    <p class="text-gray-500 text-sm">Total Activity</p>
                    <h2 class="text-2xl md:text-3xl font-bold">{{ $logs->total() }}</h2>
                </div>
                <i class="fa-solid fa-clock text-xl md:text-3xl text-blue-500"></i>
            </div>

            <!-- TABLE -->
            <div class="card">

                <!-- 📱 MOBILE -->
                <div class="block md:hidden space-y-3 px-2">

                    @foreach ($logs as $log)
                        <div class="border rounded-xl p-3 shadow-sm w-full hover:shadow-md transition">

                            <div class="text-sm font-semibold text-gray-800">
                                {{ $log->user->name ?? '-' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Room: {{ $log->room }} | AC: {{ $log->ac }}
                            </div>

                            <div class="mt-2">
                                @if ($log->activity == 'add_room')
                                    <span class="bg-purple-100 text-purple-600 px-2 py-1 rounded text-xs">ADD
                                        ROOM</span>
                                @elseif($log->activity == 'delete_room')
                                    <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-xs">DELETE ROOM</span>
                                @elseif($log->activity == 'add_ac')
                                    <span class="bg-indigo-100 text-indigo-600 px-2 py-1 rounded text-xs">ADD AC</span>
                                @elseif($log->activity == 'delete_ac')
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs">DELETE AC</span>
                                @elseif($log->activity == 'on')
                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">ON</span>
                                @elseif($log->activity == 'off')
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs">OFF</span>
                                @else
                                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs">
                                        {{ strtoupper($log->activity) }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 text-xs text-gray-500">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </div>

                        </div>
                    @endforeach

                </div>

                <!-- 💻 DESKTOP -->
                <!-- 💻 DESKTOP -->
                <div class="hidden md:block overflow-x-auto">

                    <table class="w-full text-xs md:text-sm">

                        <thead class="border-b bg-gray-50">
                            <tr class="text-left text-gray-500">
                                <th class="p-3">User</th>
                                <th class="p-3">Room</th>
                                <th class="p-3">AC</th>
                                <th class="p-3">Activity</th>
                                <th class="p-3">Time</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($logs as $log)
                                <tr class="border-b hover:bg-gray-50 transition">

                                    <td class="p-3">{{ $log->user->name ?? '-' }}</td>

                                    <td class="p-3">{{ $log->room }}</td>

                                    <td class="p-3">{{ $log->ac }}</td>

                                    <td class="p-3">
                                        <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs">
                                            {{ strtoupper($log->activity) }}
                                        </span>
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
                <div class="mt-4 text-sm">
                    {{ $logs->links() }}
                </div>

            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            let sidebar = document.getElementById("sidebar")
            sidebar.classList.toggle("close")
            sidebar.classList.toggle("open")
        }
    </script>

</body>

</html>
