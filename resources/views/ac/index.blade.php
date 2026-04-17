    <!DOCTYPE html>
    <html lang="en">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>AC Units</title>

        <link href="/css/app.css" rel="stylesheet">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        <style>
            /* ===== GLOBAL ===== */
            body,
            html {
                margin: 0;
                max-width: 100%;
                overflow-x: hidden;
            }

            button {
                transition: 0.2s ease;
            }

            /* ===== SIDEBAR ===== */
            .sidebar {
                width: 256px;
                transition: all 0.3s ease;
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

            /* ===== CONTENT ===== */
            .main-content {
                margin-left: 256px;
                transition: all 0.3s ease;
            }

            .sidebar.close+.main-content {
                margin-left: 80px;
            }

            /* ===== CARD ===== */
            .ac-card {
                background: rgba(15, 23, 42, 0.85);
                color: white;
                backdrop-filter: blur(16px);
                border-radius: 20px;
                padding: 24px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                transition: 0.2s ease;
            }

            .ac-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            }

            /* ===== PANEL ANIMATION ===== */
            .ac-panel {
                transition: 0.25s ease;
                opacity: 0;
                transform: translateY(8px);
            }

            .ac-panel:not(.hidden) {
                opacity: 1;
                transform: translateY(0);
            }

            /* ===== SWITCH ===== */
            .switch {
                position: relative;
                width: 50px;
                height: 26px;
            }

            .slider {
                position: absolute;
                inset: 0;
                background: #d1d5db;
                border-radius: 16px;
                transition: 0.3s;
            }

            .slider:before {
                content: "";
                position: absolute;
                height: 20px;
                width: 20px;
                left: 3px;
                bottom: 3px;
                background: white;
                border-radius: 50%;
                transition: 0.3s;
            }

            input:checked+.slider {
                background: #22c55e;
            }

            input:checked+.slider:before {
                transform: translateX(24px);
            }

            /* ===== MODE BUTTON ===== */
            .mode-btn {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 5px;
                background: rgba(30, 41, 59, 0.8);
                border-radius: 14px;
                height: 60px;
                font-size: 12px;
                border: 1px solid rgba(255, 255, 255, 0.05);
                transition: 0.2s ease;
            }

            .mode-btn:hover {
                background: rgba(59, 130, 246, 0.2);
                transform: translateY(-2px);
            }

            /* ===== HEADER ===== */
            header {
                height: 72px;
                box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
            }

            /* ===== DROPDOWN ===== */
            #dropdownAC {
                position: absolute;
                top: 60px;
                left: 0;
                width: 160px;
                background: rgba(15, 23, 42, 0.95);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                opacity: 0;
                transform: translateY(10px);
                transition: 0.25s ease;
            }

            #dropdownAC.show {
                opacity: 1;
                transform: translateY(0);
            }

            /* ===== BACKGROUND ===== */
            .custom-bg {
                background:
                    linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                    url('/images/wallpaper.jpeg') no-repeat center center fixed;
                background-size: cover;
            }

            /* ===== RESPONSIVE ===== */
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

            @media(max-width:768px) {
                .ac-card {
                    padding: 16px;
                }

                .ac-panel>div {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>

    </head>

    <body class="custom-bg overflow-x-hidden">

        <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

        <!-- SIDEBAR -->
        <div id="sidebar" class="sidebar fixed top-0 left-0 w-64 bg-slate-900 text-white shadow-lg h-full p-6">
            <div class="flex justify-between items-center pb-5 mb-8 border-b">
                <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="menu-text">AC System</span>
                </h2>

                <button onclick="toggleSidebar()" class="lg:hidden text-gray-500 hover:text-blue-500">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <ul class="space-y-3">
                @auth
                    {{-- Dashboard --}}
                    <li>
                        <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>

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
                            <a href="/logs" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10">
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
        <div class="main-content min-h-screen flex flex-col bg-transparent">


            <header class="w-full flex items-center justify-between px-4 py-6 bg-slate-900/80 text-white">
                <!-- LEFT -->
                <div class="flex items-center gap-3 h-full">

                    <!-- MENU -->
                    <button onclick="toggleSidebar()" class="lg:hidden text-lg text-white">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <!-- TITLE -->
                    <div>
                        <h1 class="text-base md:text-xl font-semibold text-white leading-none">
                            Centralized AC Management
                        </h1>
                    </div>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="px-4 md:px-6 py-6">

                @php
                    $firstAc = $acs->first();
                @endphp

                <div
                    class="header-wrap relative flex items-center justify-between h-[64px] px-4 md:px-6 mb-2 rounded-xl shadow-md border border-white/10">

                    <!-- LEFT -->
                    <div class="flex items-center gap-2 min-w-0">

                        <!-- SELECT AC  -->
                        <div onclick="toggleDropdown()"
                            class="flex items-center gap-3 px-3 py-2 text-sm bg-slate-800 text-white border border-white/10 rounded-xl">

                            <span id="selectedAC" class="font-medium text-white truncate max-w-[80px]">
                                {{ $firstAc ? 'AC ' . $firstAc->ac_number : 'No AC' }}
                            </span>

                            <i class="fa-solid fa-chevron-down text-xs text-white"></i>
                        </div>

                        <!-- ROOM -->
                        <span class="text-white tracking-widest text-xs md:text-sm">
                            {{ strtoupper($room->name) }}
                        </span>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center gap-3 shrink-0">
                        @auth
                            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                <button onclick="openModal()"
                                    class="bg-blue-600 text-white px-3 py-1.5 text-xs rounded-lg flex items-center">
                                    + Add AC
                                </button>
                            @endif
                        @endauth

                        <!-- DELETE AC -->
                        @auth
                            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                <form id="deleteForm" method="POST" action="">
                                    @csrf
                                    @method('DELETE')

                                    <button {{ !$firstAc ? 'disabled' : '' }}
                                        class="w-8 h-8 flex items-center justify-center rounded-full border border-red-400 text-red-500">

                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>

                    <!-- DROPDOWN BARU -->
                    <div id="dropdownAC"
                        class="hidden absolute bg-[rgba(15,23,42,0.95)] border border-white/10 text-white rounded-xl shadow-lg">

                        @foreach ($acs as $ac)
                            <div onclick="selectAC({{ $ac->id }}, 'AC {{ $ac->ac_number }}')"
                                class="px-4 py-3 hover:bg-blue-500/20 cursor-pointer">

                                AC {{ $ac->ac_number }}

                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="p-4 md:p-8 pt-2  ">
                    @foreach ($acs as $ac)
                        <div id="ac-{{ $ac->id }}" class="ac-panel hidden">
                            <div class="grid grid-cols-1 md:grid-cols-[280px_1fr] lg:grid-cols-[350px_1fr] gap-6">

                                <!-- STATUS KHUSUS MOBILE -->
                                <div class="ac-card mb-4 md:hidden px-4 py-3">

                                    <div class="flex justify-between items-center text-sm">

                                        <div class="flex flex-col items-center flex-1">
                                            <span class="text-xs text-gray-400">POWER</span>
                                            <span class="text-green-400 font-semibold">
                                                {{ $ac->status?->power ?? 'OFF' }}
                                            </span>
                                        </div>

                                        <div class="flex flex-col items-center flex-1">
                                            <span class="text-xs text-gray-400">MODE</span>
                                            <span class="text-blue-400">
                                                {{ strtoupper($ac->status?->mode ?? 'cool') }}
                                            </span>
                                        </div>

                                        <div class="flex flex-col items-center flex-1">
                                            <span class="text-xs text-gray-400">TEMP</span>
                                            <span class="text-yellow-300">
                                                {{ $ac->status?->set_temperature ?? 24 }}°C
                                            </span>
                                        </div>

                                    </div>

                                </div>

                                <!-- LEFT -->
                                <div class="ac-card p-5 md:p-8 flex flex-col items-center justify-center text-center">

                                    <!-- POWER -->
                                    <form action="/ac/{{ $ac->id }}/toggle" method="POST"
                                        onsubmit="return handleSubmit({{ $ac->id }})">
                                        @csrf
                                        <button type="submit"
                                            class="w-16 h-16 md:w-20 md:h-20 rounded-full flex items-center justify-center
                                            {{ $ac->status && $ac->status?->power == 'ON'
                                                ? 'bg-green-500 shadow-green-300 ring-4 ring-green-200 animate-pulse'
                                                : 'bg-gray-300' }} text-white text-2xl shadow-lg hover:opacity-80 transition">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>

                                    <!-- TEMPERATURE -->
                                    <div class="mt-6">
                                        <div class="text-3xl md:text-6xl font-bold text-blue-600">
                                            {{ $ac->status?->set_temperature ?? 24 }}°C
                                        </div>

                                        <p class="text-xs text-white tracking-widest">
                                            TEMPERATURE
                                        </p>
                                    </div>

                                    <!-- BUTTON -->
                                    <div class="flex gap-3 md:gap-6 mt-6">
                                        <button
                                            onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) - 1 }})"
                                            class="w-10 h-10 md:w-12 md:h-12 bg-slate-700 text-white rounded-full text-xl hover:bg-slate-600 hover:opacity-80">
                                            −
                                        </button>

                                        <button
                                            onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) + 1 }})"
                                            class="w-10 h-10 md:w-12 md:h-12 bg-blue-600 text-white rounded-full text-xl hover:bg-blue-700 hover:opacity-80">
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- RIGHT -->
                                <div class="flex flex-col gap-5">
                                    <!-- INFO -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="ac-card text-center">
                                            <p class="text-xs text-white">SET TEMP</p>
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
                                                class="text-xl font-semibold {{ ($ac->status?->power ?? 'OFF') == 'ON' ? 'text-green-600' : 'text-white' }}">

                                                {{ $ac->status?->power ?? 'OFF' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- MODE -->
                                    <div class="ac-card">
                                        <p class="text-gray-500 mb-4 text-sm">OPERATING MODE</p>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                            <a href="/ac/{{ $ac->id }}/mode/cool"
                                                class="mode-btn {{ ($ac->status?->mode ?? '') == 'COOL' ? 'bg-blue-600 text-white shadow-lg' : '' }}">
                                                <i class="fa-solid fa-snowflake"></i> Cool
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/heat"
                                                class="mode-btn {{ ($ac->status?->mode ?? '') == 'HEAT' ? 'bg-blue-600 text-white shadow-lg' : '' }}">
                                                <i class="fa-solid fa-fire"></i> Heat
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/dry"
                                                class="mode-btn {{ ($ac->status?->mode ?? '') == 'DRY' ? 'bg-blue-600 text-white shadow-lg' : '' }}">
                                                <i class="fa-solid fa-droplet"></i> Dry
                                            </a>

                                            <a href="/ac/{{ $ac->id }}/mode/fan"
                                                class="mode-btn {{ ($ac->status?->mode ?? '') == 'FAN' ? 'bg-blue-600 text-white shadow-lg' : '' }}">
                                                <i class="fa-solid fa-fan"></i> Fan
                                            </a>
                                            <a href="/ac/{{ $ac->id }}/mode/auto"
                                                class="mode-btn {{ ($ac->status?->mode ?? '') == 'AUTO' ? 'bg-blue-600 text-white shadow-lg' : '' }}">
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
                                        @if (session('success'))
                                            <p class="text-green-500 text-sm mb-2">
                                                {{ session('success') }}
                                            </p>
                                        @endif
                                        <div id="timerView-{{ $ac->id }}">
                                            @if ($ac->timer_on || $ac->timer_off)
                                                <p class="text-gray-600 text-sm">
                                                    ON:
                                                    {{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') : '--:--' }}
                                                    &nbsp;&nbsp;
                                                    OFF:
                                                    {{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') : '--:--' }}
                                                </p>

                                                @if ($ac->timer_on)
                                                    <p class="text-green-500 text-xs mt-1">Timer ON aktif</p>
                                                @elseif ($ac->timer_off)
                                                    <p class="text-yellow-500 text-xs mt-1">Timer OFF aktif</p>
                                                @else
                                                    <p class="text-white text-xs mt-1">Timer tidak aktif</p>
                                                @endif
                                            @else
                                                <p class="text-white text-sm">
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
                                                    <p class="text-xs text-white mb-1">TURN ON</p>
                                                    <input type="time" name="timer_on"
                                                        value="{{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->format('H:i') : '' }}"
                                                        class="w-full
                                                        bg-slate-800 text-white border border-white/10 border rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                                                </div>

                                                <!-- OFF -->
                                                <div>
                                                    <p class="text-xs text-white mb-1">TURN OFF</p>
                                                    <input type="time" name="timer_off"
                                                        value="{{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->format('H:i') : '' }}"
                                                        class="w-full bg-slate-800 text-white border border-white/10 rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                                                </div>
                                            </div>

                                            <!-- BUTTON -->
                                            <div class="flex gap-3">

                                                <button
                                                    onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();"
                                                    class="flex-1 bg-blue-600 text-white py-3 rounded-full">
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
                <div id="modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center">

                    <div class="bg-white p-8 rounded-2xl w-96 shadow-lg relative">

                        <!-- CLOSE BUTTON -->
                        <button onclick="closeModal()"
                            class="absolute top-4 right-4 text-black text-2xl z-50 hover:text-red-500">
                            ✕
                        </button>

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

                            <button class="bg-blue-600 text-white w-full py-2 rounded-lg">
                                Create AC
                            </button>
                        </form>

                    </div>
                </div>
            @endif
        @endauth

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
            document.querySelectorAll("#sidebar a").forEach(link => {
                link.addEventListener("click", () => {
                    document.getElementById("sidebar").classList.remove("open");
                    document.getElementById("overlay").classList.add("hidden");
                });
            });

            function openModal() {
                document.getElementById('modal').classList.remove('hidden')
            }

            function closeModal() {
                document.getElementById('modal').classList.add('hidden');

                const form = document.querySelector('#modal form');
                if (form) form.reset();
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

                localStorage.setItem("selectedAC", id);

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
                let deleteForm = document.getElementById('deleteForm');
                if (deleteForm) {
                    deleteForm.action = "/ac/" + id;
                }

                document.getElementById('dropdownAC').classList.remove('show');
                setTimeout(() => {
                    document.getElementById('dropdownAC').classList.add('hidden');
                }, 200);
            }

            document.addEventListener("DOMContentLoaded", function() {

                @if (session('new_ac_id'))

                    let id = "{{ session('new_ac_id') }}";

                    localStorage.setItem("selectedAC", id);

                    let el = document.querySelector(`[onclick*="selectAC(${id},"]`);
                    let name = el ? el.innerText.trim() : "AC";

                    selectAC(id, name);
                @else

                    let saved = localStorage.getItem("selectedAC");

                    if (saved) {

                        let target = document.getElementById('ac-' + saved);

                        if (target) {

                            let el = document.querySelector(`[onclick*="selectAC(${saved},"]`);
                            let name = el ? el.innerText.trim() : "AC";

                            selectAC(saved, name);

                        } else {

                            localStorage.removeItem("selectedAC");

                            @if ($firstAc)
                                selectAC({{ $firstAc->id }}, "AC {{ $firstAc->ac_number }}");
                            @endif

                        }

                    }
                @endif

            });

            function handleSubmit(id) {
                return true;
            }
        </script>
    </body>

    </html>
