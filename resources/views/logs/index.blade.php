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
            margin-left: 260px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
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
            <li>
                <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="/rooms" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                    <i class="fa-solid fa-server"></i>
                    <span class="menu-text">Manage Rooms</span>
                </a>
            </li>

            <li>
                <a href="/users" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
                    <i class="fa-solid fa-users"></i>
                    <span class="menu-text">User Management</span>
                </a>
            </li>

            <!-- ACTIVE MENU -->
            <li>
                <a href="/logs"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span class="menu-text">Activity Log</span>
                </a>
            </li>
        </ul>

    </div>

    <!-- MAIN -->
    <div class="main-content min-h-screen flex flex-col">

        <header class="sticky top-0 bg-white border-b px-8 py-5 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Activity Log</h1>
        </header>

        <div class="p-8">

            <!-- STATS -->
            <div class="card mb-6 flex justify-between items-center">
                <div>
                    <p class="text-gray-500 text-sm">Total Activity</p>
                    <h2 class="text-3xl font-bold">{{ $logs->count() }}</h2>
                </div>
                <i class="fa-solid fa-clock text-3xl text-blue-500"></i>
            </div>

            <!-- TABLE -->
            <div class="card overflow-x-auto">

                <table class="w-full">

                    <thead class="border-b">
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="p-3">User</th>
                            <th class="p-3">Room</th>
                            <th class="p-3">AC</th>
                            <th class="p-3">Activity</th>
                            <th class="p-3">Time</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-b hover:bg-gray-50">

                                <!-- USER -->
                                <td class="p-3 font-medium">
                                    {{ $log->user->name ?? '-' }}
                                </td>

                                <!-- ROOM -->
                                <td class="p-3">
                                    {{ $log->room }}
                                </td>

                                <!-- AC -->
                                <td class="p-3">
                                    {{ $log->ac }}
                                </td>

                                <!-- ACTIVITY -->
                                <td class="p-3">

                                    @if ($log->activity == 'on')
                                        <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">ON</span>
                                    @elseif($log->activity == 'off')
                                        <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs">OFF</span>
                                    @else
                                        <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs">
                                            {{ strtoupper($log->activity) }}
                                        </span>
                                    @endif

                                </td>

                                <!-- TIME -->
                                <td class="p-3 text-gray-600">
                                    {{ $log->created_at }}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>

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
