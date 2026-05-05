<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>

    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        /* ===== GLOBAL ===== */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== BACKGROUND ===== */
        .custom-bg {
            background:
                linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                url('/images/wallpaper.jpeg') no-repeat center center;
            background-size: cover;
        }

        /* ===== LAYOUT ===== */
        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
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
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .sidebar.close~.main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        /* ===== HEADER - diam di atas ===== */
        .main-header {
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

        /* ===== PAGE BODY - area yang scroll ===== */
        .page-body {
            flex: 1;
            overflow-y: auto;
            scroll-behavior: smooth;
            padding-bottom: 100px;
        }

        @media (min-width: 1024px) {
            .page-body {
                padding-bottom: 0;
            }
        }

        /* ===== ROOM CARD ===== */
        .room-card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 20px;
            padding: 16px;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
            min-height: 220px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .room-card:active {
            transform: none;
        }

        /* ===== MODAL ===== */
        .modal-bg {
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(4px);
        }

        /* ===== OVERLAY ===== */
        #overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* ===== REMOVE TAP ZOOM ===== */
        button:active,
        a:active {
            transform: none !important;
        }

        /* ===== TOAST NOTIFICATION ===== */
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
        @media (min-width: 768px) {
            .room-card {
                padding: 24px;
            }
        }

        @media (max-width: 640px) {
            .room-card {
                padding: 10px;
                border-radius: 10px;
            }
        }

        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 256px !important;
            }

            .sidebar.open {
                transform: translateX(0);
            }
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
                    <a href="/dashboard"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                        {{ request()->is('dashboard') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <li>
                        <a href="/rooms"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('rooms*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-server"></i>
                            <span class="menu-text">Manage Rooms & Ac Unit</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->role == 'admin')
                    <li>
                        <a href="/users"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('users*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                            <i class="fa-solid fa-users"></i>
                            <span class="menu-text">User Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="/logs"
                            class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                            {{ request()->is('logs*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
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
            <!-- HEADER - tidak ikut scroll -->
            <header class="main-header">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden text-gray-300 text-lg" onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="flex flex-col leading-tight">
                        <h1 class="text-base md:text-xl font-bold text-white">
                            Room Management
                        </h1>
                        <p class="text-sm text-blue-200 font-medium">
                            Control Ac Unit
                        </p>
                    </div>
                </div>
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY - hanya bagian ini yang scroll -->
            <div class="page-body">

                <!-- SEARCH & ADD -->
                <div class="w-full max-w-7xl mx-auto px-4 md:px-6 mt-4 mb-4">
                    <div class="flex items-center gap-2">

                        <form method="GET" class="flex-1 min-w-0">
                            <div
                                class="flex items-center bg-white/10 border border-white/20 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">
                                <span class="px-3 text-gray-300">
                                    <i class="fa fa-search"></i>
                                </span>
                                <input name="search" value="{{ request('search') }}" type="text"
                                    placeholder="Search room..." autocomplete="off"
                                    class="flex-1 bg-transparent text-white px-2 py-2 outline-none placeholder-gray-300">
                            </div>
                        </form>

                        @auth
                            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                <button onclick="openModal()"
                                    class="h-[40px] bg-blue-600 hover:bg-blue-700 text-white px-3 rounded-lg text-sm whitespace-nowrap">
                                    + Add Room
                                </button>
                            @endif
                        @endauth

                    </div>
                </div>
                <!-- END SEARCH & ADD -->

                <!-- ROOM GRID -->
                <div class="w-full max-w-7xl mx-auto px-4 md:px-6 pb-8">
                    <div
                        class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 md:gap-4">

                        @forelse ($rooms as $room)
                            @php $status = $room->device_status ?? 'offline'; @endphp

                            <div
                                class="room-card border {{ $status == 'online' ? 'border-green-500/30' : 'border-red-500/30' }}">

                                <!-- Room Name & Status -->
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h2 class="text-sm sm:text-base md:text-lg font-semibold break-words">
                                            {{ $room->name }}
                                        </h2>

                                        @if ($status == 'online')
                                            <span
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-green-400 mt-1">
                                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                                ESP Online
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 mt-1">
                                                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                                ESP Offline
                                            </span>
                                        @endif
                                    </div>

                                    <i class="fa-solid fa-server text-white/60 text-base md:text-lg"></i>
                                </div>

                                <!-- Unit Count -->
                                <p class="text-gray-400 text-xs md:text-sm mb-3">
                                    Total : {{ $room->acUnits->count() }} units
                                </p>

                                <!-- Temperature -->
                                <div
                                    class="bg-blue-500/20 text-blue-300 px-3 py-2 rounded mb-3 text-xs md:text-sm flex justify-between">
                                    <span>Room Temperature</span>
                                    <span id="temp-{{ $room->id }}" class="font-semibold">
                                        {{ $room->temperature ?? '--' }} &deg;C
                                    </span>
                                </div>

                                <!-- Active Units -->
                                <div
                                    class="bg-green-500/20 text-green-300 px-3 py-2 text-xs md:text-sm rounded-lg mb-2 flex justify-between">
                                    <span>Active Units</span>
                                    <span class="font-semibold">
                                        {{ $room->acUnits->filter(fn($ac) => $ac->status && $ac->status->power == 'ON')->count() }}
                                    </span>
                                </div>

                                <!-- Inactive Units -->
                                <div
                                    class="bg-white/10 text-gray-300 px-3 py-2 text-xs md:text-sm rounded-lg mb-4 flex justify-between">
                                    <span>Inactive Units</span>
                                    <span class="font-semibold">
                                        {{ $room->acUnits->filter(fn($ac) => !$ac->status || $ac->status->power == 'OFF')->count() }}
                                    </span>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-col gap-2 mt-auto pt-3">
                                    <a href="/rooms/{{ $room->id }}/ac"
                                        class="text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                                        Control Ac Units
                                    </a>

                                    @auth
                                        @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                            <form action="/rooms/{{ $room->id }}" method="POST"
                                                onsubmit="return confirmDelete(event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg transition">
                                                    Delete Room
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>

                            </div>
                        @empty
                            <div class="col-span-full text-center text-white py-8">
                                <i class="fa-solid fa-folder-open text-4xl mb-2 opacity-50"></i>
                                <p>No rooms found</p>
                            </div>
                        @endforelse

                    </div>
                </div>
                <!-- END ROOM GRID -->

            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->

    <!-- ==================== MODAL ==================== -->
    @auth
        @if (in_array(Auth::user()->role, ['admin', 'operator']))
            <div id="modal" class="hidden fixed inset-0 modal-bg flex items-center justify-center z-50">
                <div class="bg-slate-900 text-white p-5 sm:p-8 rounded-2xl w-[90%] sm:w-96 shadow-lg relative">

                    <button onclick="closeModal()"
                        class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-xl z-50">
                        &times;
                    </button>

                    <h2 class="text-xl font-bold mb-5">Add New Room</h2>

                    <form id="addRoomForm" method="POST" action="/rooms">
                        @csrf
                        <input type="text" name="name" placeholder="Room Name"
                            class="bg-white/10 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                        <input type="text" name="device_id" placeholder="ESP ID (contoh: esp32_01)"
                            class="bg-white/10 border border-white/20 text-white p-3 w-full mb-4 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                        <button type="submit"
                            class="w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition">
                            Create Room
                        </button>
                    </form>

                </div>
            </div>
        @endif
    @endauth
    <!-- ==================== END MODAL ==================== -->


    <!-- ===== SCRIPTS ===== -->
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

        // ==================== MODAL FUNCTIONS ====================
        function openModal() {
            const modal = document.getElementById('modal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                // PERBAIKAN 4: Reset form saat close
                const form = modal.querySelector('form');
                if (form) form.reset();
            }
        }

        // Close modal when clicking outside
        document.getElementById('modal')?.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Escape key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });

        // ==================== DELETE CONFIRMATION ====================
        function confirmDelete(event) {
            event.preventDefault();
            if (confirm(
                    'Apakah Anda yakin ingin menghapus room ini? Semua AC unit di dalamnya juga akan terhapus. Tindakan ini tidak dapat dibatalkan.'
                )) {
                event.target.submit();
            }
            return false;
        }

        // ==================== TEMPERATURE AUTO-REFRESH ====================
        let tempRefreshInterval = null;

        function startTemperatureRefresh() {
            if (tempRefreshInterval) clearInterval(tempRefreshInterval);

            tempRefreshInterval = setInterval(() => {
                fetch('/temperature', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response was not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data && Array.isArray(data)) {
                            data.forEach(room => {
                                const el = document.getElementById(`temp-${room.id}`);
                                if (el && room.temp !== undefined && room.temp !== null) {
                                    const tempValue = typeof room.temp === 'number' ? room.temp :
                                        parseFloat(room.temp);
                                    if (!isNaN(tempValue)) {
                                        el.innerHTML = tempValue + ' &deg;C';
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Failed to fetch temperature:', err);
                        // Don't show toast to avoid spam
                    });
            }, 5000);
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
                // Create form for logout via POST
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
            startTemperatureRefresh();
            resetIdleTimer();

            // Show session messages
            @if (session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if (session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            @if ($errors->any())
                showToast("{{ $errors->first() }}", 'error');
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
            if (tempRefreshInterval) clearInterval(tempRefreshInterval);
            if (idleTimeout) clearTimeout(idleTimeout);
        });
    </script>

</body>

</html>
