<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Centralized AC Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
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

        /* CONTENT SHIFT */

        .main-content {
            margin-left: 256px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 80px;
        }

        /* CARD */

        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all .25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        /* ROOM CARD */

        .room-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all .25s ease;
        }

        .room-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        /* ICON */

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
        }

        /* MOBILE */

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

<body class="bg-gray-100">


    <!-- SIDEBAR -->

    <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 z-50">

        <div class="flex justify-between items-center pb-5 mb-8 border-b">

            <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>

            <button onclick="toggleSidebar()" class="text-gray-500 hover:text-blue-500">
                <i class="fa-solid fa-bars"></i>
            </button>

        </div>

        <ul class="space-y-3">

            @auth
                <li>
                    <a href="/dashboard"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 text-blue-600 font-semibold hover:bg-blue-100">
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
                        <a href="/logs" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100">
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
            class="sticky top-0 bg-white px-6 py-4 flex items-center justify-between shadow-sm transition-all duration-300">
            @auth
                <div class="flex items-center gap-6">

                    <button class="lg:hidden text-xl text-gray-600" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div>

                        <h1 class="text-2xl font-bold text-gray-800">
                            Centralized AC Management
                        </h1>

                        <p class="text-sm text-gray-400">
                            Server Room Cooling Control System
                        </p>

                    </div>

                </div>


                <div class="flex items-center gap-6">

                    <div id="systemStatus"
                        class="flex items-center gap-2 bg-green-50 text-green-600 px-3 py-1.5 rounded-full text-sm font-semibold">

                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>

                        Online

                    </div>

                </div>
            @endauth

        </header>



        <!-- CONTENT -->

        <div class="px-6 py-6">


            <!-- STATISTICS -->

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">


                <div class="stat-card">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 text-sm">Rooms</p>
                            <h2 class="text-2xl font-bold">{{ $rooms->count() }}</h2>

                        </div>

                        <div class="icon-box text-blue-500">
                            <i class="fa-solid fa-server"></i>
                        </div>

                    </div>

                </div>



                <div class="stat-card">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 text-sm">AC Units</p>
                            <h2 class="text-2xl font-bold">{{ $totalAc }}</h2>

                        </div>

                        <div class="icon-box text-indigo-500">
                            <i class="fa-solid fa-snowflake"></i>
                        </div>

                    </div>

                </div>



                <div class="stat-card">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 text-sm">Active AC Units</p>
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

                            <p class="text-gray-500 text-sm">Users</p>
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

                            <p class="text-gray-500 text-sm">Users Online</p>
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

            <h2 class="text-2xl font-bold mb-4 text-gray-800">
                Server Rooms
            </h2>


            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                @foreach ($rooms as $room)
                    <div class="room-card">

                        <div class="flex justify-between mb-3">

                            <h3 class="font-semibold text-lg">{{ $room->name }}</h3>

                            <i class="fa-solid fa-server text-gray-400"></i>

                        </div>


                        <p class="text-gray-500 text-sm mb-4">
                            Total : {{ $room->acUnits->count() }} units
                        </p>


                        <div class="bg-green-50 text-green-700 p-3 rounded-lg mb-2 flex justify-between text-sm">

                            <span>Active Units</span>

                            <span class="font-semibold">
                                {{ $room->acUnits->where('status.power', 'ON')->count() }}
                            </span>

                        </div>


                        <div class="bg-gray-100 text-gray-600 p-3 rounded-lg mb-4 flex justify-between text-sm">

                            <span>Inactive Units</span>

                            <span class="font-semibold">
                                {{ $room->acUnits->where('status.power', 'OFF')->count() }}
                            </span>

                        </div>


                        <a href="/rooms/{{ $room->id }}/status">

                            <button class="w-full py-2 rounded-lg bg-gray-900 text-white hover:bg-black transition">

                                View Details

                            </button>

                        </a>

                    </div>
                @endforeach

            </div>

        </div>

    </div>



    <script>
        function toggleSidebar() {


            let sidebar = document.getElementById("sidebar")

            sidebar.classList.toggle("close")
            sidebar.classList.toggle("open")

        }

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
    </script>

    <script>
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

</body>

</html>
