<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manajemen Ruangan — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite('resources/js/app.js')
    @include('components.sidebar-styles')
    <style>
        /* ===== Temperature color scheme (match fuzzy graph) =====
           - cool (dingin)   → biru
           - warm (stabil)   → hijau
           - hot  (panas)    → orange */
        .temp-chip.cool {
            background: rgba(94, 208, 255, 0.14) !important;
            color: #5ed0ff !important;
        }
        .temp-chip.warm {
            background: rgba(110, 231, 183, 0.14) !important;
            color: #6ee7b7 !important;
        }
        .temp-chip.hot {
            background: rgba(251, 146, 60, 0.18) !important;
            color: #fb923c !important;
        }

        /* Keputusan text color follows action */
        .keputusan-yellow { color: #facc15 !important; } /* TURUNKAN */
        .keputusan-cool   { color: #5ed0ff !important; } /* NAIKKAN */
        .keputusan-warm   { color: #6ee7b7 !important; } /* DIAM (stabil) */
        .keputusan-hot    { color: #fb923c !important; }
        .keputusan-idle   { color: var(--ink-3) !important; }

        .room-card {
            position: relative;
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-xl);
            box-shadow: var(--inset-hi);
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: var(--t-base);
            overflow: hidden;
        }

        .room-card::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 2px;
            background: var(--card-accent, var(--ink-3));
            opacity: 0.7;
        }

        .room-card[data-status="online"] {
            --card-accent: var(--mint);
        }

        .room-card[data-status="offline"] {
            --card-accent: var(--coral);
        }

        .room-card:hover {
            background: var(--panel-2);
            border-color: var(--line);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .floor-section {
            margin-bottom: 4px;
        }

        .floor-section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .floor-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: var(--ink-3);
            white-space: nowrap;
        }

        .floor-divider {
            flex: 1;
            height: 1px;
            background: var(--line-soft);
        }

        .floor-count {
            font-size: 10px;
            color: var(--ink-4);
            white-space: nowrap;
        }

        .temp-offline-badge {
            display: inline-block;
            margin-left: 4px;
            padding: 2px 6px;
            background-color: var(--coral);
            color: white;
            font-size: 9px;
            font-weight: 600;
            border-radius: 3px;
            letter-spacing: 0.05em;
        }

        /* Responsive search and filters */
        @media (max-width: 768px) {
            .flex.items-center.gap-2 > form {
                flex: 1;
                min-width: 160px;
                max-width: 400px;
                transition: flex 0.2s ease;
            }

            .flex.items-center.gap-2 > div {
                display: inline-flex;
                gap: 8px;
                flex-wrap: wrap;
                flex-shrink: 0;
            }

            .segmented {
                display: inline-flex;
                gap: 3px;
            }

            .segmented .seg {
                font-size: 11px;
                padding: 6px 10px;
            }

            .search-input input {
                font-size: 13px;
                padding: 6px 10px;
            }

            .search-input i {
                font-size: 13px;
            }
        }

        /* Very small screens (< 480px) */
        @media (max-width: 480px) {
            .flex.items-center.gap-2 {
                gap: 6px;
            }

            .flex.items-center.gap-2 > form {
                flex: 1;
                min-width: 0;
            }

            .flex.items-center.gap-2 > form:focus-within {
                flex: 1;
            }

            .flex.items-center.gap-2 > div {
                display: inline-flex;
                gap: 4px;
                flex-wrap: nowrap;
                flex-shrink: 0;
            }

            .segmented {
                display: inline-flex;
                gap: 2px;
            }

            .segmented .seg {
                font-size: 10px;
                padding: 5px 6px;
                min-width: auto;
            }

            .search-input input {
                font-size: 12px;
                padding: 6px 8px 6px 28px;
            }

            .search-input input::placeholder {
                color: var(--ink-3);
                transition: color 0.2s ease;
            }

            .search-input input:focus::placeholder {
                color: transparent;
            }

            .search-input i {
                font-size: 12px;
                transition: opacity 0.2s ease;
            }

            .search-input:focus-within i {
                opacity: 0;
                pointer-events: none;
            }

            .flex.items-center.gap-2 > button.btn-primary {
                padding: 6px 12px;
                font-size: 11px;
                white-space: nowrap;
            }

            .flex.items-center.gap-2 > button.btn-primary span {
                display: inline;
            }

            .flex.items-center.gap-2 > button.btn-primary i {
                margin-right: 4px;
                font-size: 11px;
            }
        }
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
                <div class="flex items-center gap-2">
                    @include('components.notification-bell')
                    <span id="systemStatus" class="pill pill-online">
                        <span class="dot"></span>
                        <span>Online</span>
                    </span>
                </div>
            </header>

            <div class="page-body">
                <div class="app-content">
                    <div class="app-content-inner space-y-4">

                        <div class="flex items-center gap-2">
                            <form method="GET" action="{{ route('rooms.index') }}" class="flex-1 min-w-0">
                                <label class="search-input">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input name="search" value="{{ request('search') }}" type="text"
                                        placeholder="Cari ruangan…" autocomplete="off">
                                    @if (request('search'))
                                        <a href="{{ route('rooms.index') }}" class="clear" title="Clear"><i
                                                class="fa-solid fa-xmark text-[10px]"></i></a>
                                    @endif
                                </label>
                            </form>

                            <div class="flex gap-2 flex-shrink-0 items-center">
                                <div class="segmented">
                                    <button class="seg active" data-room-filter="all" type="button">All</button>
                                    <button class="seg" data-room-filter="online" type="button">
                                        Online
                                    </button>
                                    <button class="seg" data-room-filter="offline" type="button">
                                        Offline
                                    </button>
                                </div>
                                @auth
                                    @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                        <button onclick="openModal()" class="btn btn-primary btn-sm" type="button">
                                            <i class="fa-solid fa-plus text-[10px]"></i>
                                            <span>Add Room</span>
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>

                        <p id="roomCount" class="text-mono text-xs" style="color:var(--ink-3);"></p>

                        @if ($rooms->count() > 0)
                            <div class="space-y-2">
                                @foreach ($roomsByFloor as $floorName => $floorRooms)
                                    <section class="floor-section">
                                        <div class="floor-section-header">
                                            <i class="fa-solid fa-layer-group text-[10px]"
                                                style="color:var(--lavender);"></i>
                                            <span class="floor-label">{{ $floorName }}</span>
                                            <div class="floor-divider"></div>
                                            <span class="floor-count">{{ $floorRooms->count() }} ruangan</span>
                                        </div>

                                        <div
                                            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 mb-6">
                                            @foreach ($floorRooms as $room)
                                                @php
                                                    $online = ($room->device_status ?? 'offline') === 'online';
                                                    $temp = $room->temperature ?? null;
                                                    $tcls =
                                                        $temp === null
                                                            ? 'idle'
                                                            : ($temp > 30
                                                                ? 'hot'
                                                                : ($temp > 25
                                                                    ? 'warm'
                                                                    : 'cool'));
                                                    $activeAcs = $room->acUnits
                                                        ->filter(fn($ac) => $ac->status && $ac->status->power == 'ON')
                                                        ->count();
                                                    $idleAcs = $room->acUnits
                                                        ->filter(fn($ac) => !$ac->status || $ac->status->power !== 'ON')
                                                        ->count();
                                                @endphp
                                                <div class="room-card" data-room-id="{{ $room->id }}"
                                                    data-status="{{ $online ? 'online' : 'offline' }}">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div class="min-w-0 flex-1">
                                                            <h2 class="text-sm font-semibold text-tight truncate"
                                                                style="color:var(--ink-0);">{{ $room->name }}</h2>
                                                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                                                <span
                                                                    class="pill room-status-pill {{ $online ? 'pill-online' : 'pill-offline' }}"
                                                                    style="padding:3px 9px;font-size:10px;">
                                                                    <span class="dot"></span><span
                                                                        class="room-status-text">{{ $online ? 'Online' : 'Offline' }}</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <i class="fa-solid fa-server text-[11px]"
                                                            style="color:var(--ink-4);margin-top:3px;"></i>
                                                    </div>

                                                    {{-- BAR SUHU (tetap ada span suhu untuk realtime JS) --}}
                                                    <div class="temp-chip {{ $tcls }}"
                                                        style="justify-content:space-between;width:100%;">
                                                        <span
                                                            style="display:inline-flex;align-items:center;gap:6px;color:var(--ink-3);font-weight:500;">
                                                            <i class="fa-solid fa-temperature-half text-[10px]"></i>Suhu
                                                        </span>

                                                        <span id="temp-{{ $room->id }}" class="text-mono" data-offline="{{ $room->temperature_is_offline ? 'true' : 'false' }}">
                                                            {{ $temp ?? '—' }}°C
                                                            @if ($room->temperature_is_offline)
                                                                <span class="temp-offline-badge">OFFLINE</span>
                                                            @endif
                                                        </span>
                                                    </div>

                                                    {{-- FUZZY OUTPUT (taruh DI BAWAH BAR SUHU) --}}
                                                    @if (!is_null($room->temperature))
                                                        <div class="mt-2"
                                                            style="background:var(--panel-1);border:1px solid var(--line-soft);border-radius:var(--r-md);padding:8px 10px;">
                                                            <div class="flex items-center justify-between"
                                                                style="font-size:12px;color:var(--ink-3);">
                                                                <span>ΔT</span>
                                                                <span
                                                                    class="text-mono">{{ $room->delta_t ?? 0 }}</span>
                                                            </div>

                                                            @if (!empty($room->fuzzy))
                                                                <div class="flex items-center justify-between mt-1"
                                                                    style="font-size:12px;">
                                                                    <span
                                                                        style="color:var(--ink-3);">Pendinginan</span>
                                                                    <span class="text-mono"
                                                                        style="font-weight:700;color:var(--mint);">
                                                                        {{ $room->fuzzy['status_pendinginan'] ?? '-' }}
                                                                    </span>
                                                                </div>

                                                                {{-- KEPUTUSAN FUZZY --}}
                                                                @if (!empty($room->decision))
                                                                    <div class="mt-2"
                                                                        style="font-size:11px;color:var(--ink-3);">

                                                                        @php
                                                                            $action = strtoupper($room->decision['action'] ?? 'DIAM');
                                                                            $keputusanClass = match($action) {
                                                                                'TURUNKAN' => 'keputusan-yellow',
                                                                                'NAIKKAN'  => 'keputusan-cool',
                                                                                'DIAM'     => 'keputusan-warm',
                                                                                default    => 'keputusan-idle',
                                                                            };
                                                                        @endphp
                                                                        <div class="flex items-center justify-between">
                                                                            <span>Keputusan</span>

                                                                            <span class="text-mono {{ $keputusanClass }}"
                                                                                style="font-weight:700;">
                                                                                {{ $action }}
                                                                            </span>
                                                                        </div>

                                                                        <div class="flex items-center justify-between mt-1"
                                                                            style="font-size:11px;color:var(--ink-4);">

                                                                            <span>Setpoint</span>

                                                                            <span class="text-mono">
                                                                                {{ $room->decision['setpoint_before'] ?? '-' }}
                                                                                →
                                                                                {{ $room->decision['setpoint_after'] ?? '-' }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <div class="grid grid-cols-2 gap-1.5">
                                                        <div
                                                            style="background:var(--panel-1);border:1px solid var(--line-soft);border-radius:var(--r-md);padding:6px 8px;text-align:center;">
                                                            <p class="text-mono text-base font-bold"
                                                                style="color:var(--mint);line-height:1;"
                                                                id="active-{{ $room->id }}">
                                                                {{ $activeAcs }}</p>
                                                            <p class="label-tag mt-1" style="font-size:9.5px;">Active
                                                            </p>
                                                        </div>
                                                        <div
                                                            style="background:var(--panel-1);border:1px solid var(--line-soft);border-radius:var(--r-md);padding:6px 8px;text-align:center;">
                                                            <p class="text-mono text-base font-bold"
                                                                style="color:var(--ink-2);line-height:1;"
                                                                id="idle-{{ $room->id }}">
                                                                {{ $idleAcs }}</p>
                                                            <p class="label-tag mt-1" style="font-size:9.5px;">Idle
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-center"
                                                        style="color:var(--ink-4);margin-top:-2px;">
                                                        {{ $room->acUnits->count() }} unit total</p>

                                                    <div class="flex flex-col gap-1.5 mt-auto">
                                                        <a href="/rooms/{{ $room->id }}/ac"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fa-solid fa-sliders text-[10px]"></i>Control AC
                                                        </a>
                                                        @auth
                                                            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                                                <form action="/rooms/{{ $room->id }}" method="POST"
                                                                    onsubmit="return confirmDelete(event)">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="btn btn-danger btn-sm btn-block">
                                                                        <i class="fa-solid fa-trash text-[10px]"></i>Hapus
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @endauth
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                            <div id="roomFilterEmpty" class="empty-state" hidden>
                                <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                <p class="empty-title">Tidak ditemukan</p>
                                <p class="empty-sub">Coba filter status lain</p>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-server"></i></div>
                                <p class="empty-title">Belum ada ruangan</p>
                                <p class="empty-sub">Tambahkan ruangan untuk memulai</p>
                            </div>
                        @endif

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
                        <button type="button" class="modal-close" onclick="closeModal()"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form id="addRoomForm" method="POST" action="/rooms">
                        @csrf
                        <div class="modal-body space-y-3">
                            <div class="field">
                                <label class="field-label">Nama Ruangan</label>
                                <input class="input" type="text" name="name" placeholder="Server Room 1"
                                    required>
                            </div>
                            <div class="field">
                                <label class="field-label">ESP Device ID</label>
                                <input class="input text-mono" type="text" name="device_id" placeholder="esp32_01"
                                    required>
                                <p class="field-help">Identifier unik dari device ESP</p>
                            </div>
                            <div class="field">
                                <label class="field-label">Lantai / Zona <span
                                        style="color:var(--ink-4);font-weight:400;">(opsional)</span></label>
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
        function openModal() {
            document.getElementById('modal')?.classList.add('is-open');
        }

        function closeModal() {
            document.getElementById('modal')?.classList.remove('is-open');
            document.querySelector('#modal form')?.reset();
        }
        document.getElementById('modal')?.addEventListener('click', e => {
            if (e.target === document.getElementById('modal')) closeModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeModal();
        });

        function confirmDelete(e) {
            e.preventDefault();
            if (confirm('Hapus ruangan ini beserta semua AC unit di dalamnya?')) e.target.submit();
            return false;
        }

        const roomCards = Array.from(document.querySelectorAll('.room-card'));
        const floorSections = Array.from(document.querySelectorAll('.floor-section'));
        const roomFilterEmpty = document.getElementById('roomFilterEmpty');
        const roomCount = document.getElementById('roomCount');
        let activeRoomFilter = 'all';

        function applyRoomFilter() {
            let visible = 0;

            roomCards.forEach(card => {
                const show = activeRoomFilter === 'all' || card.dataset.status === activeRoomFilter;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            floorSections.forEach(section => {
                const hasVisible = Array.from(section.querySelectorAll('.room-card'))
                    .some(card => card.style.display !== 'none');

                section.style.display = hasVisible ? '' : 'none';
            });

            if (roomCount) {
                roomCount.textContent =
                    visible === roomCards.length ?
                    `Showing ${roomCards.length} rooms` :
                    `${visible} of ${roomCards.length} rooms`;
            }

            if (roomFilterEmpty) {
                roomFilterEmpty.hidden = visible > 0;
            }
        }

        document.querySelectorAll('[data-room-filter]').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('[data-room-filter]').forEach(item => item.classList.remove(
                    'active'));
                this.classList.add('active');
                activeRoomFilter = this.dataset.roomFilter;
                applyRoomFilter();
            });
        });

        setInterval(() => {
            fetch('/temperature', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!Array.isArray(data)) return;
                    data.forEach(r => {
                        const el = document.getElementById(`temp-${r.id}`);
                        const t = parseFloat(r.temp);
                        if (el && !isNaN(t)) {
                            const badge = el.querySelector('.temp-offline-badge');
                            el.textContent = t + '°C';
                            if (badge) el.appendChild(badge);
                        }
                    });
                }).catch(() => {});
        }, 5000);

        function setRoomStatus(card, online) {
            card.dataset.status = online ? 'online' : 'offline';

            const pill = card.querySelector('.room-status-pill');
            const text = card.querySelector('.room-status-text');
            if (!pill || !text) return;

            pill.classList.toggle('pill-online', online);
            pill.classList.toggle('pill-offline', !online);
            text.textContent = online ? 'Online' : 'Offline';
        }

        function refreshRoomStatuses() {
            fetch('/device-status', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!Array.isArray(data)) return;

                    data.forEach(device => {
                        const card = document.querySelector(`.room-card[data-room-id="${device.room_id}"]`);
                        if (!card) return;

                        setRoomStatus(card, device.is_online === true || device.status === 'online');
                    });

                    applyRoomFilter();
                })
                .catch(() => {});
        }

        setInterval(refreshRoomStatuses, 5000);

        document.addEventListener('DOMContentLoaded', () => {
            applyRoomFilter();
            refreshRoomStatuses();

            // Real-time via Reverb: refresh segera saat device/suhu/AC berubah (tanpa reload)
            function refreshAcCounters() {
                fetch('/api/ac-status', { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                    .then(r => r.ok ? r.json() : null)
                    .then(data => {
                        if (!Array.isArray(data)) return;
                        // Group by room_id, count power=ON vs OFF
                        const counts = {};
                        data.forEach(item => {
                            const roomId = item.ac_unit?.room?.id ?? item.acUnit?.room?.id;
                            if (!roomId) return;
                            if (!counts[roomId]) counts[roomId] = { active: 0, idle: 0 };
                            if ((item.power || 'OFF').toUpperCase() === 'ON') counts[roomId].active++;
                            else counts[roomId].idle++;
                        });
                        // Update DOM
                        Object.entries(counts).forEach(([roomId, c]) => {
                            const a = document.getElementById(`active-${roomId}`);
                            const i = document.getElementById(`idle-${roomId}`);
                            if (a) a.textContent = c.active;
                            if (i) i.textContent = c.idle;
                        });
                    })
                    .catch(() => {});
            }

            if (window.Echo) {
                window.Echo.channel('device-status')
                    .listen('.DeviceStatusUpdated', () => refreshRoomStatuses())
                    .listen('.RoomTemperatureUpdated', () => {
                        fetch('/temperature', { headers: { 'Accept': 'application/json' } })
                            .then(r => r.ok ? r.json() : null)
                            .then(data => {
                                if (!Array.isArray(data)) return;
                                data.forEach(r => {
                                    const el = document.getElementById(`temp-${r.id}`);
                                    const t = parseFloat(r.temp);
                                    if (el && !isNaN(t)) {
                                        const badge = el.querySelector('.temp-offline-badge');
                                        el.textContent = t + '°C';
                                        if (badge) el.appendChild(badge);
                                    }
                                });
                            }).catch(() => {});
                    })
                    .listen('.AcStatusUpdated', () => refreshAcCounters());
            }

            @if (session('success'))
                window.smToast("{{ session('success') }}", 'success');
            @endif
            @if (session('error'))
                window.smToast("{{ session('error') }}", 'error');
            @endif
            @if ($errors->any())
                window.smToast("{{ $errors->first() }}", 'error');
            @endif
        });

        function setSystemStatus(online) {
            const el = document.getElementById('systemStatus');
            if (!el) return;
            el.className = 'pill ' + (online ? 'pill-online' : 'pill-offline');
            el.innerHTML = `<span class="dot"></span><span>${online ? 'Online' : 'Offline'}</span>`;
        }
        window.addEventListener('online', () => setSystemStatus(true));
        window.addEventListener('offline', () => setSystemStatus(false));
        document.addEventListener('DOMContentLoaded', () => {
            setSystemStatus(navigator.onLine);
        });
    </script>
    @include('components.sidebar-scripts')
</body>

</html>
