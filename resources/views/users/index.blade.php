<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management</title>

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

        /* ===== HEADER — diam di atas ===== */
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

        /* ===== PAGE BODY — area yang scroll ===== */
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

        /* ===== CARD ===== */
        .card {
            background: rgba(15, 23, 42, 0.7);
            color: white;
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.2s ease;
        }

        /* ===== OVERLAY ===== */
        #overlay {
            opacity: 0;
            transition: opacity 0.3s ease;
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
            }

            .sidebar.open {
                transform: translateX(0);
            }
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
        <div id="overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden z-40"></div>

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
                        {{ request()->is('dashboard') ? 'bg-white/10 text-white font-semibold' : 'hover:bg-white/10 text-gray-300' }}">
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
                            <span class="menu-text">Manage Rooms & Control Ac</span>
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
                        <a href="/logout" class="ml-auto text-red-500 hover:text-red-600 text-lg">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
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
                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden text-white text-lg">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="flex flex-col leading-tight">
                        <h1 class="text-base md:text-xl font-bold text-white">User Management</h1>
                        <p class="text-sm text-blue-200 font-medium">Manage System Users & Roles</p>
                    </div>
                </div>
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY — hanya bagian ini yang scroll -->
            <div class="page-body">
                <div class="px-4 py-4 md:px-6 md:py-6">

                    <!-- STATS -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                        <!-- Total Users -->
                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">TOTAL USERS</p>
                                    <h2 class="text-3xl font-bold text-white">{{ $totalUsers }}</h2>
                                    <p class="text-green-400 text-sm mt-1">+2 this week</p>
                                </div>
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-500/20 text-blue-300">
                                    <i class="fa-solid fa-users text-lg"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Online Now -->
                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">ONLINE NOW</p>
                                    <h2 class="text-3xl font-bold text-white">{{ $onlineUsers }}</h2>
                                    <p class="text-green-400 text-sm mt-1">50% active</p>
                                </div>
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-green-500/20 text-green-300">
                                    <i class="fa-solid fa-user-check text-lg"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Administrators -->
                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">ADMINISTRATORS</p>
                                    <h2 class="text-3xl font-bold text-white">{{ $adminUsers }}</h2>
                                    <p class="text-gray-400 text-sm mt-1">System privileges</p>
                                </div>
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-yellow-500/20 text-yellow-300">
                                    <i class="fa-solid fa-shield-halved text-lg"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- END STATS -->

                    <!-- USER CARD -->
                    <div class="card" style="padding: 0; overflow: hidden;">

                        <!-- TOP BAR -->
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-white/10">

                            <!-- Search (kiri, lebar) -->
                            <form method="GET" action="/users" class="flex-1 max-w-sm">
                                <div
                                    class="flex items-center bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 gap-2">
                                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm shrink-0"></i>
                                    <input name="search" value="{{ request('search') }}"
                                        placeholder="Search by username..." autocomplete="off"
                                        class="bg-transparent text-white text-sm outline-none w-full placeholder-gray-400">
                                </div>
                            </form>

                            <!-- Spacer -->
                            <div class="flex-1"></div>

                            <!-- Filter Tabs -->
                            <div class="flex items-center gap-1 shrink-0">
                                <a href="/users"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                                    {{ !request('role') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                    All
                                </a>
                                <a href="/users?role=admin"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                                    {{ request('role') == 'admin' ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                    Admin
                                </a>
                                <a href="/users?role=operator"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                                    {{ request('role') == 'operator' ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                    Operator
                                </a>
                                <a href="/users?role=user"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                                    {{ request('role') == 'user' ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                                    User
                                </a>
                            </div>

                            <!-- Add User Button -->
                            <button onclick="openModal()"
                                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition shrink-0">
                                <i class="fa-solid fa-user-plus"></i>
                                Add User
                            </button>

                        </div>
                        <!-- END TOP BAR -->

                        <!-- INFO BAR -->
                        <div class="flex justify-between items-center px-5 py-3 border-b border-white/10">
                            <p class="text-sm text-gray-400">
                                Showing <span class="text-white font-semibold">{{ $users->count() }}</span>
                                of <span class="text-white font-semibold">{{ $totalUsers }}</span> users
                            </p>
                            <div class="flex items-center gap-2 text-green-400 text-sm">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                Live
                            </div>
                        </div>
                        <!-- END INFO BAR -->

                        <!-- USER LIST -->
                        <div id="user-list">

                            @foreach ($users as $user)
                                @php
                                    $isOnline = $user->isOnline;
                                    $statusText = $isOnline
                                        ? 'Online'
                                        : ($user->last_activity
                                            ? \Carbon\Carbon::parse($user->last_activity)->diffForHumans()
                                            : 'Offline');
                                    $statusColor = $isOnline ? 'text-green-400' : 'text-gray-500';
                                    $statusDot = $isOnline ? 'bg-green-400' : 'bg-gray-500';
                                @endphp

                                <div
                                    class="flex items-center justify-between px-5 py-4
                                    {{ !$loop->last ? 'border-b border-white/10' : '' }}
                                    hover:bg-white/[0.04] transition-colors duration-150">

                                    <!-- Left: Avatar + Name -->
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="relative shrink-0">
                                            <div
                                                class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <span
                                                class="absolute bottom-0.5 right-0.5 w-3 h-3 rounded-full border-2 border-slate-900 {{ $statusDot }}"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-white font-semibold text-sm leading-snug">
                                                {{ $user->name }}</p>
                                            <p class="text-gray-500 text-xs mt-0.5">
                                                {{ '@' . strtolower($user->name) }}</p>
                                        </div>
                                    </div>

                                    <!-- Right: Role + Status + Menu -->
                                    <div class="flex items-center gap-5 shrink-0">

                                        <!-- Role Badge -->
                                        @if ($user->role == 'admin')
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full border border-blue-400/40 text-blue-400 bg-blue-500/10 shadow-[0_0_10px_rgba(59,130,246,0.3)]">

                                                <i class="fa-solid fa-shield text-[10px]"></i>
                                                ADMIN
                                            </span>
                                        @elseif ($user->role == 'operator')
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full border border-yellow-400/40 text-yellow-400 bg-yellow-500/10 shadow-[0_0_10px_rgba(234,179,8,0.3)]">

                                                <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                                OPERATOR
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full border border-white/20 text-gray-300 bg-white/5">

                                                <i class="fa-regular fa-user text-[10px]"></i>
                                                USER
                                            </span>
                                        @endif

                                        <!-- Online Status -->
                                        <div class="flex items-center gap-1.5 min-w-[80px]">
                                            <span
                                                class="w-2 h-2 rounded-full {{ $statusDot }} {{ $isOnline ? 'animate-pulse' : '' }}"></span>
                                            <span class="text-xs {{ $statusColor }}">{{ $statusText }}</span>
                                        </div>

                                        <!-- Dropdown -->
                                        <div class="relative">
                                            <button onclick="toggleMenu({{ $user->id }})"
                                                class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-white rounded-lg hover:bg-white/10 transition">
                                                <i class="fa-solid fa-ellipsis text-sm"></i>
                                            </button>
                                            <div id="menu-{{ $user->id }}"
                                                class="hidden absolute right-0 top-9 w-36 bg-slate-800 border border-white/10 rounded-xl shadow-xl z-50 overflow-hidden">
                                                <button onclick="deleteUser({{ $user->id }})"
                                                    class="w-full text-left px-4 py-2.5 text-red-400 hover:bg-white/10 flex items-center gap-2 text-sm">
                                                    <i class="fa-solid fa-trash text-xs"></i> Delete
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endforeach

                            <!-- Pagination -->
                            <div class="px-5 py-4 border-t border-white/10">
                                {{ $users->links() }}
                            </div>

                        </div>
                        <!-- END USER LIST -->

                    </div>
                    <!-- END USER CARD -->

                </div>
            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->

    <!-- ==================== MODAL ==================== -->
    <div id="modal"
        class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg relative">

            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl transition">
                ✕
            </button>

            <h2 class="text-lg md:text-xl font-semibold text-white mb-5">Add New User</h2>

            <form method="POST" action="/users">
                @csrf
                <input type="text" name="name" placeholder="Name"
                    class="bg-white/10 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
                <input type="password" name="password" placeholder="Password"
                    class="bg-white/10 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    required>

                <div class="relative mb-4">
                    <select name="role"
                        class="w-full p-3 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700
                            focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                            transition shadow-sm appearance-none">
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="user">User</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-sm"></i>
                    </div>
                </div>

                <button class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2.5 rounded-lg shadow transition">
                    Create User
                </button>
            </form>

        </div>
    </div>
    <!-- ==================== END MODAL ==================== -->


    <!-- ===== SCRIPTS — di luar semua div, sebelum </body> ===== -->
    <script>
        // ---- Sidebar Toggle ----
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

        document.getElementById('overlay').onclick = function() {
            document.getElementById('sidebar').classList.remove('open');
            this.classList.add('hidden');
        };

        // ---- Modal ----
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        // ---- Dropdown Menu ----
        function toggleMenu(id) {
            document.querySelectorAll('[id^="menu-"]').forEach(menu => {
                if (menu.id !== 'menu-' + id) menu.classList.add('hidden');
            });
            document.getElementById('menu-' + id).classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const isToggleBtn = e.target.closest('[onclick^="toggleMenu"]');
            const isMenu = e.target.closest('[id^="menu-"]');
            if (!isToggleBtn && !isMenu) {
                document.querySelectorAll('[id^="menu-"]').forEach(menu => menu.classList.add('hidden'));
            }
        });

        // ---- Delete User ----
        function deleteUser(id) {
            if (!confirm('Yakin hapus user?')) return;
            fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(res => res.json())
                .then(() => location.reload())
                .catch(() => alert('Gagal hapus user'));
        }

        // ---- Activity Ping ----
        setInterval(() => {
            if (!document.hidden) {
                fetch('/update-activity', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
            }
        }, 60000);
    </script>

    <script>
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function(e) {

                if (window.innerWidth <= 1024) {
                    e.preventDefault();

                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('overlay');

                    sidebar.classList.remove('open');
                    overlay.classList.add('hidden');

                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 250);
                }
            });
        });
    </script>

</body>

</html>
