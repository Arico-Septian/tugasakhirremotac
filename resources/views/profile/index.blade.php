<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>

    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

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

        .layout {
            display: flex;
            height: 100vh;
            width: 100vw;
            position: relative;
            z-index: 1;
        }

        .sidebar {
            width: 256px;
            flex-shrink: 0;
            position: fixed;
            top: 0; left: 0;
            height: 100%;
            z-index: 50;
            overflow: hidden;
            transition: width 0.25s ease;
        }
        .sidebar.close { width: 80px; }
        .sidebar.close .menu-text,
        .sidebar.close h2 span,
        .sidebar.close .profile-full { display: none; }
        .sidebar.close .profile-collapse { display: block; }
        .sidebar.close ul li a { justify-content: center; }

        .main-content {
            margin-left: 256px;
            width: calc(100% - 256px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        .sidebar.close ~ .main-content {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

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

        .page-body {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            padding-bottom: 60px;
        }

        .profile-card {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 28px;
        }

        .input-field {
            width: 100%;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 10px 14px;
            color: white;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-field:focus { border-color: #3b82f6; }
        .input-field::placeholder { color: #64748b; }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 256px !important;
                will-change: transform;
            }
            .sidebar.open { transform: translateX(0); }
        }

        button:active, a:active { transform: none !important; }

        .toast {
            position: fixed;
            bottom: 20px; right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        .toast.success { background: #22c55e; }
        .toast.error   { background: #ef4444; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
    </style>
</head>

<body class="custom-bg">
<div class="layout">

    <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

    <!-- ===== SIDEBAR ===== -->
    <div id="sidebar" class="sidebar bg-slate-900 text-white shadow-lg p-6 border-r border-white/10">

        <div class="flex justify-between items-center pb-5 mb-8 border-b border-white/10">
            <h2 class="text-xl font-bold text-blue-500 flex items-center gap-2">
                <i class="fa-solid fa-layer-group"></i>
                <span class="menu-text">AC System</span>
            </h2>
            <button onclick="toggleSidebar()" class="md:hidden text-gray-300">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <ul class="space-y-4">
            <li>
                <a href="{{ route('dashboard') }}"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 text-gray-300">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            @if (Auth::user()->role === 'user')
                <li>
                    <a href="{{ route('rooms.overview') }}"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 text-gray-300">
                        <i class="fa-solid fa-server"></i>
                        <span class="menu-text">Room Status</span>
                    </a>
                </li>
            @endif

            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                <li>
                    <a href="/rooms"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 text-gray-300">
                        <i class="fa-solid fa-server"></i>
                        <span class="menu-text">Manage Rooms & Ac Unit</span>
                    </a>
                </li>
            @endif

            @if (Auth::user()->role == 'admin')
                <li>
                    <a href="/users"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 text-gray-300">
                        <i class="fa-solid fa-users"></i>
                        <span class="menu-text">User Management</span>
                    </a>
                </li>
                <li>
                    <a href="/logs"
                        class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition hover:bg-white/10 text-gray-300">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="menu-text">Activity Log</span>
                    </a>
                </li>
            @endif

            <li>
                <a href="/profile"
                    class="menu-link flex items-center gap-3 px-4 py-3 rounded-xl transition
                    {{ request()->is('profile*') ? 'bg-white/10 text-white font-bold' : 'hover:bg-white/10 text-gray-300' }}">
                    <i class="fa-solid fa-circle-user"></i>
                    <span class="menu-text">Profile</span>
                </a>
            </li>
        </ul>

        <div class="absolute bottom-6 left-6 right-6">
            <div class="profile-full">
                <div class="w-full flex items-center gap-3 px-3 py-2">
                    <a href="/profile" class="flex-shrink-0 hover:opacity-80 transition">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-500
                                    flex items-center justify-center font-bold text-sm text-white">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </a>
                    <a href="/profile" class="text-left menu-text flex-1 min-w-0 hover:text-blue-300 transition">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ Auth::user()->role }}</p>
                    </a>
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
    <!-- ===== END SIDEBAR ===== -->

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">

        <header class="main-header">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="lg:hidden text-gray-300 text-lg p-1 rounded-lg hover:bg-white/10 transition">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1 class="text-base md:text-xl font-bold text-white leading-tight">My Profile</h1>
                    <p class="text-sm text-blue-200 font-medium">Account settings</p>
                </div>
            </div>
        </header>

        <div class="page-body">
            <div class="w-full max-w-lg mx-auto px-4 md:px-6 py-8">

                {{-- Avatar + Info --}}
                <div class="profile-card mb-6 flex items-center gap-5">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600
                                flex items-center justify-center text-2xl font-bold text-white flex-shrink-0 shadow-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                        <span class="inline-block mt-1 text-xs font-semibold px-3 py-1 rounded-full
                            {{ $user->role === 'admin' ? 'bg-red-500/20 text-red-300' :
                               ($user->role === 'operator' ? 'bg-yellow-500/20 text-yellow-300' : 'bg-blue-500/20 text-blue-300') }}">
                            {{ strtoupper($user->role) }}
                        </span>
                        @if ($user->last_activity)
                            <p class="text-xs text-gray-400 mt-1">
                                Last active: {{ $user->last_activity->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Change Password --}}
                <div class="profile-card">
                    <h3 class="text-base font-semibold text-white mb-5 flex items-center gap-2">
                        <i class="fa-solid fa-key text-blue-400"></i>
                        Change Password
                    </h3>

                    @if (session('success'))
                        <div class="mb-4 bg-green-500/20 border border-green-500/30 text-green-300
                                    text-sm px-4 py-3 rounded-xl flex items-center gap-2">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 bg-red-500/20 border border-red-500/30 text-red-300
                                    text-sm px-4 py-3 rounded-xl flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="/change-password" id="pwForm">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs text-gray-400 mb-1.5 font-medium">New Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="pwInput"
                                    class="input-field pr-10" placeholder="Min. 6 characters" required minlength="6">
                                <button type="button" onclick="togglePw('pwInput', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs text-gray-400 mb-1.5 font-medium">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" id="pwConfirm"
                                    class="input-field pr-10" placeholder="Repeat new password" required>
                                <button type="button" onclick="togglePw('pwConfirm', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="pwBtn"
                            class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl
                                   font-semibold text-sm transition">
                            Update Password
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
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

    document.getElementById('overlay')?.addEventListener('click', function () {
        document.getElementById('sidebar')?.classList.remove('open');
        this.classList.add('hidden');
    });

    function togglePw(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    document.getElementById('pwForm')?.addEventListener('submit', function (e) {
        const pw  = document.getElementById('pwInput').value;
        const pw2 = document.getElementById('pwConfirm').value;
        if (pw !== pw2) {
            e.preventDefault();
            const toast = document.createElement('div');
            toast.className = 'toast error';
            toast.textContent = 'Password dan konfirmasi tidak cocok';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    });

    // Idle timeout
    const role = "{{ Auth::check() ? Auth::user()->role : '' }}";
    const idleTime = role === 'admin' ? 10 * 60 * 1000 :
                     role === 'operator' ? 5 * 60 * 1000 : 2 * 60 * 1000;
    let idleTimeout;
    function resetTimer() {
        clearTimeout(idleTimeout);
        idleTimeout = setTimeout(() => {
            const form = document.createElement('form');
            form.method = 'POST'; form.action = '/logout'; form.style.display = 'none';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }, idleTime);
    }
    ['mousemove','keypress','click','scroll','touchstart'].forEach(e => document.addEventListener(e, resetTimer));
    document.addEventListener('DOMContentLoaded', resetTimer);

    document.querySelectorAll('.menu-link').forEach(link => {
        link.addEventListener('click', function (e) {
            if (window.innerWidth <= 1024) {
                e.preventDefault();
                document.getElementById('sidebar')?.classList.remove('open');
                document.getElementById('overlay')?.classList.add('hidden');
                setTimeout(() => { window.location.href = this.href; }, 250);
            }
        });
    });
</script>

</body>
</html>
