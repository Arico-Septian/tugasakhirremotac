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
                transition: none !important;
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
                    url('/images/wallpaper.jpeg') no-repeat center center fixed;
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
            class="sidebar fixed top-0 left-0 w-64 bg-slate-900 text-white shadow-lg h-full p-6 border-r border-white/10 z-50">

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
                    <button class="w-full flex items-center gap-3 px-3 py-2">

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

                    </button>
                </div>

                <div class="profile-collapse hidden text-center">
                    <a href="/logout" class="text-red-500 text-xl">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </a>
                </div>

            </div>
        </div>

        <!-- MAIN -->
        <div class="main-content min-h-screen flex flex-col">

            <header class="sticky top-0 bg-slate-900/70 backdrop-blur-md px-6 py-4 flex items-center justify-between">

                <div class="flex items-center gap-3">

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

            </header>

            <div class="w-full max-w-7xl mx-auto px-4 md:px-6 mt-4 mb-4">

                <div class="flex items-center gap-3">

                    <!-- SEARCH -->
                    <form method="GET" class="flex-1">
                        <div
                            class="flex items-center bg-white/10 border border-white/20 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">

                            <!-- ICON -->
                            <span class="px-3 text-gray-300">
                                <i class="fa fa-search"></i>
                            </span>

                            <!-- INPUT -->
                            <input name="search" value="{{ request('search') }}" type="text"
                                placeholder="Search user..." autocomplete="off"
                                class="flex-1 bg-transparent text-white px-2 py-2 outline-none placeholder-gray-300">

                            <!-- BUTTON -->
                            <button type="submit" class="px-3 py-2 text-gray-300 hover:text-white transition">
                                <i class="fa fa-search"></i>
                            </button>

                        </div>
                    </form>

                    <!-- BUTTON -->
                    <button onclick="openModal()"
                        class="h-[40px] bg-blue-600 hover:bg-blue-700 text-white px-4 rounded-lg text-sm whitespace-nowrap transition">
                        + Add User
                    </button>

                </div>

            </div>

            <div class="px-4 py-4 md:px-6 md:py-6">
                <div class="w-full max-w-7xl mx-auto">

                    <!-- STATS -->
                    <div class="card mb-6 flex items-center justify-between px-6 py-5">

                        <div>
                            <p class="text-gray-300 text-sm mb-1">
                                Total Users
                            </p>

                            <h2 class="text-4xl font-bold text-white">
                                {{ $totalUsers }}
                            </h2>
                        </div>

                        <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-500/20 text-blue-300">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>

                    </div>

                    <p class="text-sm text-gray-400 mt-2 mb-4">
                        Showing
                        <span class="text-white font-semibold">{{ $users->count() }}</span>
                        of
                        <span class="text-white font-semibold">{{ $totalUsers }}</span> users
                    </p>

                    <!-- MOBILE CARD -->
                    <div class="card">

                        <!-- MOBILE -->
                        <div class="block md:hidden space-y-3 px-2 max-h-[500px] overflow-y-auto">
                            @foreach ($users as $user)
                                <div
                                    class="bg-slate-800/70 border border-white/10 rounded-xl p-4 shadow-sm hover:shadow-md transition w-full max-w-sm mx-auto space-y-2">
                                    <p class="font-semibold text-base text-white leading-tight">
                                        {{ $user->name }}
                                    </p>

                                    <div class="mt-2 flex justify-between text-xs sm:text-sm">
                                        <span class="text-gray-300">Role</span>
                                        <span
                                            class="px-2 py-1 rounded-full text-xs
                                            {{ $user->role == 'admin' ? 'bg-blue-500/20 text-blue-300' : '' }}
                                            {{ $user->role == 'operator' ? 'bg-green-500/20 text-green-300' : '' }}
                                            {{ $user->role == 'user' ? 'bg-white/10 text-gray-300' : '' }}">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex justify-between text-sm">
                                        <span id="user-status-{{ $user->id }}"
                                            class="{{ $user->isOnline ? 'bg-green-500/20 text-green-300' : 'bg-gray-500/20 text-gray-300' }} px-2 py-1 rounded text-xs">
                                            {{ $user->isOnline ? 'Online' : 'Offline' }}
                                        </span>
                                    </div>

                                    <form action="/users/{{ $user->id }}" method="POST" class="mt-3">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Yakin hapus user ini?')"
                                            class="w-full bg-red-500 hover:bg-red-600 transition text-white py-2.5 rounded-lg text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            @endforeach
                        </div>

                        <!-- DESKTOP -->
                        <div class="hidden md:block overflow-x-auto max-h-[500px] overflow-y-auto">
                            <table class="w-full text-white">

                                <thead
                                    class="sticky top-0 bg-slate-900/90 backdrop-blur z-10 border-b border-white/10">
                                    <tr class="text-left text-gray-300 text-sm">
                                        <th class="p-3">Name</th>
                                        <th class="p-3">Role</th>
                                        <th class="p-3">Status</th>
                                        <th class="p-3">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($users as $user)
                                        <tr class="border-b hover:bg-white/5 transition">

                                            <td class="p-3 font-medium">{{ $user->name }}</td>

                                            <td class="p-3">
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs md:text-sm
                                                    {{ $user->role == 'admin' ? 'bg-white/10 text-blue-400' : '' }}
                                                    {{ $user->role == 'operator' ? 'bg-green-500/20 text-green-300' : '' }}
                                                    {{ $user->role == 'user' ? 'bg-white/10 text-gray-300' : '' }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                            </td>

                                            <td class="p-3">
                                                <span id="user-status-{{ $user->id }}"
                                                    class="{{ $user->isOnline ? 'bg-green-500/20 text-green-300' : 'bg-gray-500/20 text-gray-300' }} px-2 py-1 rounded text-xs">
                                                    {{ $user->isOnline ? 'Online' : 'Offline' }}
                                                </span>
                                            </td>

                                            <td class="p-3">
                                                <form action="/users/{{ $user->id }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('Yakin hapus user ini?')"
                                                        class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- MODAL -->
            <div id="modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center">

                <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg relative">

                    <!-- ❌ CLOSE BUTTON -->
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
                    fetch('/update-activity', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                }, 180000);

                let userStatusSource;

                function startUserStatusSSE() {

                    if (userStatusSource) userStatusSource.close();

                    userStatusSource = new EventSource('/users-status-stream');

                    userStatusSource.onmessage = function(event) {

                        const users = JSON.parse(event.data);

                        users.forEach(u => {

                            let el = document.getElementById('user-status-' + u.id);

                            if (!el) return;

                            if (u.online) {
                                el.innerText = "Online";
                                el.classList.remove('bg-gray-500/20', 'text-gray-300');
                                el.classList.add('bg-green-500/20', 'text-green-300');
                            } else {
                                el.innerText = "Offline";
                                el.classList.remove('bg-green-500/20', 'text-green-300');
                                el.classList.add('bg-gray-500/20', 'text-gray-300');
                            }

                        });
                    };

                    userStatusSource.onerror = function() {
                        console.log("Reconnect User Status SSE...");
                        userStatusSource.close();

                        if (navigator.onLine) {
                            setTimeout(startUserStatusSSE, 2000);
                        }
                    };
                }

                window.addEventListener("load", startUserStatusSSE);

                window.addEventListener("beforeunload", () => {
                    if (userStatusSource) userStatusSource.close();
                });

                document.addEventListener("visibilitychange", () => {
                    if (document.hidden && userStatusSource) {
                        userStatusSource.close();
                    } else if (!userStatusSource || userStatusSource.readyState === 2) {
                        startUserStatusSSE();
                    }
                });
            </script>

    </body>

    </html>
