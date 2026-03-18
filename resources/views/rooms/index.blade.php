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
            margin-left: 260px;
            transition: all .3s ease;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        /* ===== ROOM CARD ===== */

        .room-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: all .25s ease;
        }

        .room-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        /* ===== MODAL ===== */

        .modal-bg {
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(4px);
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


        @auth
            <ul class="space-y-3">
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
            </ul>
        @endauth
    </div>


    <!-- MAIN -->
    <div class="main-content min-h-screen flex flex-col">
        <header class="sticky top-0 bg-white border-b px-8 py-5 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-800">
                    Room Management
                </h1>
            </div>

            @auth
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <button onclick="openModal()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow">
                        + Add Room
                    </button>
                @endif
            @endauth
        </header>


        <!-- CONTENT -->

        <div class="p-8">
            <!-- ROOM GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($rooms as $room)
                    <div class="room-card">
                        <div class="flex justify-between mb-3">
                            <h2 class="text-lg font-semibold">
                                {{ $room->name }}
                            </h2>
                            <i class="fa-solid fa-server text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-sm mb-4">
                            Total : {{ $room->acUnits->count() }} units
                        </p>

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
                                        return $ac->status && $ac->status->power == 'OFF';
                                    })->count() }}
                            </span>
                        </div>

                        <div class="flex gap-3">
                            <a href="/rooms/{{ $room->id }}/ac"
                                class="flex-1 text-center bg-gray-900 text-white py-2 rounded-lg hover:bg-black">

                                View Details

                            </a>

                            @auth
                                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                    <form action="/rooms/{{ $room->id }}" method="POST" class="flex-1">

                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this room?')"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">

                                            Delete

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

                <div class="bg-white p-8 rounded-2xl w-96 shadow-lg">

                    <h2 class="text-xl font-bold mb-5">
                        Add New Room
                    </h2>

                    <form method="POST" action="/rooms">
                        @csrf

                        <!-- ROOM NAME -->
                        <input type="text" name="name" placeholder="Room Name"
                            class="border p-3 w-full mb-3 rounded-lg" required>

                        <!-- 🔥 TAMBAHAN WAJIB -->
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
            document.getElementById("sidebar").classList.toggle("close")
        }

        function openModal() {
            document.getElementById("modal").classList.remove("hidden")
        }
    </script>

</body>

</html>
