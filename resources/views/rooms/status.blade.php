<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AC Status</title>

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
            margin: 0;
            padding: 0;
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
            background-attachment: fixed;
            position: fixed;
            width: 100%;
            height: 100%;
        }

        /* ===== LAYOUT ===== */
        .layout {
            display: flex;
            height: 100vh;
            width: 100vw;
            position: relative;
            z-index: 1;
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
            min-height: 100vh;
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
            position: sticky;
            top: 0;
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
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            padding-bottom: 100px;
        }

        @media (min-width: 1024px) {
            .page-body {
                padding-bottom: 0;
            }
        }

        /* ===== AC CARD ===== */
        .ac-card {
            background: rgba(15, 23, 42, 0.85);
            color: white;
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .ac-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        /* ===== OVERLAY ===== */
        #overlay {
            backdrop-filter: blur(2px);
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
                will-change: transform;
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }

        @media (min-width: 768px) {
            .ac-card {
                padding: 24px;
            }
        }

        @media (max-width: 640px) {
            .ac-card {
                padding: 12px;
                border-radius: 14px;
            }

            .main-header h1 {
                font-size: 14px;
            }
        }

        .ac-card span {
            word-break: break-word;
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
        <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

        <!-- ==================== SIDEBAR ==================== -->
        <div id="sidebar" class="sidebar bg-slate-900 text-white shadow-lg p-6 border-r border-white/10">

            <!-- Logo -->
            <div class="flex justify-between items-center pb-5 mb-8 border-b border-white/10">
                <h2 class="text-xl font-bold text-blue-500 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="menu-text">AC System</span>
                </h2>
            </div>

            <!-- Menu -->
            <ul class="space-y-4">
                <li>
                    <a href="/dashboard"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->is('dashboard') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('rooms*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms & Control Ac</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/users"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('users*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-users"></i>
                            <span class="menu-text">User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="/logs"
                            class="flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('logs*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
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
                    <button onclick="goBack()"
                        class="lg:hidden text-gray-300 text-lg p-1 rounded-lg hover:bg-white/10 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div>
                        <h1 class="text-base md:text-xl font-bold text-white leading-tight">
                            {{ ucwords($room->name) }} Room Units
                        </h1>

                        <p class="text-sm text-blue-200 font-medium">
                            Status Room System
                        </p>
                    </div>
                </div>
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY — hanya bagian ini yang scroll -->
            <div class="page-body">
                <div class="px-4 py-4 md:px-6 md:py-6">
                    <div class="max-w-7xl mx-auto">

                        <!-- AC GRID -->
                        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-2 md:gap-4">
                            @foreach ($acs as $ac)
                                <div class="ac-card">

                                    <div class="flex justify-between items-center mb-4">
                                        <h2 class="text-lg font-semibold">AC {{ $ac->ac_number }}</h2>
                                        <i class="fa-solid fa-snowflake text-blue-500"></i>
                                    </div>

                                    <p class="text-gray-300 text-sm mb-4">Brand : {{ $ac->brand }}</p>

                                    <!-- Power -->
                                    <div
                                        class="bg-green-500/20 text-green-400 p-3 rounded-lg mb-3 flex justify-between text-sm">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-power-off"></i> Power
                                        </span>
                                        <span id="power-{{ $ac->id }}" class="font-semibold">
                                            {{ $ac->status?->power ?? 'OFF' }}
                                        </span>
                                    </div>

                                    <!-- Temperature -->
                                    <div
                                        class="bg-blue-500/20 text-blue-400 p-3 rounded-lg mb-3 flex justify-between text-sm">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-temperature-half"></i> Temperature
                                        </span>
                                        <span id="temp-{{ $ac->id }}" class="font-semibold">
                                            {{ $ac->status?->set_temperature ?? 24 }}°C
                                        </span>
                                    </div>

                                    <!-- Mode -->
                                    <div
                                        class="bg-purple-500/20 text-purple-400 p-3 rounded-lg mb-3 flex justify-between text-sm">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-fan"></i> Mode
                                        </span>
                                        <span id="mode-{{ $ac->id }}" class="font-semibold">
                                            {{ $ac->status?->mode ?? 'AUTO' }}
                                        </span>
                                    </div>

                                    <!-- Timer -->
                                    <div
                                        class="bg-yellow-500/20 text-yellow-300 p-3 rounded-lg flex justify-between text-sm">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-clock"></i> Timer
                                        </span>
                                        <span id="timer-{{ $ac->id }}"
                                            class="font-medium text-sm leading-tight text-right">

                                            @if ($ac->timer_on)
                                                <div>
                                                    ON
                                                    {{ \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                </div>
                                            @endif

                                            @if ($ac->timer_off)
                                                <div>
                                                    OFF
                                                    {{ \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                </div>
                                            @endif

                                            @if (!$ac->timer_on && !$ac->timer_off)
                                                OFF
                                            @endif

                                        </span>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                        <!-- END AC GRID -->

                    </div>
                </div>
            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->


    <!-- ===== SCRIPTS — di luar semua div, sebelum </body> ===== -->
    <script>
        // ----Toggle Back ----
        function goBack() {
            if (window.innerWidth <= 1024) {

                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/dashboard';
                }

            }
        }

        // ---- AC Status Live Update ----
        function loadStatus() {
            fetch('/api/ac-status')
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data)) return;

                    data.forEach(ac => {
                        if (!ac.acUnit) return;
                        const id = ac.acUnit.id;

                        updateElement('power-' + id, ac.power ?? 'OFF');
                        updateElement('temp-' + id, (ac.set_temperature ?? 24) + '°C');
                        updateElement('mode-' + id, (ac.mode ?? 'AUTO').toUpperCase());

                        const timerEl = document.getElementById('timer-' + id);
                        if (timerEl) {
                            const onTime = ac.acUnit?.timer_on;
                            const offTime = ac.acUnit?.timer_off;
                            let textParts = [];

                            if (onTime && onTime !== '0000-00-00 00:00:00') {
                                const t = formatTime(onTime);
                                if (t !== '--:--') textParts.push('ON ' + t);
                            }

                            if (offTime && offTime !== '0000-00-00 00:00:00') {
                                const t = formatTime(offTime);
                                if (t !== '--:--') textParts.push('OFF ' + t);
                            }

                            const finalText = textParts.length > 0 ? textParts.join(' | ') : 'OFF';
                            updateElement('timer-' + id, finalText);
                        }
                    });
                })
                .catch(err => console.error("Update failed:", err));
        }

        function updateElement(id, newValue) {
            const el = document.getElementById(id);
            if (el && el.innerText.trim() !== newValue.toString().trim()) {

                el.style.color = '#60a5fa';
                el.style.transform = 'scale(1.1)';

                el.innerText = newValue;

                setTimeout(() => {
                    el.style.color = '';
                    el.style.transform = 'scale(1)';
                }, 1000);
            }
        }

        function formatTime(t) {
            if (!t) return '';

            const date = new Date(t.replace(/-/g, '/'));
            if (isNaN(date)) return '--:--';
            return date.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadStatus();
            setInterval(loadStatus, 5000);
        });
    </script>
</body>

</html>
