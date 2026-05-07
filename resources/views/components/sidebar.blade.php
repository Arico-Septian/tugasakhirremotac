{{-- Shared Sidebar Component --}}
<div id="sidebar" class="sidebar bg-[#0c1628] text-white flex flex-col border-r border-white/5">

    {{-- HEADER --}}
    <div class="flex items-center justify-between px-4 h-16 border-b border-white/5 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-900/40">
                <i class="fa-solid fa-snowflake text-white text-sm"></i>
            </div>
            <div class="menu-text leading-tight">
                <p class="text-sm font-bold text-white">SmartAC</p>
                <p class="text-[10px] text-gray-500 font-medium">Control System</p>
            </div>
        </div>
        <button onclick="toggleSidebar()"
            class="sidebar-toggle hidden lg:flex w-7 h-7 items-center justify-center rounded-lg hover:bg-white/8 transition-colors text-gray-500 hover:text-gray-300">
            <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
    </div>

    {{-- NAV MENU --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

        <a href="{{ route('dashboard') }}"
            class="sidebar-nav-item menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie sidebar-icon"></i>
            <span class="menu-text sidebar-label">Dashboard</span>
        </a>

        @if (Auth::user()->role === 'user')
        <a href="{{ route('rooms.overview') }}"
            class="sidebar-nav-item menu-link {{ request()->routeIs('rooms.overview') ? 'active' : '' }}">
            <i class="fa-solid fa-server sidebar-icon"></i>
            <span class="menu-text sidebar-label">Room Status</span>
        </a>
        @endif

        @if (in_array(Auth::user()->role, ['admin', 'operator']))
        <a href="/rooms"
            class="sidebar-nav-item menu-link {{ request()->is('rooms*') && !request()->routeIs('rooms.overview') ? 'active' : '' }}">
            <i class="fa-solid fa-server sidebar-icon"></i>
            <span class="menu-text sidebar-label">Kelola Ruangan</span>
        </a>
        @endif

        @if (Auth::user()->role === 'admin')
        <a href="/users"
            class="sidebar-nav-item menu-link {{ request()->is('users*') ? 'active' : '' }}">
            <i class="fa-solid fa-users sidebar-icon"></i>
            <span class="menu-text sidebar-label">Manajemen User</span>
        </a>
        <a href="/logs"
            class="sidebar-nav-item menu-link {{ request()->is('logs*') ? 'active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left sidebar-icon"></i>
            <span class="menu-text sidebar-label">Activity Log</span>
        </a>
        @endif

        <a href="/profile"
            class="sidebar-nav-item menu-link {{ request()->is('profile*') ? 'active' : '' }}">
            <i class="fa-solid fa-circle-user sidebar-icon"></i>
            <span class="menu-text sidebar-label">Profile</span>
        </a>

    </nav>

    {{-- FOOTER --}}
    <div class="px-3 pb-4 pt-3 border-t border-white/5 flex-shrink-0">
        <div class="profile-full flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-white/5 transition-colors">
            <a href="/profile" class="flex-shrink-0">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-violet-600 flex items-center justify-center text-xs font-bold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </a>
            <a href="/profile" class="menu-text flex-1 min-w-0 hover:text-blue-300 transition-colors">
                <p class="text-xs font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-gray-500 capitalize">{{ Auth::user()->role }}</p>
            </a>
            <form action="/logout" method="POST" class="menu-text ml-auto">
                @csrf
                <button type="submit"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                </button>
            </form>
        </div>
        <div class="profile-collapse hidden text-center pt-1">
            <form action="/logout" method="POST">
                @csrf
                <button class="w-8 h-8 rounded-xl flex items-center justify-center mx-auto text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                </button>
            </form>
        </div>
    </div>

</div>
