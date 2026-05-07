<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ruangan — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.sidebar-styles')
    <style>
        .room-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-top: 2px solid transparent;
            border-radius: 16px;
            padding: 14px;
            backdrop-filter: blur(10px);
            transition: all 0.25s ease;
            display: flex; flex-direction: column; gap: 8px;
        }
        .room-card:hover {
            background: rgba(255,255,255,0.07);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        }
        .toast {
            position: fixed; bottom: 80px; right: 20px;
            padding: 10px 20px; border-radius: 10px; color: white;
            font-size: 13px; font-weight: 500; z-index: 1000;
            animation: slideIn 0.3s ease; box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        .toast.success { background: #22c55e; }
        .toast.error   { background: #ef4444; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        @media (max-width: 1024px) { .page-body { padding-bottom: 72px; } }
    </style>
</head>
<body>
<div class="custom-bg"></div>
<div id="overlay" class="fixed inset-0 bg-black/50 z-40"></div>

<div class="layout">
    @include('components.sidebar')

    <div class="main-content">
        <header class="main-header">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white/10 text-gray-300 transition">
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">Manajemen Ruangan</h1>
                    <p class="text-xs text-blue-300 font-medium hidden sm:block">Room & AC Unit Management</p>
                </div>
            </div>
            @auth
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <button onclick="openModal()"
                        class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded-xl text-sm font-medium transition">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span class="hidden sm:inline">Tambah Ruangan</span>
                    </button>
                @endif
            @endauth
        </header>

        <div class="page-body">
            <div class="max-w-7xl mx-auto px-4 md:px-6 py-5 space-y-4">

                <!-- Search -->
                <form method="GET" action="/rooms">
                    <div class="flex items-center bg-white/05 border border-white/08 rounded-xl overflow-hidden focus-within:border-blue-500/50 transition max-w-sm">
                        <span class="px-3 text-gray-500"><i class="fa-solid fa-search text-sm"></i></span>
                        <input name="search" value="{{ request('search') }}" type="text"
                            placeholder="Cari ruangan..." autocomplete="off"
                            class="flex-1 bg-transparent text-white py-2 text-sm outline-none placeholder-gray-500">
                        @if (request('search'))
                            <a href="/rooms" class="px-3 text-gray-500 hover:text-white transition">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Room Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                    @forelse ($rooms as $room)
                        @php $online = ($room->device_status ?? 'offline') === 'online'; @endphp
                        <div class="room-card" style="border-top-color: {{ $online ? 'rgba(34,197,94,0.6)' : 'rgba(239,68,68,0.4)' }}">
                            <!-- Name + Status -->
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <h2 class="text-sm font-bold text-white truncate">{{ $room->name }}</h2>
                                    <span class="inline-flex items-center gap-1 text-xs font-medium mt-0.5 {{ $online ? 'text-green-400' : 'text-red-400' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $online ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></span>
                                        {{ $online ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                                <i class="fa-solid fa-server text-gray-600 text-xs flex-shrink-0 ml-1 mt-1"></i>
                            </div>

                            <!-- Temperature -->
                            @php $temp = $room->temperature ?? null; @endphp
                            <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium
                                {{ $temp > 30 ? 'bg-red-500/10 text-red-300' : ($temp > 25 ? 'bg-yellow-500/10 text-yellow-300' : 'bg-blue-500/10 text-blue-300') }}">
                                <span><i class="fa-solid fa-temperature-half mr-1"></i>Suhu</span>
                                <span id="temp-{{ $room->id }}" class="font-bold">{{ $temp ?? '--' }}°C</span>
                            </div>

                            <!-- AC Stats -->
                            <div class="grid grid-cols-2 gap-1.5">
                                <div class="bg-green-500/08 rounded-lg px-2 py-1.5 text-center">
                                    <p class="text-base font-bold text-green-400 leading-none">{{ $room->acUnits->filter(fn($ac) => $ac->status && $ac->status->power == 'ON')->count() }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Aktif</p>
                                </div>
                                <div class="bg-white/04 rounded-lg px-2 py-1.5 text-center">
                                    <p class="text-base font-bold text-gray-400 leading-none">{{ $room->acUnits->filter(fn($ac) => !$ac->status || $ac->status->power !== 'ON')->count() }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">Nonaktif</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 text-center -mt-1">{{ $room->acUnits->count() }} unit total</p>

                            <!-- Actions -->
                            <div class="flex flex-col gap-1.5 mt-auto">
                                <a href="/rooms/{{ $room->id }}/ac"
                                    class="text-center bg-blue-600 hover:bg-blue-500 text-white py-1.5 rounded-lg text-xs font-semibold transition">
                                    <i class="fa-solid fa-sliders mr-1"></i>Kontrol AC
                                </a>
                                @auth
                                    @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                        <form action="/rooms/{{ $room->id }}" method="POST" onsubmit="return confirmDelete(event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full bg-red-500/08 hover:bg-red-500/20 text-red-400 py-1.5 rounded-lg text-xs font-medium transition border border-red-500/15">
                                                <i class="fa-solid fa-trash mr-1"></i>Hapus
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 text-gray-500">
                            <i class="fa-solid fa-server text-5xl mb-4 opacity-20"></i>
                            <p class="text-base font-medium text-white">Belum ada ruangan</p>
                            <p class="text-sm mt-1">Tambahkan ruangan untuk memulai</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<!-- Modal: Add Room -->
@auth
    @if (in_array(Auth::user()->role, ['admin', 'operator']))
    <div id="modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm px-4" style="display:none;align-items:center;justify-content:center;">
        <div class="bg-[#0c1628] border border-white/10 text-white p-6 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold">Tambah Ruangan</h2>
                <button onclick="closeModal()" class="w-8 h-8 rounded-xl hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="addRoomForm" method="POST" action="/rooms">
                @csrf
                <div class="space-y-3 mb-5">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Nama Ruangan</label>
                        <input type="text" name="name" placeholder="Server Room 1" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">ESP Device ID</label>
                        <input type="text" name="device_id" placeholder="esp32_01" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600">
                    </div>
                </div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Buat Ruangan
                </button>
            </form>
        </div>
    </div>
    @endif
@endauth

<script>
function showToast(msg, type = 'info') {
    document.querySelector('.toast')?.remove();
    const t = document.createElement('div'); t.className = `toast ${type}`; t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.animation = 'slideOut 0.3s ease'; setTimeout(() => t.remove(), 300); }, 3000);
}
function openModal()  { document.getElementById('modal').style.display = 'flex'; }
function closeModal() { document.getElementById('modal').style.display = 'none'; document.querySelector('#modal form')?.reset(); }
document.getElementById('modal')?.addEventListener('click', e => { if (e.target === document.getElementById('modal')) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
function confirmDelete(e) {
    e.preventDefault();
    if (confirm('Hapus ruangan ini beserta semua AC unit di dalamnya?')) e.target.submit();
    return false;
}
setInterval(() => {
    fetch('/temperature', { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!Array.isArray(data)) return;
            data.forEach(r => {
                const el = document.getElementById(`temp-${r.id}`);
                const t = parseFloat(r.temp);
                if (el && !isNaN(t)) el.textContent = t + '°C';
            });
        }).catch(() => {});
}, 5000);
document.addEventListener('DOMContentLoaded', () => {
    @if (session('success')) showToast("{{ session('success') }}", 'success'); @endif
    @if (session('error'))   showToast("{{ session('error') }}", 'error'); @endif
    @if ($errors->any())     showToast("{{ $errors->first() }}", 'error'); @endif
});
</script>
@include('components.sidebar-scripts')
</body>
</html>
