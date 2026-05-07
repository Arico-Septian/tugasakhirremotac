{{-- Mobile Bottom Navigation --}}
<style>
    .bottom-nav {
        display: none;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        height: 64px;
        background: rgba(12, 22, 40, 0.97);
        backdrop-filter: blur(16px);
        border-top: 1px solid rgba(255,255,255,0.06);
        z-index: 45;
        padding: 0 8px;
        justify-content: space-around;
        align-items: center;
        box-shadow: 0 -4px 24px rgba(0,0,0,0.4);
    }
    @media (max-width: 1024px) {
        .bottom-nav { display: flex; }
        .page-body  { padding-bottom: 80px !important; }
    }
    .bnav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        padding: 8px 16px;
        border-radius: 12px;
        color: #64748b;
        font-size: 10px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        min-width: 56px;
    }
    .bnav-item i { font-size: 18px; }
    .bnav-item.active {
        color: #60a5fa;
        background: rgba(59,130,246,0.1);
    }
    .bnav-item:active { transform: scale(0.93); }
</style>

<nav class="bottom-nav">
    <a href="{{ route('dashboard') }}"
        class="bnav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
    </a>

    @if (Auth::user()->role === 'user')
    <a href="{{ route('rooms.overview') }}"
        class="bnav-item {{ request()->routeIs('rooms.overview') || request()->is('rooms/*/status') ? 'active' : '' }}">
        <i class="fa-solid fa-server"></i>
        <span>Ruangan</span>
    </a>
    @endif

    @if (in_array(Auth::user()->role, ['admin','operator']))
    <a href="/rooms"
        class="bnav-item {{ request()->is('rooms*') && !request()->routeIs('rooms.overview') ? 'active' : '' }}">
        <i class="fa-solid fa-server"></i>
        <span>Ruangan</span>
    </a>
    @endif

    @if (Auth::user()->role === 'admin')
    <a href="/logs"
        class="bnav-item {{ request()->is('logs*') ? 'active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Log</span>
    </a>
    @endif

    <a href="/profile"
        class="bnav-item {{ request()->is('profile*') ? 'active' : '' }}">
        <i class="fa-solid fa-circle-user"></i>
        <span>Profile</span>
    </a>
</nav>
