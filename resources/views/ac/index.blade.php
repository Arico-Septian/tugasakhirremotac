<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AC Units</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ===== SIDEBAR ===== */

        .sidebar {
            transition: .3s;
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
            transition: .3s;
        }

        .sidebar.close+.main-content {
            margin-left: 100px;
        }

        /* ===== CARD ===== */

        .ac-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            border-radius: 22px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: .3s;
        }

        .ac-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.12);
        }


        /* ===== POWER SWITCH ===== */

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #d1d5db;
            transition: .3s;
            border-radius: 999px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background: #22c55e;
        }

        input:checked+.slider:before {
            transform: translateX(24px);
        }


        /* ===== TEMPERATURE SLIDER ===== */

        .temp-slider {
            width: 100%;
            height: 6px;
            border-radius: 999px;
            background: #e5e7eb;
            outline: none;
            appearance: none;
        }

        .temp-slider::-webkit-slider-thumb {
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
        }


        /* ===== MODE BUTTON ===== */

        .mode-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #f9fafb;
            border-radius: 16px;
            height: 68px;
            font-size: 12px;
            transition: .25s;
            border: 1px solid #eee;
        }

        .mode-btn:hover {
            background: #eef2ff;
            transform: translateY(-3px) scale(1.05);
        }
    </style>

