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

        /* SIDEBAR */

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
            margin-left: 260px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        /* CARD */

        .stat-card {
            background: white;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all .25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
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
            transform: translateY(-6px);
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

<body class="bg-gray-50">


    <!-- SIDEBAR -->

    <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 border-r z-50">

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

            </ul>
        @endauth

    </div>



    <!-- MAIN -->

    <div class="main-content min-h-screen flex flex-col">


        <!-- HEADER -->

        <header class="sticky top-0 bg-white border-b px-10 py-6 flex items-center justify-between shadow-sm">

            @auth
                <div class="flex items-center gap-6">

                    <button class="lg:hidden text-xl text-gray-600" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div>

                        <h1 class="text-3xl font-bold text-gray-800">
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


                    <!-- PROFILE -->

                    <div class="relative">

                        <button onclick="toggleProfile()"
                            class="flex items-center gap-3 bg-white border px-3 py-2 rounded-xl shadow-sm hover:bg-gray-50 transition">

                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white flex items-center justify-center font-bold text-sm">

                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}

                            </div>


                            <div class="text-left hidden md:block">

                                <p class="text-sm font-semibold text-gray-800">
                                    {{ Auth::user()->name }}
                                </p>

                                <p class="text-xs text-gray-400">
                                    {{ Auth::user()->role ?? 'Administrator' }}
                                </p>

                            </div>

                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>

                        </button>


                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-3 w-52 bg-white border shadow-lg rounded-xl p-2">

                            <a href="/profile"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700">

                                <i class="fa-solid fa-user"></i>
                                Profile

                            </a>

                            <a href="/logout"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-red-50 text-red-500">

                                <i class="fa-solid fa-right-from-bracket"></i>
                                Logout

                            </a>

                        </div>

                    </div>

                </div>
            @endauth

        </header>



        <!-- CONTENT -->

        <div class="p-8">


            <!-- STATISTICS -->

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">


                <div class="stat-card">

                    <div class="flex justify-between items-center">

                        <div>

                            <p class="text-gray-500 text-sm">Rooms</p>
                            <h2 class="text-3xl font-bold">{{ $rooms->count() }}</h2>

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
                            <h2 class="text-3xl font-bold">{{ $totalAc }}</h2>

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
                            <h2 class="text-3xl font-bold">{{ $activeAc }}</h2>

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
                            <h2 class="text-3xl font-bold">{{ $users }}</h2>

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
                            <h2 class="text-3xl font-bold">1</h2>

                        </div>

                        <div class="icon-box text-orange-500">
                            <i class="fa-solid fa-user-check"></i>
                        </div>

                    </div>

                </div>

            </div>



            <!-- SERVER ROOMS -->

            <h2 class="text-2xl font-bold mb-6 text-gray-800">
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
    </script>

</body>

</html>
