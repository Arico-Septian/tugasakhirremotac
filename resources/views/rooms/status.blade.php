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

        /* ===== HEADER ===== */
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

        /* ===== PAGE BODY ===== */
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

        /* ===== TOAST ===== */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .toast.success {
            background: #22c55e;
        }

        .toast.error {
            background: #ef4444;
        }

        .toast.info {
            background: #3b82f6;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* ===== RESPONSIVE ===== */
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
                <button onclick="toggleSidebar()" class="md:hidden text-gray-300">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Menu -->
            <ul class="space-y-4">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->routeIs('dashboard*') || (Auth::user()->role == 'user' && request()->is('rooms*')) ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('rooms*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms & Ac Unit</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/users"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('users*') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-users"></i>
                            <span class="menu-text">User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="/logs"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
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
                    <div class="w-full flex items-center gap-3 px-3 py-2">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="text-left menu-text">
                            <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->role }}</p>
                        </div>
                        <!-- PERBAIKAN 1: Form logout dengan POST -->
                        <form action="/logout" method="POST" class="ml-auto">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-600 text-lg">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="profile-collapse hidden text-center">
                    <form action="/logout" method="POST">
                        @csrf
                        <button class="text-red-500 text-xl">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
        <!-- ==================== END SIDEBAR ==================== -->

        <!-- ==================== MAIN CONTENT ==================== -->
        <div class="main-content">

            <!-- HEADER -->
            <header class="main-header">
                <div class="flex items-center gap-3">
                    <button onclick="goBack()"
                        class="lg:hidden text-gray-300 text-lg p-1 rounded-lg hover:bg-white/10 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div>
                        <h1 class="text-base md:text-xl font-bold text-white leading-tight">
                            Room {{ ucwords($room->name) }}
                        </h1>
                        <p class="text-sm text-blue-200 font-medium">
                            Status Ac System
                        </p>
                    </div>
                </div>
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY -->
            <div class="page-body">
                <div class="px-4 py-4 md:px-6 md:py-6">
                    <div class="max-w-7xl mx-auto">

                        <!-- AC GRID -->
                        @if ($acs->count() > 0)
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
                                            <span id="power-{{ $ac->id }}"
                                                class="font-semibold transition-all duration-300">
                                                {{ $ac->status?->power ?? 'OFF' }}
                                            </span>
                                        </div>

                                        <!-- Temperature -->
                                        <div
                                            class="bg-blue-500/20 text-blue-400 p-3 rounded-lg mb-3 flex justify-between text-sm">
                                            <span class="flex items-center gap-2">
                                                <i class="fa-solid fa-temperature-half"></i> Temperature
                                            </span>
                                            <span id="temp-{{ $ac->id }}"
                                                class="font-semibold transition-all duration-300">
                                                {{ $ac->status?->set_temperature ?? 24 }}°C
                                            </span>
                                        </div>

                                        <!-- Mode -->
                                        <div
                                            class="bg-purple-500/20 text-purple-400 p-3 rounded-lg mb-3 flex justify-between text-sm">
                                            <span class="flex items-center gap-2">
                                                <i class="fa-solid fa-fan"></i> Mode
                                            </span>
                                            <span id="mode-{{ $ac->id }}"
                                                class="font-semibold transition-all duration-300">
                                                {{ strtoupper($ac->status?->mode ?? 'AUTO') }}
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
                                                    <div>ON
                                                        {{ \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') }}
                                                    </div>
                                                @endif
                                                @if ($ac->timer_off)
                                                    <div>OFF
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
                        @else
                            <div class="text-center text-white py-12">
                                <i class="fa-solid fa-snowflake text-6xl mb-4 opacity-30"></i>
                                <p class="text-lg">No AC units found in this room</p>
                                <p class="text-sm text-gray-400 mt-2">Please add AC units first</p>
                            </div>
                        @endif
                        <!-- END AC GRID -->

                    </div>
                </div>
            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->

    <script>
        // ==================== TOAST NOTIFICATION ====================
        function showToast(message, type = 'info') {
            const existingToast = document.querySelector('.toast');
            if (existingToast) {
                existingToast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => existingToast.remove(), 300);
            }

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ==================== SIDEBAR TOGGLE ====================
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

        // Close sidebar when clicking overlay
        document.getElementById('overlay')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && window.innerWidth <= 1024) {
                sidebar.classList.remove('open');
                this.classList.add('hidden');
            }
        });

        // ==================== GO BACK ====================
        function goBack() {
            if (window.innerWidth <= 1024) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/dashboard';
                }
            }
        }

        // ==================== AC STATUS LIVE UPDATE ====================
        let retryCount = 0;
        const maxRetries = 3;

        function formatTime(t) {
            if (!t || t === '0000-00-00 00:00:00') return '';
            try {
                const date = new Date(t.replace(/-/g, '/'));
                if (isNaN(date.getTime())) return '';
                return date.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            } catch (e) {
                return '';
            }
        }

        function updateElement(id, newValue) {
            const el = document.getElementById(id);
            if (el && el.innerText.trim() !== newValue.toString().trim()) {
                // Add highlight effect
                el.style.color = '#60a5fa';
                el.style.transform = 'scale(1.05)';
                el.style.display = 'inline-block';

                el.innerText = newValue;

                setTimeout(() => {
                    el.style.color = '';
                    el.style.transform = '';
                    el.style.display = '';
                }, 500);
            }
        }

        let refreshInterval = null;

        function loadStatus() {
            // PERBAIKAN 3: Gunakan endpoint yang benar
            fetch('/api/ac-status', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    return res.json();
                })
                .then(data => {
                    retryCount = 0; // Reset retry counter on success

                    if (!Array.isArray(data)) return;

                    data.forEach(item => {
                        const ac = item.acUnit || item;
                        if (!ac || !ac.id) return;

                        const id = ac.id;

                        // Update power
                        const power = item.power || ac.power || 'OFF';
                        updateElement('power-' + id, power);

                        // Update temperature
                        const temp = item.set_temperature || ac.set_temperature || 24;
                        updateElement('temp-' + id, temp + '°C');

                        // Update mode
                        const mode = (item.mode || ac.mode || 'AUTO').toUpperCase();
                        updateElement('mode-' + id, mode);

                        // Update timer
                        const timerEl = document.getElementById('timer-' + id);
                        if (timerEl) {
                            const onTime = ac.timer_on || item.timer_on;
                            const offTime = ac.timer_off || item.timer_off;
                            let textParts = [];

                            const onFormatted = formatTime(onTime);
                            if (onFormatted) textParts.push('ON ' + onFormatted);

                            const offFormatted = formatTime(offTime);
                            if (offFormatted) textParts.push('OFF ' + offFormatted);

                            const finalText = textParts.length > 0 ? textParts.join(' | ') : 'OFF';

                            if (timerEl.innerText.trim() !== finalText) {
                                timerEl.style.color = '#fbbf24';
                                timerEl.style.transform = 'scale(1.05)';
                                timerEl.innerText = finalText;
                                setTimeout(() => {
                                    timerEl.style.color = '';
                                    timerEl.style.transform = '';
                                }, 500);
                            }
                        }
                    });
                })
                .catch(err => {
                    console.error("Update failed:", err);
                    retryCount++;

                    if (retryCount >= maxRetries) {
                        if (refreshInterval) {
                            clearInterval(refreshInterval);
                            refreshInterval = null;
                            showToast('Lost connection to server. Please refresh the page.', 'error');
                        }
                    }
                });
        }

        // ==================== IDLE TIMEOUT (Security) ====================
        const role = "{{ Auth::check() ? Auth::user()->role : '' }}";
        const idleTime = role === 'admin' ? 10 * 60 * 1000 :
            role === 'operator' ? 5 * 60 * 1000 :
            2 * 60 * 1000;

        let idleTimeout;

        function resetIdleTimer() {
            clearTimeout(idleTimeout);
            idleTimeout = setTimeout(() => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }, idleTime);
        }

        // ==================== MENU LINK HANDLER ====================
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 1024) {
                    e.preventDefault();
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('overlay');

                    if (sidebar) sidebar.classList.remove('open');
                    if (overlay) overlay.classList.add('hidden');

                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 250);
                }
            });
        });

        // ==================== INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            loadStatus();
            refreshInterval = setInterval(loadStatus, 5000);
            resetIdleTimer();

            // Show session messages
            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if (session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif
        });

        // Event listeners for idle timer
        const events = ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'];
        events.forEach(event => {
            document.addEventListener(event, resetIdleTimer);
        });

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) resetIdleTimer();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (refreshInterval) clearInterval(refreshInterval);
            if (idleTimeout) clearTimeout(idleTimeout);
        });
    </script>
</body>

</html>
