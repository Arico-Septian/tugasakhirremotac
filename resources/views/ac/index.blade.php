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

        .ac-panel {
            transition: all 0.25s ease;
            opacity: 0;
            transform: translateY(10px);
        }

        .ac-panel:not(.hidden) {
            opacity: 1;
            transform: translateY(0);
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

        .power-on {
            box-shadow: 0 0 25px rgba(34, 197, 94, 0.6);
        }

        button:active {
            transform: scale(0.96);
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

        /* ===== HEADER ===== */

        .header-wrap {
            position: relative;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        /* ===== GARIS MELINGKAR ===== */
        .header-wrap::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(120deg,
                    rgba(255, 255, 255, 0.9),
                    rgba(200, 200, 255, 0.4),
                    rgba(255, 255, 255, 0.6));

            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;

            pointer-events: none;
        }

        .header-wrap {
            position: relative;
            z-index: 1;
        }

        .header-wrap * {
            position: relative;
            z-index: 2;
        }

        .header-wrap {
            overflow: visible;
        }

        #dropdownAC {
            z-index: 9999;
        }

        #dropdownAC {
            transition: all 0.25s ease;
            transform: translateY(10px);
            opacity: 0;
        }

        #dropdownAC.show {
            transform: translateY(0);
            opacity: 1;
        }

        #dropdownAC {
            transform-origin: top left;
            will-change: transform, opacity;
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
                {{-- Dashboard --}}
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

        <!-- CONTENT -->
        <div class="p-8">

            @php
                $firstAc = $acs->first();
            @endphp

            <div class="header-wrap flex justify-between items-center mb-6 px-6 py-4">

                <!-- LEFT -->
                <div class="flex items-center gap-4">

                    <!-- SELECT AC  -->
                    <div onclick="toggleDropdown()"
                        class="flex items-center gap-3 px-5 py-3 bg-white border rounded-full shadow-sm cursor-pointer hover:shadow min-w-[200px] justify-between">

                        <span id="selectedAC" class="font-medium text-gray-700">
                            {{ $firstAc ? 'AC ' . $firstAc->ac_number : 'No AC' }}
                        </span>

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                    </div>

                    <!-- ROOM -->
                    <span class="text-gray-400 tracking-widest text-sm">
                        {{ strtoupper($room->name) }}
                    </span>
                </div>

                <!-- RIGHT -->
                <div class="flex items-center gap-3">
                    @auth
                        @if (in_array(Auth::user()->role, ['admin', 'operator']))
                            <button onclick="openModal()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow">
                                + Add AC
                            </button>
                        @endif
                    @endauth

                    <!-- DELETE PINDAH KE SINI -->
                    @auth
                        @if (in_array(Auth::user()->role, ['admin', 'operator']))
                            <form id="deleteForm" method="POST" action="/ac/{{ $firstAc?->id }}">
                                @csrf
                                @method('DELETE')

                                <button {{ !$firstAc ? 'disabled' : '' }}
                                    class="w-10 h-10 rounded-full border border-red-400 text-red-500 hover:bg-red-50 disabled:opacity-50">

                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>

                <!-- DROPDOWN BARU -->
                <div id="dropdownAC"
                    class="hidden absolute top-full left-0 mt-2 w-64 bg-white border rounded-xl shadow-lg z-[9999]">

                    @foreach ($acs as $ac)
                        <div onclick="selectAC({{ $ac->id }}, 'AC {{ $ac->ac_number }}')"
                            class="px-4 py-3 hover:bg-blue-100 cursor-pointer">

                            AC {{ $ac->ac_number }}

                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-8 pt-0">
                @foreach ($acs as $ac)
                    <div id="ac-{{ $ac->id }}" class="ac-panel hidden">
                        <div class="grid grid-cols-1 md:grid-cols-[350px_1fr] gap-6">

                            <!-- LEFT -->
                            <div class="ac-card flex flex-col items-center justify-center text-center">

                                <!-- POWER -->
                                <form action="/ac/{{ $ac->id }}/toggle" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-20 h-20 rounded-full flex items-center justify-center
                                        {{ $ac->status && $ac->status?->power == 'ON' ? 'bg-green-500 shadow-green-300' : 'bg-gray-300' }} text-white text-2xl shadow-lg hover:scale-110 transition">
                                        <i class="fa-solid fa-power-off"></i>
                                    </button>
                                </form>

                                <!-- TEMPERATURE -->
                                <div class="mt-6">
                                    <div class="text-6xl font-bold text-blue-600">
                                        {{ $ac->status?->set_temperature ?? 24 }}°C
                                    </div>

                                    <p class="text-xs text-gray-400 tracking-widest">
                                        TEMPERATURE
                                    </p>
                                </div>

                                <!-- BUTTON -->
                                <div class="flex gap-6 mt-6">
                                    <button
                                        onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) - 1 }})"
                                        class="w-12 h-12 bg-gray-200 rounded-full text-xl hover:scale-110">
                                        −
                                    </button>

                                    <button
                                        onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) + 1 }})"
                                        class="w-12 h-12 bg-blue-600 text-white rounded-full text-xl hover:scale-110">
                                        +
                                    </button>
                                </div>
                            </div>

                            <!-- RIGHT -->
                            <div class="flex flex-col gap-5">
                                <!-- INFO -->
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="ac-card text-center">
                                        <p class="text-xs text-gray-400">SET TEMP</p>
                                        <p class="text-xl font-semibold">
                                            {{ $ac->status?->set_temperature ?? 24 }}°C
                                        </p>
                                    </div>

                                    <div class="ac-card text-center">
                                        <p class="text-xl font-semibold">
                                            {{ ucfirst($ac->status?->mode ?? 'cool') }}
                                        </p>
                                    </div>

                                    <div class="ac-card text-center">
                                        <p
                                            class="text-xl font-semibold {{ ($ac->status?->power ?? 'OFF') == 'ON' ? 'text-green-600' : 'text-gray-400' }}">

                                            {{ $ac->status?->power ?? 'OFF' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- MODE -->
                                <div class="ac-card">
                                    <p class="text-gray-500 mb-4 text-sm">OPERATING MODE</p>
                                    <div class="grid grid-cols-5 gap-2">
                                        <a href="/ac/{{ $ac->id }}/mode/cool" class="mode-btn">
                                            <i class="fa-solid fa-snowflake"></i> Cool
                                        </a>

                                        <a href="/ac/{{ $ac->id }}/mode/heat" class="mode-btn">
                                            <i class="fa-solid fa-fire"></i> Heat
                                        </a>

                                        <a href="/ac/{{ $ac->id }}/mode/dry" class="mode-btn">
                                            <i class="fa-solid fa-droplet"></i> Dry
                                        </a>

                                        <a href="/ac/{{ $ac->id }}/mode/fan" class="mode-btn">
                                            <i class="fa-solid fa-fan"></i> Fan
                                        </a>
                                        <a href="/ac/{{ $ac->id }}/mode/auto" class="mode-btn">
                                            <i class="fa-solid fa-rotate"></i> Auto
                                        </a>
                                    </div>
                                </div>

                                <!-- TIMER -->
                                <div class="ac-card">

                                    <!-- HEADER -->
                                    <div class="flex justify-between items-center mb-4">
                                        <div class="flex items-center gap-2 text-blue-500">
                                            <i class="fa-solid fa-clock"></i>
                                            <span class="font-medium">Timer Schedule</span>
                                        </div>

                                        <button id="btnTimer-{{ $ac->id }}"
                                            onclick="toggleTimer({{ $ac->id }})"
                                            class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-sm font-medium">
                                            Set Timer
                                        </button>
                                    </div>

                                    <!-- DEFAULT VIEW -->
                                    <div id="timerView-{{ $ac->id }}">
                                        @if ($ac->timer_on || $ac->timer_off)
                                            <p class="text-gray-600 text-sm">
                                                ON:
                                                {{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->format('H:i') : '--:--' }}
                                                &nbsp;&nbsp;
                                                OFF:
                                                {{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->format('H:i') : '--:--' }}
                                            </p>

                                            <p class="text-green-500 text-xs mt-1">
                                                Timer aktif
                                            </p>
                                        @else
                                            <p class="text-gray-400 text-sm">
                                                No timer set
                                            </p>
                                        @endif
                                    </div>

                                    <!-- EDIT MODE -->
                                    @if ($errors->any() && old('ac_id') == $ac->id)
                                        <div class="text-red-500 text-sm mb-2">
                                            {{ $errors->first() }}
                                        </div>
                                    @endif

                                    <form id="timerEdit-{{ $ac->id }}" class="hidden"
                                        action="/ac/{{ $ac->id }}/schedule" method="POST">
                                        @csrf
                                        <input type="hidden" name="ac_id" value="{{ $ac->id }}">
                                        <div class="grid grid-cols-2 gap-4 mb-4">

                                            <!-- ON -->
                                            <div>
                                                <p class="text-xs text-gray-400 mb-1">TURN ON</p>
                                                <input type="time" name="timer_on"
                                                    value="{{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->format('H:i') : '' }}"
                                                    class="w-full bg-gray-100 border rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                                            </div>

                                            <!-- OFF -->
                                            <div>
                                                <p class="text-xs text-gray-400 mb-1">TURN OFF</p>
                                                <input type="time" name="timer_off"
                                                    value="{{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->format('H:i') : '' }}"
                                                    class="w-full bg-gray-100 border rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                                            </div>
                                        </div>

                                        <!-- BUTTON -->
                                        <div class="flex gap-3">

                                            <button class="flex-1 bg-blue-600 text-white py-3 rounded-full">
                                                ✓ Save
                                            </button>

                                            <button type="button" onclick="toggleTimer({{ $ac->id }})"
                                                class="flex-1 bg-gray-200 py-3 rounded-full">
                                                Cancel
                                            </button>

                                        </div>
                                    </form>
                                </div>
                            </div>
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

        function toggleTimer(id) {

            const view = document.getElementById('timerView-' + id);
            const edit = document.getElementById('timerEdit-' + id);
            const btn = document.getElementById('btnTimer-' + id);

            if (!view || !edit || !btn) return;

            const isEdit = edit.classList.contains('hidden');

            view.classList.toggle('hidden');
            edit.classList.toggle('hidden');

            if (isEdit) {
                btn.classList.add('hidden');
            } else {
                btn.classList.remove('hidden');
            }
        }

        function toggleDropdown() {
            const el = document.getElementById('dropdownAC');

            if (el.classList.contains('hidden')) {
                el.classList.remove('hidden');
                setTimeout(() => el.classList.add('show'), 10);
            } else {
                el.classList.remove('show');
                setTimeout(() => el.classList.add('hidden'), 200);
            }
        }

        function selectAC(id, name) {

            console.log("Selected:", id);

            document.getElementById('selectedAC').innerText = name;

            document.querySelectorAll('.ac-panel').forEach(el => {
                el.classList.add('hidden');
            });

            let target = document.getElementById('ac-' + id);

            console.log("TARGET:", target);

            if (target) {
                target.classList.remove('hidden');
            } else {
                console.error("AC panel tidak ditemukan:", id);
            }

            document.getElementById('dropdownAC').classList.remove('show');
            setTimeout(() => {
                document.getElementById('dropdownAC').classList.add('hidden');
            }, 200);
        }

        window.addEventListener('click', function(e) {

            let dropdown = document.getElementById('dropdownAC');

            if (!e.target.closest('#dropdownAC') && !e.target.closest('[onclick="toggleDropdown()"]')) {
                dropdown.classList.remove('show');
                setTimeout(() => dropdown.classList.add('hidden'), 200);
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            @if ($firstAc)
                selectAC({{ $firstAc->id }}, "AC {{ $firstAc->ac_number }}");
            @endif
        });
    </script>
</body>

</html>
