<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AC Units - Management Control</title>

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
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
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

        /* ===== AC CARD ===== */
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

        /* ===== AC PANEL ===== */
        .ac-panel {
            transition: 0.25s ease;
            opacity: 0;
            transform: translateY(8px);
        }

        .ac-panel:not(.hidden) {
            opacity: 1;
            transform: translateY(0);
        }

        /* ===== MODE BUTTON ===== */
        .mode-btn {
            display: flex;
            width: 100%;
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
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }

        .mode-btn:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
        }

        .mode-btn.active {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* ===== DROPDOWN ===== */
        #dropdownAC {
            position: absolute;
            top: 60px;
            left: 0;
            width: 200px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: 0.25s ease;
            z-index: 40;
        }

        #dropdownAC.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* ===== OVERLAY ===== */
        #overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* ===== TOAST ===== */
        .toast {
            position: fixed;
            top: 20px;
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

        /* ===== LOADING STATE ===== */
        .loading {
            opacity: 0.6;
            pointer-events: none;
            cursor: wait;
        }

        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ===== RESPONSIVE: Sembunyikan status desktop di mobile ===== */
        @media (max-width: 768px) {
            .desktop-status {
                display: none !important;
            }

            .ac-card {
                padding: 16px;
            }
        }

        /* ===== MOBILE SIDEBAR ===== */
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

        /* ===== ACCESSIBILITY ===== */
        button:focus-visible,
        a:focus-visible,
        .mode-btn:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        button:active {
            transform: scale(0.98);
        }

        /* Hide scrollbar for cleaner look */
        .page-body::-webkit-scrollbar {
            width: 8px;
        }

        .page-body::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        .page-body::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 4px;
        }

        .page-body::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.8);
        }
    </style>
</head>

