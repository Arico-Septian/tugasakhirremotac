<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management</title>

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

        /* ===== HEADER ===== */
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

        /* ===== PAGE BODY ===== */
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

        /* Custom Scrollbar */
        .page-body::-webkit-scrollbar {
            width: 6px;
        }

        .page-body::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .page-body::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 10px;
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

        /* ===== MODERN USER LIST STYLES ===== */
        .user-row {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .user-row:hover {
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.05));
            transform: translateX(4px);
        }

        /* Avatar Gradient */
        .avatar-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .avatar-gradient.admin {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .avatar-gradient.operator {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .avatar-gradient.user {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        /* Role Badges Modern */
        .role-badge {
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(4px);
        }

        .role-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .role-badge:hover::before {
            left: 100%;
        }

        /* Action Buttons */
        .action-btn {
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn:active {
            transform: scale(0.95);
        }

        /* Tooltip */
        .tooltip {
            position: relative;
        }

        .tooltip::before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(-8px);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            pointer-events: none;
            z-index: 10;
        }

        .tooltip:hover::before {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-4px);
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
            font-weight: 500;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

        /* Select dropdown dark mode */
        select {
            background-color: rgb(30, 41, 59);
            color: white;
            border-color: rgba(255, 255, 255, 0.2);
        }

        select option {
            background-color: rgb(30, 41, 59);
            color: white;
        }

        /* Pagination Modern */
        .pagination {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 14px;
            border-radius: 12px;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .pagination a:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
        }

        .pagination .active {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
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

            <!-- PAGE BODY -->
            <div class="page-body">
                <div class="px-4 py-4 md:px-6 md:py-6">

                    <!-- STATS -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">

                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">TOTAL USERS</p>
                                    <h2 class="text-3xl font-bold text-white">{{ $totalUsers }}</h2>
                                    <p class="text-green-400 text-sm mt-1">{{ $newUsersThisWeek ?? 0 }} this week</p>
                                </div>
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-blue-500/20 text-blue-300">
                                    <i class="fa-solid fa-users text-lg"></i>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-gradient-to-r from-slate-900/80 to-slate-800/60 p-6 rounded-2xl border border-white/10 backdrop-blur">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">ONLINE NOW</p>
                                    <h2 class="text-3xl font-bold text-white">{{ $onlineUsers }}</h2>
                                    <p class="text-green-400 text-sm mt-1">{{ $onlinePercentage }}% active</p>
                                </div>
                                <div
                                    class="w-12 h-12 flex items-center justify-center rounded-xl bg-green-500/20 text-green-300">
                                    <i class="fa-solid fa-user-check text-lg"></i>
                                </div>
                            </div>
                        </div>

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
                        <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-white/10">

                            <form method="GET" action="/users" class="flex-1 min-w-[200px]">
                                <div
                                    class="flex items-center bg-slate-800/80 border border-white/10 rounded-xl px-4 py-2.5 gap-2">
                                    <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm shrink-0"></i>
                                    <input name="search" value="{{ request('search') }}"
                                        placeholder="Search by username..." autocomplete="off"
                                        class="bg-transparent text-white text-sm outline-none w-full placeholder-gray-400">
                                </div>
                            </form>

                            <div class="flex flex-wrap items-center gap-1">
                                <a href="/users"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap
                                    {{ !request('role') ? 'text-white bg-white/10' : 'text-gray-400 hover:text-white' }}">
                                    All
                                </a>
                                <a href="/users?role=admin"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap
                                    {{ request('role') == 'admin' ? 'text-white bg-white/10' : 'text-gray-400 hover:text-white' }}">
                                    Admin
                                </a>
                                <a href="/users?role=operator"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap
                                    {{ request('role') == 'operator' ? 'text-white bg-white/10' : 'text-gray-400 hover:text-white' }}">
                                    Operator
                                </a>
                                <a href="/users?role=user"
                                    class="px-3 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap
                                    {{ request('role') == 'user' ? 'text-white bg-white/10' : 'text-gray-400 hover:text-white' }}">
                                    User
                                </a>
                            </div>

                            <button onclick="openModal()"
                                class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition shrink-0">
                                <i class="fa-solid fa-user-plus"></i>
                                Add User
                            </button>

                        </div>
                        <!-- END TOP BAR -->

                        <!-- INFO BAR -->
                        <div class="flex justify-between items-center px-5 py-3 border-b border-white/10 bg-white/5">
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

                        <!-- ==================== MODERN USER LIST ==================== -->
                        <div id="user-list">
                            @forelse ($users as $user)
                                @php
                                    $isOnline = $user->isOnline ?? false;
                                    $statusText = $isOnline
                                        ? 'Online'
                                        : ($user->last_activity
                                            ? \Carbon\Carbon::parse($user->last_activity)->diffForHumans()
                                            : 'Offline');
                                    $statusColor = $isOnline ? 'text-green-400' : 'text-gray-500';
                                    $statusDotColor = $isOnline ? 'bg-green-500' : 'bg-gray-500';
                                    $avatarClass =
                                        $user->role == 'admin'
                                            ? 'admin'
                                            : ($user->role == 'operator'
                                                ? 'operator'
                                                : 'user');
                                @endphp

                                <div
                                    class="user-row flex flex-wrap items-center justify-between gap-4 px-5 py-4 border-b border-white/5">

                                    <!-- Left Section: Avatar + User Info -->
                                    <div class="flex items-center gap-4 flex-1 min-w-[200px]">
                                        <!-- Modern Avatar with Status -->
                                        <div class="relative">
                                            <div
                                                class="w-12 h-12 rounded-full avatar-gradient {{ $avatarClass }} flex items-center justify-center text-white font-bold text-sm shadow-lg">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <!-- Status Indicator -->
                                            <div class="absolute -bottom-0.5 -right-0.5">
                                                <div class="relative">
                                                    <span
                                                        class="block w-3.5 h-3.5 rounded-full {{ $statusDotColor }} ring-2 ring-slate-900"></span>
                                                    @if ($isOnline)
                                                        <span
                                                            class="absolute inset-0 w-full h-full rounded-full bg-green-500 animate-ping opacity-75"></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- User Details -->
                                        <div class="min-w-0">
                                            <p class="text-white font-semibold text-base leading-tight">
                                                {{ $user->name }}
                                            </p>
                                            <p class="text-gray-500 text-xs mt-1 flex items-center gap-1">
                                                <i class="fa-regular fa-envelope text-[10px]"></i>
                                                {{ '@' . strtolower(str_replace(' ', '', $user->name)) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Right Section: Role + Status + Actions -->
                                    <div class="flex items-center gap-3 flex-wrap">

                                        <!-- Modern Role Badge -->
                                        @if ($user->role == 'admin')
                                            <span
                                                class="role-badge inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 shadow-sm">
                                                <i class="fa-solid fa-crown text-[10px]"></i>
                                                ADMINISTRATOR
                                            </span>
                                        @elseif ($user->role == 'operator')
                                            <span
                                                class="role-badge inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 shadow-sm">
                                                <i class="fa-solid fa-gear text-[10px]"></i>
                                                OPERATOR
                                            </span>
                                        @else
                                            <span
                                                class="role-badge inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                                <i class="fa-regular fa-user text-[10px]"></i>
                                                USER
                                            </span>
                                        @endif

                                        <!-- Status with Icon -->
                                        <div class="flex items-center gap-2 px-2 py-1 rounded-lg bg-white/5">
                                            @if ($isOnline)
                                                <i
                                                    class="fa-solid fa-circle text-[8px] text-green-500 animate-pulse"></i>
                                            @else
                                                <i class="fa-solid fa-circle text-[8px] text-gray-500"></i>
                                            @endif
                                            <span
                                                class="text-xs {{ $statusColor }} font-medium">{{ $statusText }}</span>
                                        </div>

                                        <!-- Action Buttons with Tooltips -->
                                        @if ($user->id !== Auth::user()->id)
                                            <button onclick="editRole({{ $user->id }}, '{{ $user->role }}')"
                                                class="action-btn tooltip w-9 h-9 flex items-center justify-center text-blue-400 hover:text-blue-300 rounded-xl hover:bg-blue-500/20 transition-all duration-200"
                                                data-tooltip="Edit Role">
                                                <i class="fa-solid fa-pen text-sm"></i>
                                            </button>

                                            <button onclick="deleteUser({{ $user->id }})"
                                                class="action-btn tooltip w-9 h-9 flex items-center justify-center text-red-400 hover:text-red-300 rounded-xl hover:bg-red-500/20 transition-all duration-200"
                                                data-tooltip="Delete User">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-16">
                                    <div
                                        class="w-20 h-20 mx-auto mb-4 rounded-full bg-white/5 flex items-center justify-center">
                                        <i class="fa-solid fa-users text-3xl text-gray-500"></i>
                                    </div>
                                    <p class="text-gray-400 font-medium">No users found</p>
                                    <p class="text-sm text-gray-500 mt-1">Try adjusting your search or filter</p>
                                </div>
                            @endforelse

                            <!-- Modern Pagination -->
                            @if ($users->hasPages())
                                <div class="px-5 py-4 border-t border-white/10 bg-white/5">
                                    <div class="pagination">
                                        {{ $users->links() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <!-- ==================== END USER LIST ==================== -->

                    </div>
                    <!-- END USER CARD -->

                </div>
            </div>
            <!-- END PAGE BODY -->

        </div>
        <!-- ==================== END MAIN CONTENT ==================== -->

    </div>
    <!-- ==================== END LAYOUT WRAPPER ==================== -->

    <!-- ==================== MODAL ADD USER ==================== -->
    <div id="modal"
        class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg relative">
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl transition">✕</button>
            <h2 class="text-lg md:text-xl font-semibold text-white mb-5">Add New User</h2>
            <form id="addUserForm" method="POST" action="/users">
                @csrf
                <input type="text" name="name" placeholder="Name"
                    class="bg-slate-800 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
                <input type="password" name="password" placeholder="Password"
                    class="bg-slate-800 border border-white/20 text-white p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
                <div class="relative mb-4">
                    <select name="role"
                        class="w-full p-3 pr-10 rounded-lg border border-white/20 bg-slate-800 text-white focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="user" selected>User</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-sm"></i>
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2.5 rounded-lg shadow transition">Create
                    User</button>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL EDIT ROLE ==================== -->
    <div id="editRoleModal"
        class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-slate-900 text-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg relative">
            <button onclick="closeEditModal()"
                class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-xl transition">✕</button>
            <h2 class="text-lg md:text-xl font-semibold text-white mb-5">Edit User Role</h2>
            <form id="editRoleForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="relative mb-4">
                    <select name="role" id="edit_user_role"
                        class="w-full p-3 pr-10 rounded-lg border border-white/20 bg-slate-800 text-white focus:ring-2 focus:ring-blue-500 outline-none appearance-none cursor-pointer">
                        <option value="admin">Admin</option>
                        <option value="operator">Operator</option>
                        <option value="user">User</option>
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-chevron-down text-sm"></i>
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2.5 rounded-lg shadow transition">Update
                    Role</button>
            </form>
        </div>
    </div>

    <!-- ===== SCRIPTS ===== -->
    <script>
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
            const sidebar = document.getElementById('sidebar');
            if (sidebar && window.innerWidth <= 1024) {
                sidebar.classList.remove('open');
                this.classList.add('hidden');
            }
        };

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
                const form = modal.querySelector('form');
                if (form) form.reset();
            }
        }

        function openEditModal(userId, currentRole) {
            const modal = document.getElementById('editRoleModal');
            if (modal) {
                document.getElementById('edit_user_id').value = userId;
                document.getElementById('edit_user_role').value = currentRole;
                document.getElementById('editRoleForm').action = `/users/${userId}`;
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editRoleModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }

        function editRole(userId, currentRole) {
            openEditModal(userId, currentRole);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeEditModal();
            }
        });

        document.getElementById('modal')?.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        document.getElementById('editRoleModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        function deleteUser(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')) return;
            fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Delete failed');
                    return res.json();
                })
                .then(() => {
                    showToast('User deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    showToast('Failed to delete user', 'error');
                });
        }

        let pingInterval = null;

        function startActivityPing() {
            if (pingInterval) clearInterval(pingInterval);
            pingInterval = setInterval(() => {
                if (!document.hidden) {
                    fetch('/update-activity', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    }).catch(err => console.error('Activity ping failed:', err));
                }
            }, 60000);
        }

        const role = "{{ Auth::check() ? Auth::user()->role : '' }}";
        const idleTime = role === 'admin' ? 10 * 60 * 1000 : role === 'operator' ? 5 * 60 * 1000 : 2 * 60 * 1000;
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

        document.addEventListener('DOMContentLoaded', function() {
            startActivityPing();
            resetIdleTimer();
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

        const events = ['mousemove', 'keypress', 'click', 'scroll', 'touchstart'];
        events.forEach(event => {
            document.addEventListener(event, resetIdleTimer);
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) resetIdleTimer();
        });
        window.addEventListener('beforeunload', () => {
            if (pingInterval) clearInterval(pingInterval);
            if (idleTimeout) clearTimeout(idleTimeout);
        });
    </script>
</body>

</html>