</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50">

    <!-- SIDEBAR -->

    <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full p-6 border-r">

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
                {{-- Dashboard (semua role) --}}

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

        <header
            class="sticky top-0 bg-white/80 backdrop-blur-lg border-b px-8 py-5 flex justify-between items-center shadow-sm">

            <div class="flex items-center gap-4">

                <!-- BACK BUTTON -->

                <a href="/rooms" class="flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium">

                    <i class="fa-solid fa-arrow-left"></i>

                </a>

                <h1 class="text-2xl font-bold text-gray-800">
                    {{ strtoupper($room->name) }} AC Units
                </h1>

            </div>

            @auth
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <button onclick="openModal()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow">
                        + Add AC
                    </button>
                @endif
            @endauth

        </header>



        <!-- CONTENT -->

        <div class="p-8">
            <div class="relative mb-6">

                <!-- BUTTON -->
                <button onclick="toggleDropdown()"
                    class="w-full flex justify-between items-center px-5 py-4 bg-white/80 backdrop-blur-lg border border-gray-200 rounded-2xl shadow-md hover:shadow-lg transition">

                    <span id="selectedAC">Pilih AC</span>
                    <i class="fa-solid fa-chevron-down"></i>

                </button>

                <!-- DROPDOWN -->
                <div id="dropdownAC"
                    class="hidden absolute w-full mt-2 bg-white border rounded-xl shadow-lg max-h-60 overflow-y-auto z-50">

                    @foreach ($acs as $ac)
                        <div onclick="selectAC({{ $ac->id }}, 'AC {{ $ac->ac_number }}')"
                            class="px-4 py-3 hover:bg-blue-100 cursor-pointer">

                            AC {{ $ac->ac_number }}

                        </div>
                    @endforeach

                </div>

            </div>

            <div>

                @foreach ($acs as $index => $ac)
                    <div id="ac-{{ $ac->id }}" class="ac-panel {{ $index == 0 ? '' : 'hidden' }}">

                        <div class="ac-card w-full max-w-xl mx-auto border border-gray-100">

                            <div class="flex justify-between items-center mb-4">

                                <h2 class="text-lg font-semibold">
                                    AC {{ $ac->ac_number }}
                                </h2>

                                <i class="fa-solid fa-snowflake text-blue-500"></i>

                            </div>

                            <p class="text-gray-500 text-sm mb-3">
                                Brand : {{ $ac->brand }}
                            </p>


                            <!-- STATUS -->

                            @if ($ac->status && $ac->status->power == 'ON')
                                <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-semibold">
                                    ● ON
                                </span>
                            @else
                                <span class="bg-gray-200 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">
                                    ● OFF
                                </span>
                            @endif


                            <!-- POWER -->
                            @auth
                                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                    <div class="mt-5">

                                        <p class="text-xs text-gray-500 mb-2">Power</p>

                                        <form action="/ac/{{ $ac->id }}/toggle" method="POST">

                                            @csrf

                                            <label class="switch">

                                                <input type="checkbox" onchange="this.form.submit()"
                                                    {{ $ac->status && $ac->status->power == 'ON' ? 'checked' : '' }}>

                                                <span class="slider"></span>

                                            </label>

                                        </form>

                                    </div>



                                    <!-- TEMPERATURE -->

                                    <div class="mt-6">

                                        <p class="text-xs text-gray-500 mb-2">
                                            Temperature : {{ $ac->status->set_temperature ?? 24 }}°C
                                        </p>

                                        <input type="range" min="16" max="30"
                                            value="{{ $ac->status->set_temperature ?? 24 }}" class="temp-slider"
                                            onchange="setTemp({{ $ac->id }},this.value)">

                                    </div>



                                    <!-- MODE -->

                                    <div class="mt-6">

                                        <p class="text-xs text-gray-500 mb-3">Mode</p>

                                        <div class="grid grid-cols-5 gap-2">

                                            <a href="/ac/{{ $ac->id }}/mode/cool" class="mode-btn">
                                                <i class="fa-solid fa-snowflake"></i>
                                                Cool
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/heat" class="mode-btn">
                                                <i class="fa-solid fa-fire"></i>
                                                Heat
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/dry" class="mode-btn">
                                                <i class="fa-solid fa-droplet"></i>
                                                Dry
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/fan" class="mode-btn">
                                                <i class="fa-solid fa-fan"></i>
                                                Fan
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/auto" class="mode-btn">
                                                <i class="fa-solid fa-rotate"></i>
                                                Auto
                                            </a>

                                        </div>

                                        <form action="/ac/{{ $ac->id }}/schedule" method="POST" class="mt-4">

                                            @csrf

                                            <div class="grid grid-cols-2 gap-3">

                                                <div>
                                                    <label class="text-sm text-gray-500">ON Time</label>
                                                    <input type="time" name="timer_on"
                                                        class="w-full border rounded-lg p-2">
                                                </div>

                                                <div>
                                                    <label class="text-sm text-gray-500">OFF Time</label>
                                                    <input type="time" name="timer_off"
                                                        class="w-full border rounded-lg p-2">
                                                </div>

                                            </div>

                                            <button
                                                class="mt-3 w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl shadow-md hover:shadow-lg transition">

                                                Set Timer

                                            </button>

                                        </form>

                                    </div>
                                @endif

                                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                    <form action="/ac/{{ $ac->id }}" method="POST" class="mt-6">

                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this AC?')"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg">

                                            Delete AC

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
            <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center">

                <div class="bg-white p-8 rounded-2xl w-96 shadow-lg">

                    <h2 class="text-xl font-bold mb-5">
                        Add New AC
                    </h2>

                    <form method="POST" action="/rooms/{{ $room->id }}/ac">

                        @csrf

                        <input type="number" name="ac_number" placeholder="AC Number"
                            class="border p-3 w-full mb-3 rounded-lg">

                        <input type="text" name="name" placeholder="AC Name"
                            class="border p-3 w-full mb-3 rounded-lg">

                        <input type="text" name="brand" placeholder="Brand"
                            class="border p-3 w-full mb-4 rounded-lg">

                        <button class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2 rounded-lg">

                            Create AC

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
            document.getElementById('modal').classList.remove('hidden')
        }

        function setTemp(id, temp) {
            window.location = "/ac/" + id + "/temp/" + temp
        }
    </script>

    <script>
        function toggleDropdown() {
            document.getElementById('dropdownAC').classList.toggle('hidden');
        }

        function selectAC(id, name) {

            // ubah text
            document.getElementById('selectedAC').innerText = name;

            // tutup dropdown
            document.getElementById('dropdownAC').classList.add('hidden');

            // switch AC
            document.querySelectorAll('.ac-panel').forEach(el => {
                el.classList.add('hidden');
            });

            document.getElementById('ac-' + id).classList.remove('hidden');
        }

        // klik luar = tutup dropdown
        window.addEventListener('click', function(e) {

            let dropdown = document.getElementById('dropdownAC');

            if (!e.target.closest('#dropdownAC') && !e.target.closest('button')) {
                dropdown.classList.add('hidden');
            }
        });
    </script>


</body>

</html>