<body class="custom-bg">

    <!-- ==================== LAYOUT WRAPPER ==================== -->
    <div class="layout">

        <!-- OVERLAY (mobile) -->
        <div id="overlay" class="fixed inset-0 bg-black/50 z-40"></div>

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
                            {{ request()->is('users*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
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
                <div class="profile-full flex items-center gap-3 px-3 py-2">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="text-left menu-text">
                        <p class="text-sm font-semibold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ Auth::user()->role }}</p>
                    </div>
                    <form action="/logout" method="POST" class="ml-auto" id="logoutForm">
                        @csrf
                        <button type="submit" class="text-red-500 hover:text-red-600 text-lg">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
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
                        class="lg:hidden text-lg text-white p-1 rounded-lg hover:bg-white/10 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div>
                        <h1 class="text-base md:text-xl font-semibold text-white leading-none">
                            Management Control Ac
                        </h1>
                        <p class="text-sm text-blue-200 font-medium">
                            Centralized Ac
                        </p>
                    </div>
                </div>
            </header>
            <!-- END HEADER -->

            <!-- PAGE BODY -->
            <div class="page-body">
                <div class="px-2 pr-3 md:px-6 py-4">

                    @php $firstAc = $acs->first(); @endphp

                    <!-- AC SELECTOR BAR -->
                    <div
                        class="relative flex items-center justify-between h-[64px] px-4 pr-3 md:px-6 mb-2 rounded-xl shadow-md border border-white/10 bg-slate-900/50">

                        <!-- Left: Dropdown + Room Name -->
                        <div class="flex items-center gap-2 min-w-0">
                            <div onclick="toggleDropdown()"
                                class="flex items-center gap-2 px-2 py-2 text-xs md:text-sm bg-slate-800 text-white border border-white/10 rounded-xl cursor-pointer hover:bg-slate-700 transition">
                                <span id="selectedAC" class="font-medium text-white">
                                    {{ $firstAc ? 'AC ' . $firstAc->ac_number . ' ' . $firstAc->name : 'No AC' }}
                                </span>
                                <i class="fa-solid fa-chevron-down text-xs text-white"></i>
                            </div>
                            <span class="text-white tracking-widest text-xs md:text-sm">
                                {{ strtoupper($room->name) }}
                            </span>
                        </div>

                        <!-- Right: Add + Delete -->
                        <div class="flex items-center gap-2 md:gap-3 shrink-0 pr-1">
                            @auth
                                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                    <button {{ $acs->count() >= 15 ? 'disabled' : '' }}
                                        onclick="{{ $acs->count() >= 15 ? '' : 'openModal()' }}"
                                        class="bg-blue-600 text-white px-3 py-1.5 text-xs rounded-lg flex items-center transition hover:bg-blue-700
                                        {{ $acs->count() >= 15 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        + Add AC
                                    </button>

                                    <form id="deleteForm" method="POST" onsubmit="return confirmDelete(event)"
                                        action="{{ $firstAc ? '/ac/' . $firstAc->id : '' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" {{ !$firstAc ? 'disabled' : '' }}
                                            class="w-8 h-8 flex items-center justify-center rounded-full border border-red-400 text-red-500 hover:bg-red-500/10 transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        <!-- Dropdown -->
                        <div id="dropdownAC" class="absolute">
                            @foreach ($acs as $ac)
                                <div data-id="{{ $ac->id }}"
                                    onclick="selectAC({{ $ac->id }}, 'AC {{ $ac->ac_number }} {{ $ac->name }}')"
                                    class="px-4 py-3 hover:bg-blue-500/20 cursor-pointer transition whitespace-nowrap">
                                    AC {{ $ac->ac_number }} {{ $ac->name }}
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <!-- END AC SELECTOR BAR -->

                    <!-- AC PANELS -->
                    <div class="p-2 md:p-8 pt-2">
                        @foreach ($acs as $ac)
                            <div id="ac-{{ $ac->id }}" class="ac-panel {{ $loop->first ? '' : 'hidden' }}">
                                <div class="grid grid-cols-1 md:grid-cols-[280px_1fr] lg:grid-cols-[350px_1fr] gap-4">

                                    <!-- STATUS MOBILE ONLY (di atas) -->
                                    <div class="grid grid-cols-2 gap-2 mb-4 md:hidden">
                                        <div class="ac-card text-center py-2">
                                            <p class="text-xs text-gray-400">POWER</p>
                                            <p class="text-sm font-semibold text-green-400">
                                                {{ $ac->status?->power ?? 'OFF' }}
                                            </p>
                                        </div>
                                        <div class="ac-card text-center py-2">
                                            <p class="text-xs text-gray-400">TEMP</p>
                                            <p class="text-sm font-semibold text-yellow-300">
                                                {{ $ac->status?->set_temperature ?? 24 }}°C
                                            </p>
                                        </div>
                                        <div class="ac-card text-center py-2">
                                            <p class="text-xs text-gray-400">MODE</p>
                                            <p class="text-sm font-semibold text-blue-400">
                                                {{ strtoupper($ac->status?->mode ?? 'cool') }}
                                            </p>
                                        </div>
                                        <div class="ac-card text-center py-2">
                                            <p class="text-xs text-gray-400">FAN</p>
                                            <p class="text-sm font-semibold text-cyan-300">
                                                {{ strtoupper($ac->status?->fan_speed ?? 'AUTO') }}
                                            </p>
                                        </div>
                                        <div class="ac-card text-center py-2 col-span-2">
                                            <p class="text-xs text-gray-400">SWING</p>
                                            <p class="text-sm font-semibold text-purple-300">
                                                {{ strtoupper($ac->status?->swing ?? 'OFF') }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- LEFT: Power + Temp -->
                                    <div
                                        class="ac-card p-5 md:p-8 flex flex-col items-center justify-center text-center">
                                        <form action="/ac/{{ $ac->id }}/toggle" method="POST"
                                            class="power-form">
                                            @csrf
                                            <button type="submit"
                                                class="power-btn w-16 h-16 md:w-20 md:h-20 rounded-full flex items-center justify-center
                                                {{ $ac->status && $ac->status?->power == 'ON'
                                                    ? 'bg-green-500 shadow-green-300 ring-4 ring-green-200'
                                                    : 'bg-gray-500' }} text-white text-2xl shadow-lg hover:opacity-80 transition">
                                                <i class="fa-solid fa-power-off"></i>
                                            </button>
                                        </form>

                                        <div class="mt-6">
                                            <div class="text-3xl md:text-6xl font-bold text-blue-400">
                                                <span
                                                    class="temp-value">{{ $ac->status?->set_temperature ?? 24 }}</span>°C
                                            </div>
                                            <p class="text-xs text-white tracking-widest mt-2">TEMPERATURE</p>
                                        </div>

                                        <div class="flex gap-3 md:gap-6 mt-6">
                                            <button
                                                type="button"
                                                onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) - 1 }})"
                                                class="temp-btn w-10 h-10 md:w-12 md:h-12 bg-slate-700 text-white rounded-full text-xl hover:bg-slate-600 transition">
                                                −
                                            </button>
                                            <button
                                                type="button"
                                                onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) + 1 }})"
                                                class="temp-btn w-10 h-10 md:w-12 md:h-12 bg-blue-600 text-white rounded-full text-xl hover:bg-blue-700 transition">
                                                +
                                            </button>
                                        </div>
                                    </div>

                                    <!-- RIGHT: Info + Mode + Timer -->
                                    <div class="flex flex-col gap-4">
                                        <!-- STATUS DESKTOP ONLY (sembunyikan di mobile) -->
                                        <div class="desktop-status grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                                            <div class="ac-card text-center py-3">
                                                <p class="text-xs text-gray-400">POWER</p>
                                                <p class="text-lg font-semibold text-green-400">
                                                    {{ $ac->status?->power ?? 'OFF' }}
                                                </p>
                                            </div>
                                            <div class="ac-card text-center py-3">
                                                <p class="text-xs text-gray-400">SET TEMP</p>
                                                <p class="text-lg font-semibold text-yellow-300">
                                                    {{ $ac->status?->set_temperature ?? 24 }}°C
                                                </p>
                                            </div>
                                            <div class="ac-card text-center py-3">
                                                <p class="text-xs text-gray-400">MODE</p>
                                                <p class="text-lg font-semibold text-blue-400">
                                                    {{ strtoupper($ac->status?->mode ?? 'cool') }}
                                                </p>
                                            </div>
                                            <div class="ac-card text-center py-3">
                                                <p class="text-xs text-gray-400">FAN SPEED</p>
                                                <p class="text-lg font-semibold text-cyan-300">
                                                    {{ strtoupper($ac->status?->fan_speed ?? 'AUTO') }}
                                                </p>
                                            </div>
                                            <div class="ac-card text-center py-3">
                                                <p class="text-xs text-gray-400">SWING</p>
                                                <p class="text-lg font-semibold text-purple-300">
                                                    {{ strtoupper($ac->status?->swing ?? 'OFF') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Mode Selection -->
                                        <div class="ac-card">
                                            <p class="text-gray-400 mb-4 text-sm">MODE</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                                @foreach ([
        'cool' => ['fa-snowflake', 'Cool'],
        'heat' => ['fa-fire', 'Heat'],
        'dry' => ['fa-droplet', 'Dry'],
        'fan' => ['fa-fan', 'Fan'],
        'auto' => ['fa-rotate', 'Auto'],
    ] as $mode => [$icon, $label])
                                                    <form action="/ac/{{ $ac->id }}/mode/{{ $mode }}"
                                                        method="POST" class="control-form">
                                                        @csrf
                                                        <button type="submit"
                                                            class="mode-btn {{ strtoupper($ac->status?->mode ?? 'cool') == strtoupper($mode) ? 'active' : '' }}">
                                                            <i class="fa-solid {{ $icon }}"></i>
                                                            {{ $label }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Fan Speed Selection -->
                                        <div class="ac-card">
                                            <p class="text-gray-400 mb-4 text-sm">FAN SPEED</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                                @foreach ([
        'auto' => ['fa-fan', 'Auto'],
        'low' => ['fa-wind', 'Low'],
        'medium' => ['fa-gauge-simple', 'Medium'],
        'high' => ['fa-gauge-high', 'High'],
    ] as $speed => [$icon, $label])
                                                    <form action="/ac/{{ $ac->id }}/fan-speed/{{ $speed }}"
                                                        method="POST" class="control-form">
                                                        @csrf
                                                        <button type="submit"
                                                            class="mode-btn {{ strtoupper($ac->status?->fan_speed ?? 'AUTO') == strtoupper($speed) ? 'active' : '' }}">
                                                            <i class="fa-solid {{ $icon }}"></i>
                                                            {{ $label }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Swing Selection -->
                                        <div class="ac-card">
                                            <p class="text-gray-400 mb-4 text-sm">SWING</p>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                                @foreach ([
        'off' => ['fa-ban', 'Diam'],
        'full' => ['fa-arrows-up-down', 'Full'],
        'half' => ['fa-compress', 'Setengah'],
        'down' => ['fa-arrow-down', 'Ke Bawah'],
    ] as $swing => [$icon, $label])
                                                    <form action="/ac/{{ $ac->id }}/swing/{{ $swing }}"
                                                        method="POST" class="control-form">
                                                        @csrf
                                                        <button type="submit"
                                                            class="mode-btn {{ strtoupper($ac->status?->swing ?? 'OFF') == strtoupper($swing) ? 'active' : '' }}">
                                                            <i class="fa-solid {{ $icon }}"></i>
                                                            {{ $label }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Timer Schedule -->
                                        <div class="ac-card">
                                            <div class="flex justify-between items-center mb-4">
                                                <div class="flex items-center gap-2 text-blue-400">
                                                    <i class="fa-solid fa-clock"></i>
                                                    <span class="font-medium">Timer Schedule</span>
                                                </div>
                                                <button id="btnTimer-{{ $ac->id }}"
                                                    onclick="toggleTimer({{ $ac->id }})"
                                                    class="bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-sm font-medium hover:bg-blue-200 transition">
                                                    Set Timer
                                                </button>
                                            </div>

                                            <!-- Timer View -->
                                            <div id="timerView-{{ $ac->id }}">
                                                @if ($ac->timer_on || $ac->timer_off)
                                                    <p class="text-gray-300 text-sm">
                                                        ON:
                                                        {{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') : '--:--' }}
                                                        &nbsp;&nbsp;
                                                        OFF:
                                                        {{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') : '--:--' }}
                                                    </p>
                                                    @if ($ac->timer_on)
                                                        <p class="text-green-500 text-xs mt-1">✓ Timer ON aktif</p>
                                                    @elseif ($ac->timer_off)
                                                        <p class="text-yellow-500 text-xs mt-1">⏰ Timer OFF aktif</p>
                                                    @endif
                                                @else
                                                    <p class="text-white text-sm">No timer set</p>
                                                @endif
                                            </div>

                                            <!-- Timer Edit Form -->
                                            <form id="timerEdit-{{ $ac->id }}" class="hidden timer-form"
                                                action="/ac/{{ $ac->id }}/schedule" method="POST">
                                                @csrf
                                                <input type="hidden" name="ac_id" value="{{ $ac->id }}">
                                                <div class="grid grid-cols-2 gap-4 mb-4">
                                                    <div>
                                                        <p class="text-xs text-white mb-1">TURN ON</p>
                                                        <input type="time" name="timer_on"
                                                            value="{{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->format('H:i') : '' }}"
                                                            class="w-full bg-slate-800 text-white border border-white/10 rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-white mb-1">TURN OFF</p>
                                                        <input type="time" name="timer_off"
                                                            value="{{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->format('H:i') : '' }}"
                                                            class="w-full bg-slate-800 text-white border border-white/10 rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none">
                                                    </div>
                                                </div>
                                                <div class="flex gap-3">
                                                    <button type="submit"
                                                        class="save-timer-btn flex-1 bg-blue-600 text-white py-3 rounded-full hover:bg-blue-700 transition">
                                                        ✓ Save
                                                    </button>
                                                    <button type="button" onclick="toggleTimer({{ $ac->id }})"
                                                        class="flex-1 bg-gray-200 text-gray-800 py-3 rounded-full hover:bg-gray-300 transition">
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
        </div>
    </div>

    <!-- MODAL ADD AC -->
    @auth
        @if (in_array(Auth::user()->role, ['admin', 'operator']))
            <div id="modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
                <div class="bg-white p-8 rounded-2xl w-96 shadow-lg relative animate-[fadeIn_0.3s_ease]">
                    <button onclick="closeModal()"
                        class="absolute top-4 right-4 text-gray-500 text-2xl hover:text-red-500 transition">✕</button>
                    <h2 class="text-xl font-bold mb-5">Add New AC</h2>
                    <form id="addACForm" method="POST" action="/rooms/{{ $room->id }}/ac">
                        @csrf
                        <input type="number" name="ac_number" placeholder="AC Number" required
                            class="border p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">
                        <input type="text" name="name" placeholder="AC Name" required
                            class="border p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">
                        <input type="text" name="brand" placeholder="Brand" required
                            class="border p-3 w-full mb-4 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">
                        <button type="submit"
                            class="bg-blue-600 text-white w-full py-2 rounded-lg hover:bg-blue-700 transition">
                            Create AC
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <script>
        // ==================== UTILITY FUNCTIONS ====================

        // Show toast notification
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

        // Show loading state on button
        function showLoading(button) {
            const originalText = button.textContent;
            button.classList.add('btn-loading');
            button.disabled = true;
            button.setAttribute('data-original-text', originalText);
            button.textContent = '';
        }

        function hideLoading(button) {
            button.classList.remove('btn-loading');
            button.disabled = false;
            const originalText = button.getAttribute('data-original-text');
            if (originalText) button.textContent = originalText;
        }

        // ==================== MODAL FUNCTIONS ====================
        function openModal() {
            const acCount = {{ $acs->count() }};
            if (acCount >= 15) {
                showToast('Maksimal 15 AC sudah tercapai', 'error');
                return;
            }
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
                const form = document.querySelector('#modal form');
                if (form) form.reset();
            }
        }

        // Close modal when clicking outside
        document.getElementById('modal')?.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // ==================== TEMPERATURE CONTROL ====================
        function setTemp(id, temp) {
            if (temp < 16) temp = 16;
            if (temp > 30) temp = 30;

            // Show loading state on temp buttons
            const buttons = document.querySelectorAll(`#ac-${id} .temp-btn`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.5';
            });

            const form = document.createElement('form');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            form.method = 'POST';
            form.action = '/ac/' + id + '/temp/' + temp;

            if (token) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = token;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        // ==================== TIMER FUNCTIONS ====================
        function toggleTimer(id) {
            const view = document.getElementById('timerView-' + id);
            const edit = document.getElementById('timerEdit-' + id);
            const btn = document.getElementById('btnTimer-' + id);

            if (!view || !edit || !btn) {
                console.error('Timer elements not found for ID:', id);
                return;
            }

            const isEditHidden = edit.classList.contains('hidden');

            if (isEditHidden) {
                view.classList.add('hidden');
                edit.classList.remove('hidden');
                btn.textContent = 'Cancel';
            } else {
                view.classList.remove('hidden');
                edit.classList.add('hidden');
                btn.textContent = 'Set Timer';
            }
        }

        // Timer form validation
        document.querySelectorAll('.timer-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const timerOn = this.querySelector('[name="timer_on"]').value;
                const timerOff = this.querySelector('[name="timer_off"]').value;

                if (timerOn === timerOff && timerOn !== '') {
                    e.preventDefault();
                    showToast('Timer ON and OFF cannot be the same time', 'error');
                    return false;
                }

                const submitBtn = this.querySelector('.save-timer-btn');
                if (submitBtn) {
                    showLoading(submitBtn);
                }
            });
        });

        // ==================== DROPDOWN FUNCTIONS ====================
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownAC');
            if (!dropdown) return;

            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            } else {
                dropdown.classList.add('show');
            }
        }

        function selectAC(id, name) {
            try {
                localStorage.setItem('selectedAC', id);
                const selectedSpan = document.getElementById('selectedAC');
                if (selectedSpan) selectedSpan.innerText = name;

                // Hide all panels
                document.querySelectorAll('.ac-panel').forEach(el => {
                    el.classList.add('hidden');
                });

                // Show selected panel
                const target = document.getElementById('ac-' + id);
                if (target) {
                    target.classList.remove('hidden');
                } else {
                    console.error('AC panel not found:', id);
                    showToast('Error loading AC data', 'error');
                }

                // Update delete form action
                const deleteForm = document.getElementById('deleteForm');
                if (deleteForm) {
                    deleteForm.action = '/ac/' + id;
                }

                // Close dropdown
                const dropdown = document.getElementById('dropdownAC');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            } catch (error) {
                console.error('Error in selectAC:', error);
                showToast('An error occurred', 'error');
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('dropdownAC');
            const trigger = document.querySelector('#selectedAC')?.parentElement;

            if (dropdown && trigger) {
                if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            }
        });

        // ==================== DELETE CONFIRMATION ====================
        function confirmDelete(event) {
            event.preventDefault();

            if (confirm('Apakah Anda yakin ingin menghapus AC ini? Tindakan ini tidak dapat dibatalkan.')) {
                const form = event.target;
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    showLoading(submitBtn);
                }
                form.submit();
            }
            return false;
        }

        // ==================== POWER BUTTON HANDLER ====================
        document.querySelectorAll('.power-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = this.querySelector('.power-btn');
                if (btn) {
                    showLoading(btn);
                }
            });
        });

        // ==================== AC CONTROL BUTTON HANDLER ====================
        document.querySelectorAll('.control-form').forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('.mode-btn');

                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
                    btn.style.opacity = '0.7';
                    btn.style.pointerEvents = 'none';
                }
            });
        });

        // ==================== INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Handle session new AC
            @if (session('new_ac_id'))
                const id = "{{ session('new_ac_id') }}";
                localStorage.setItem('selectedAC', id);
                const el = document.querySelector(`#dropdownAC div[data-id="${id}"]`);
                const name = el ? el.innerText.trim() :
                    "{{ $firstAc ? 'AC ' . $firstAc->ac_number . ' ' . $firstAc->name : 'No AC' }}";
                selectAC(id, name);

                // Show success toast
                @if (session('success'))
                    showToast("{{ session('success') }}", 'success');
                @endif
            @else
                // Load saved AC selection
                const saved = localStorage.getItem('selectedAC');
                if (saved && document.getElementById('ac-' + saved)) {
                    const el = document.querySelector(`#dropdownAC div[data-id="${saved}"]`);
                    const name = el ? el.innerText.trim() :
                        "{{ $firstAc ? 'AC ' . $firstAc->ac_number . ' ' . $firstAc->name : 'No AC' }}";
                    selectAC(saved, name);
                } else {
                    localStorage.removeItem('selectedAC');
                    @if ($firstAc)
                        selectAC({{ $firstAc->id }}, "{{ 'AC ' . $firstAc->ac_number . ' ' . $firstAc->name }}");
                    @endif
                }
            @endif

            // Show any session messages
            @if (session('success') && !session('new_ac_id'))
                showToast("{{ session('success') }}", 'success');
            @endif

            @if (session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            @if ($errors->any())
                showToast("{{ $errors->first() }}", 'error');
            @endif
        });

        // ==================== MOBILE SIDEBAR ====================
        // Toggle sidebar on mobile (you can add a hamburger button)
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (sidebar && overlay) {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('show');
                document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
            }
        }

        // Close sidebar when clicking overlay
        document.getElementById('overlay')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                this.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Menu link handler for mobile
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 1024) {
                    e.preventDefault();
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('overlay');

                    if (sidebar) sidebar.classList.remove('open');
                    if (overlay) overlay.classList.remove('show');
                    document.body.style.overflow = '';

                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 250);
                }
            });
        });

        // Go back function
        function goBack() {
            if (window.innerWidth <= 1024) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '/rooms';
                }
            }
        }

        // Add keyboard shortcut for Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                const dropdown = document.getElementById('dropdownAC');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });

        // Prevent accidental form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>
