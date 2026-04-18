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
            /* ===== SIDEBAR ===== */
            .sidebar {
                width: 256px;
                transition: all 0.3s ease;
                will-change: transform;
            }

            .sidebar.close {
                width: 80px;
            }

            .sidebar.close .menu-text,
            .sidebar.close h2 span {
                display: none;
            }

            .sidebar.close ul li a {
                justify-content: center;
            }

            /* ===== MAIN CONTENT ===== */
            .main-content {
                margin-left: 256px;
                transition: margin-left 0.3s ease;
            }

            .sidebar.close+.main-content {
                margin-left: 80px;
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

            .card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            }

            /* ===== MODAL ===== */
            #modal {
                transition: all 0.2s ease;
                backdrop-filter: blur(6px);
            }

            /* ===== BODY ===== */
            body {
                overflow-x: hidden;
            }

            /* ===== HEADER ===== */
            header {
                height: 72px;
                display: flex;
                align-items: center;
            }

            /* ===== BACKGROUND ===== */
            .custom-bg {
                background:
                    linear-gradient(rgba(10, 20, 80, 0.6), rgba(10, 20, 80, 0.7)),
                    url('/images/wallpaper.jpeg') no-repeat center center;
                background-size: cover;
            }

            /* ===== MOBILE ===== */
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
        </style>
    </head>

    <body class="custom-bg">

        <div id="overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm hidden z-40"></div>

        <!-- SIDEBAR -->
        <div id="sidebar"
            class="sidebar fixed top-0 left-0 w-64 bg-slate-900 text-white shadow-lg h-full p-6 border-r border-white/10 z-[999]">

            <!-- HEADER -->
            <div class="flex justify-between items-center pb-5 mb-8 border-b border-white/10">
                <h2 class="text-xl font-bold text-blue-500 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i>
                    <span class="menu-text">AC System</span>
                </h2>

                <button onclick="toggleSidebar()" class="md:hidden text-gray-300">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- MENU -->
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

            <!-- PROFILE -->
            <div class="absolute bottom-6 left-6 right-6">

                <div class="profile-full">
                    <div class="w-full flex items-center gap-3 px-3 py-2">

                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>

                        <div class="text-left menu-text">
                            <p class="text-sm font-semibold text-white">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ Auth::user()->role }}
                            </p>
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

        <!-- MAIN -->
        <div class="main-content min-h-screen flex flex-col pt-[72px]">

            <header
                class="sticky top-0 z-[999] bg-slate-900/70 backdrop-blur-md px-6 py-4 flex items-center justify-between">

                <div class="flex flex-wrap items-center gap-2">

                    <button onclick="toggleSidebar()" class="md:hidden text-white text-lg">
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <div class="flex flex-col leading-tight">

                        <h1 class="text-base md:text-xl font-bold text-white">
                            User Management
                        </h1>

                        <p class="text-sm text-blue-200 font-medium">
                            Manage System Users & Roles
                        </p>

                    </div>
                </div>

            </header>

            <div class="px-4 py-4 md:px-6 md:py-6">
                <div class="w-full">

                    <!-- STATS -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

                        <!-- TOTAL USERS -->
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

                        <!-- ONLINE NOW -->
                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">

                            <div class="flex justify-between items-center">

                                <div>
                                    <p class="text-gray-400 text-sm mb-1">ONLINE NOW</p>
                                    <h2 class="text-3xl font-bold text-white">
                                        {{ $onlineUsers }}
                                    </h2>
                                    <p class="text-green-400 text-sm mt-1">50% active</p>
                                </div>

                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-green-500/20 text-green-300">
                                    <i class="fa-solid fa-user-check text-lg"></i>
                                </div>

                            </div>

                        </div>

                        <!-- ADMIN -->
                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">

                            <div class="flex justify-between items-center">

                                <div>
                                    <p class="text-gray-400 text-sm mb-1">ADMINISTRATORS</p>
                                    <h2 class="text-3xl font-bold text-white">
                                        {{ $adminUsers }}
                                    </h2>
                                    <p class="text-gray-400 text-sm mt-1">System privileges</p>
                                </div>

                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-yellow-500/20 text-yellow-300">
                                    <i class="fa-solid fa-shield-halved text-lg"></i>
                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- MOBILE CARD -->
                    <div class="card">
                        <div class="mb-4 space-y-4">

                            <!-- TOP BAR -->
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

                                <!-- LEFT: SEARCH -->
                                <form method="GET" action="/users">
                                    <input name="search" placeholder="Search by username..."
                                        class="bg-white/10 border border-white/10 text-white px-4 py-2.5 rounded-xl w-full">
                                </form>

                                <!-- RIGHT: FILTER + BUTTON -->
                                <div class="flex items-center justify-end gap-2 ml-auto">

                                    <div class="flex bg-white/5 border border-white/10 rounded-xl p-1 gap-1">
                                        <a href="/users"
                                            class="filter-btn px-3 py-1.5 rounded-lg text-sm bg-white/10 text-white">All</a>

                                        <a href="/users?role=admin"
                                            class="filter-btn px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:bg-white/10">Admin</a>

                                        <a href="/users?role=operator"
                                            class="filter-btn px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:bg-white/10">Moderator</a>

                                        <a href="/users?role=user"
                                            class="filter-btn px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:bg-white/10">User</a>
                                    </div>

                                    <!-- BUTTON -->
                                    <button onclick="openModal()"
                                        class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-5 py-2 rounded-xl shadow-md hover:shadow-lg transition whitespace-nowrap">
                                        <i class="fa-solid fa-user-plus"></i>
                                        Add User
                                    </button>

                                </div>

                            </div>

                            <!-- INFO BAR -->
                            <div
                                class="flex justify-between items-center text-sm text-gray-400 border-t border-white/10 pt-3">

                                <p>
                                    Showing <span class="text-white font-semibold">{{ $users->count() }}</span>
                                    of <span class="text-white font-semibold">{{ $totalUsers }}</span> users
                                </p>

                                <div class="flex items-center gap-2 text-green-400">
                                    <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                                    Live
                                </div>

                            </div>

                        </div>

                        <!-- LIST USER -->
                        <div id="user-list">
                            <div class="bg-slate-900/40 border border-white/10 rounded-2xl overflow-visible">

                                <div class="divide-y divide-white/10">

                                    @foreach ($users as $user)
                                        @php
                                            $isOnline = $user->isOnline;

                                            $statusText = $isOnline
                                                ? 'Online'
                                                : ($user->last_activity
                                                    ? \Carbon\Carbon::parse($user->last_activity)->diffForHumans()
                                                    : 'Offline');

                                            $statusColor = $isOnline ? 'text-green-400' : 'text-gray-400';
                                            $statusDot = $isOnline ? 'bg-green-400' : 'bg-gray-500';
                                        @endphp

                                        <div
                                            class="flex items-center justify-between px-6 py-5 hover:bg-white/5 transition-all duration-200">

                                            <!-- LEFT -->
                                            <div class="flex items-center gap-4">

                                                <!-- AVATAR -->
                                                <div class="relative">
                                                    <div
                                                        class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white font-semibold">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>

                                                    <!-- ONLINE DOT -->
                                                    <span
                                                        class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-slate-900 {{ $statusDot }}"></span>
                                                </div>

                                                <!-- NAME -->
                                                <div>
                                                    <p class="text-white font-semibold leading-tight">
                                                        {{ $user->name }}
                                                    </p>
                                                    <p class="text-gray-400 text-sm">
                                                        {{ '@' . strtolower($user->name) }}
                                                    </p>
                                                </div>

                                            </div>

                                            <!-- RIGHT -->
                                            <div class="flex items-center gap-4">

                                                <!-- ROLE -->
                                                @if ($user->role == 'admin')
                                                    <span
                                                        class="px-3 py-1 text-xs rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                                        <i class="fa-solid fa-shield-halved mr-1 text-[10px]"></i>
                                                        ADMIN
                                                    </span>
                                                @elseif($user->role == 'operator')
                                                    <span
                                                        class="px-3 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                                                        <i class="fa-solid fa-user-gear mr-1 text-[10px]"></i>
                                                        MODERATOR
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-3 py-1 text-xs rounded-full bg-gray-500/20 text-gray-300 border border-gray-500/30">
                                                        <i class="fa-solid fa-user mr-1 text-[10px]"></i> USER
                                                    </span>
                                                @endif

                                                <!-- STATUS -->
                                                <div
                                                    class="flex items-center gap-2 text-sm min-w-[90px] justify-start">
                                                    <span class="w-2 h-2 rounded-full {{ $statusDot }}"></span>
                                                    <span class="{{ $statusColor }}">
                                                        {{ $statusText }}
                                                    </span>
                                                </div>

                                                <!-- MENU -->
                                                <!-- MENU -->
                                                <div class="relative">

                                                    <!-- BUTTON -->
                                                    <button onclick="toggleMenu({{ $user->id }})"
                                                        class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-white/10 transition">
                                                        <i class="fa-solid fa-ellipsis"></i>
                                                    </button>

                                                    <!-- DROPDOWN -->
                                                    <div id="menu-{{ $user->id }}"
                                                        class="hidden absolute right-0 top-10 w-32 bg-slate-800 border border-white/10 rounded-lg shadow-lg z-50">

                                                        <button onclick="deleteUser({{ $user->id }})"
                                                            class="w-full text-left px-4 py-2 text-red-400 hover:bg-white/10 flex items-center gap-2">

                                                            <i class="fa-solid fa-trash text-sm"></i>
                                                            Delete

                                                        </button>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                    @endforeach

                                </div>

                            </div>

                            <div class="p-4">
                                {{ $users->links() }}
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>

        </div>

        <!-- MODAL -->
        <div id="modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center">

            <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg relative">

                <!-- CLOSE BUTTON -->
                <button onclick="closeModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl transition">
                    ✕
                </button>

                <!-- TITLE -->
                <h2 class="text-lg md:text-xl font-semibold text-white mb-5">
                    Add New User
                </h2>

                <form method="POST" action="/users">
                    @csrf

                    <!-- NAME -->
                    <input type="text" name="name" placeholder="Name"
                        class="bg-white/10 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                        required>

                    <!-- PASSWORD -->
                    <input type="password" name="password" placeholder="Password"
                        class="bg-white/10 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                        required>

                    <!-- ROLE -->
                    <div class="relative mb-4">

                        <select name="role"
                            class="w-full p-3 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700
                                focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none
                                transition shadow-sm appearance-none">

                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="user">User</option>
                        </select>

                        <!-- ICON -->
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </div>

                    </div>
                    <!-- BUTTON -->
                    <button
                        class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2.5 rounded-lg shadow transition">
                        Create User
                    </button>

                </form>

            </div>
        </div>

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

            function openModal() {
                document.getElementById("modal").classList.remove("hidden")
            }

            function closeModal() {
                document.getElementById("modal").classList.add("hidden")
            }

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
            function toggleMenu(id) {

                document.querySelectorAll('[id^="menu-"]').forEach(menu => {
                    if (menu.id !== 'menu-' + id) {
                        menu.classList.add('hidden');
                    }
                });

                const menu = document.getElementById('menu-' + id);
                menu.classList.toggle('hidden');
            }

            document.addEventListener('click', function(e) {

                const isToggleBtn = e.target.closest('[onclick^="toggleMenu"]');
                const isMenu = e.target.closest('[id^="menu-"]');

                if (!isToggleBtn && !isMenu) {
                    document.querySelectorAll('[id^="menu-"]').forEach(menu => {
                        menu.classList.add('hidden');
                    });
                }

            });
        </script>



        <script>
            function deleteUser(id) {

                if (!confirm("Yakin hapus user?")) return;

                fetch(`/users/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(res => res.json())
                    .then(() => location.reload())
                    .catch(() => alert("Gagal hapus user"));

            }
        </script>

    </body>

    </html>
