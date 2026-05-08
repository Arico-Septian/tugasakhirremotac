<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Ruangan — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('components.sidebar-styles')
    <style>
        .room-card {
            position: relative;
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-xl);
            box-shadow: var(--inset-hi);
            padding: 14px;
            display: flex; flex-direction: column; gap: 8px;
            transition: var(--t-base);
            overflow: hidden;
        }
        .room-card::before {
            content: ''; position: absolute; left: 0; right: 0; top: 0;
            height: 2px;
            background: var(--card-accent, var(--ink-3));
            opacity: 0.7;
        }
        .room-card[data-status="online"]  { --card-accent: var(--mint); }
        .room-card[data-status="offline"] { --card-accent: var(--coral); }
        .room-card:hover { background: var(--panel-2); border-color: var(--line); transform: translateY(-2px); box-shadow: var(--shadow); }
    </style>
</head>
<body>
<div class="custom-bg"></div>
<div id="overlay"></div>

<div class="layout">
    @include('components.sidebar')

    <div class="main-content">
        <header class="main-header">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden btn-icon" title="Menu">
                    <i class="fa-solid fa-bars text-xs"></i>
                </button>
                <div class="app-header-title">
                    <h1>Rooms &amp; AC Units</h1>
                    <p>Manage server rooms</p>
                </div>
            </div>
            @auth
                @if (in_array(Auth::user()->role, ['admin', 'operator']))
                    <button onclick="openModal()" class="btn btn-primary btn-sm" type="button">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span class="hidden sm:inline">Add Room</span>
                    </button>
                @endif
            @endauth
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-4">

                    <form method="GET" action="/rooms" class="max-w-sm">
                        <label class="search-input">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input name="search" value="{{ request('search') }}" type="text" placeholder="Cari ruangan…" autocomplete="off">
                            @if (request('search'))
                                <a href="/rooms" class="clear" title="Clear"><i class="fa-solid fa-xmark text-[10px]"></i></a>
                            @endif
                        </label>
                    </form>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                        @forelse ($rooms as $room)
                            @php
                                $online = ($room->device_status ?? 'offline') === 'online';
                                $temp = $room->temperature ?? null;
                                $tcls = $temp === null ? 'idle' : ($temp > 30 ? 'hot' : ($temp > 25 ? 'warm' : 'cool'));
                                $activeAcs = $room->acUnits->filter(fn($ac) => $ac->status && $ac->status->power == 'ON')->count();
                                $idleAcs   = $room->acUnits->filter(fn($ac) => !$ac->status || $ac->status->power !== 'ON')->count();
                            @endphp
                            <div class="room-card" data-status="{{ $online ? 'online' : 'offline' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <h2 class="text-sm font-semibold text-tight truncate" style="color:var(--ink-0);">{{ $room->name }}</h2>
                                        <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                            <span class="pill {{ $online ? 'pill-online' : 'pill-offline' }}" style="padding:3px 9px;font-size:10px;">
                                                <span class="dot"></span><span>{{ $online ? 'Online' : 'Offline' }}</span>
                                            </span>
                                            @if ($room->floor)
                                                <span class="label-tag" style="background:var(--lavender-soft);color:var(--lavender);border-color:var(--lavender-soft-2);font-size:9px;">
                                                    <i class="fa-solid fa-layer-group" style="font-size:8px;"></i> {{ $room->floor }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fa-solid fa-server text-[11px]" style="color:var(--ink-4);margin-top:3px;"></i>
                                </div>

                                <div class="temp-chip {{ $tcls }}" style="justify-content:space-between;width:100%;">
                                    <span style="display:inline-flex;align-items:center;gap:6px;color:var(--ink-3);font-weight:500;">
                                        <i class="fa-solid fa-temperature-half text-[10px]"></i>Suhu
                                    </span>
                                    <span id="temp-{{ $room->id }}" class="text-mono">{{ $temp ?? '—' }}°C</span>
                                </div>

                                <div class="grid grid-cols-2 gap-1.5">
                                    <div style="background:var(--panel-1);border:1px solid var(--line-soft);border-radius:var(--r-md);padding:6px 8px;text-align:center;">
                                        <p class="text-mono text-base font-bold" style="color:var(--mint);line-height:1;">{{ $activeAcs }}</p>
                                        <p class="label-tag mt-1" style="font-size:9.5px;">Active</p>
                                    </div>
                                    <div style="background:var(--panel-1);border:1px solid var(--line-soft);border-radius:var(--r-md);padding:6px 8px;text-align:center;">
                                        <p class="text-mono text-base font-bold" style="color:var(--ink-2);line-height:1;">{{ $idleAcs }}</p>
                                        <p class="label-tag mt-1" style="font-size:9.5px;">Idle</p>
                                    </div>
                                </div>
                                <p class="text-xs text-center" style="color:var(--ink-4);margin-top:-2px;">{{ $room->acUnits->count() }} unit total</p>

                                <div class="flex flex-col gap-1.5 mt-auto">
                                    <a href="/rooms/{{ $room->id }}/ac" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-sliders text-[10px]"></i>Control AC
                                    </a>
                                    @auth
                                        @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                            <form action="/rooms/{{ $room->id }}" method="POST" onsubmit="return confirmDelete(event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-block">
                                                    <i class="fa-solid fa-trash text-[10px]"></i>Hapus
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-server"></i></div>
                                <p class="empty-title">Belum ada ruangan</p>
                                <p class="empty-sub">Tambahkan ruangan untuk memulai</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

{{-- Modal: Add Room --}}
@auth
    @if (in_array(Auth::user()->role, ['admin', 'operator']))
        <div id="modal" class="modal-backdrop">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow"><i class="fa-solid fa-plus"></i> New</p>
                        <h2>Tambah Ruangan</h2>
                        <p class="sub">Daftarkan ruangan baru beserta ESP device-nya</p>
                    </div>
                    <button type="button" class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="addRoomForm" method="POST" action="/rooms">
                    @csrf
                    <div class="modal-body space-y-3">
                        <div class="field">
                            <label class="field-label">Nama Ruangan</label>
                            <input class="input" type="text" name="name" placeholder="Server Room 1" required>
                        </div>
                        <div class="field">
                            <label class="field-label">ESP Device ID</label>
                            <input class="input text-mono" type="text" name="device_id" placeholder="esp32_01" required>
                            <p class="field-help">Identifier unik dari device ESP</p>
                        </div>
                        <div class="field">
                            <label class="field-label">Lantai / Zona <span style="color:var(--ink-4);font-weight:400;">(opsional)</span></label>
                            <input class="input" type="text" name="floor" placeholder="cth: Lantai 1, Zona A">
                            <p class="field-help">Digunakan untuk pengelompokan di Room Overview</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat Ruangan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth

<script>
function openModal()  { document.getElementById('modal')?.classList.add('is-open'); }
function closeModal() { document.getElementById('modal')?.classList.remove('is-open'); document.querySelector('#modal form')?.reset(); }
document.getElementById('modal')?.addEventListener('click', e => { if (e.target === document.getElementById('modal')) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

function confirmDelete(e) {
    e.preventDefault();
    if (confirm('Hapus ruangan ini beserta semua AC unit di dalamnya?')) e.target.submit();
    return false;
}

setInterval(() => {
    fetch('/temperature', { headers: { 'Accept':'application/json' } })
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
    @if (session('success')) window.smToast("{{ session('success') }}", 'success'); @endif
    @if (session('error'))   window.smToast("{{ session('error') }}", 'error'); @endif
    @if ($errors->any())     window.smToast("{{ $errors->first() }}", 'error'); @endif
});
</script>
@include('components.sidebar-scripts')
</body>
</html>
