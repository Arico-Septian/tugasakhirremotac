<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucwords($room->name) }} — AC Control</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('components.sidebar-styles')
    <style>
        .ac-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 18px;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            color: white;
        }
        .ac-panel { transition: opacity 0.25s ease, transform 0.25s ease; opacity: 0; transform: translateY(8px); }
        .ac-panel:not(.hidden) { opacity: 1; transform: translateY(0); }
        .mode-btn {
            display: flex; width: 100%; flex-direction: column; align-items: center;
            justify-content: center; gap: 4px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; height: 58px; font-size: 10px; font-weight: 500;
            transition: all 0.2s ease; color: #94a3b8; cursor: pointer; font-family: inherit;
        }
        .mode-btn:hover { background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.3); color: #93c5fd; }
        .mode-btn.active { background: #2563eb; border-color: #3b82f6; color: white; box-shadow: 0 0 12px rgba(37,99,235,0.35); }
        #dropdownAC {
            position: absolute; top: 52px; left: 0; min-width: 200px;
            background: rgba(12,22,40,0.97); backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;
            opacity: 0; visibility: hidden; transform: translateY(8px);
            transition: all 0.2s ease; z-index: 40;
        }
        #dropdownAC.show { opacity: 1; visibility: visible; transform: translateY(0); }
        .toast {
            position: fixed; bottom: 80px; right: 20px;
            padding: 10px 20px; border-radius: 10px; color: white;
            font-size: 13px; font-weight: 500; z-index: 1000;
            animation: slideIn 0.3s ease; box-shadow: 0 4px 16px rgba(0,0,0,0.3);
        }
        .toast.success { background: #22c55e; }
        .toast.error   { background: #ef4444; }
        .toast.info    { background: #3b82f6; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        .btn-loading { position: relative; pointer-events: none; }
        .btn-loading::after {
            content: ''; position: absolute; width: 14px; height: 14px;
            top: 50%; left: 50%; margin: -7px 0 0 -7px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
            border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
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
                <a href="{{ route('rooms.overview') }}"
                    class="hidden lg:flex w-8 h-8 items-center justify-center rounded-xl hover:bg-white/8 text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <button onclick="window.history.back()"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-xl hover:bg-white/10 text-gray-400 transition">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </button>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">{{ ucwords($room->name) }}</h1>
                    <p class="text-xs text-blue-300 font-medium hidden sm:block">AC Control Panel</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if (($room->device_status ?? 'offline') === 'online')
                    <div class="flex items-center gap-1.5 bg-green-500/10 text-green-400 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="hidden sm:inline">ESP</span> Online
                    </div>
                @else
                    <div class="flex items-center gap-1.5 bg-red-500/10 text-red-400 px-3 py-1.5 rounded-full text-xs font-semibold">
                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                        <span class="hidden sm:inline">ESP</span> Offline
                    </div>
                @endif
            </div>
        </header>

        <div class="page-body">
            <div class="px-4 md:px-6 py-4">
                @php $firstAc = $acs->first(); @endphp

                <!-- AC SELECTOR BAR -->
                <div class="relative flex items-center justify-between h-[54px] px-3 mb-3 rounded-xl border border-white/08 bg-white/03">
                    <div class="flex items-center gap-2 min-w-0">
                        <div onclick="toggleDropdown()"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs bg-white/06 text-white border border-white/10 rounded-lg cursor-pointer hover:bg-white/10 transition select-none">
                            <span id="selectedAC" class="font-semibold">
                                {{ $firstAc ? 'AC ' . $firstAc->ac_number . ' ' . $firstAc->name : 'No AC' }}
                            </span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-500"></i>
                        </div>
                        <span class="text-gray-500 text-xs tracking-widest hidden sm:inline">{{ strtoupper($room->name) }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @auth
                            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                @if ($acs->count() > 0)
                                    <button onclick="openBulkModal('ON')"
                                        class="bg-green-600/80 hover:bg-green-600 text-white px-2.5 py-1.5 text-xs rounded-lg flex items-center gap-1 transition font-medium">
                                        <i class="fa-solid fa-power-off text-xs"></i>
                                        <span class="hidden sm:inline">All ON</span>
                                    </button>
                                    <button onclick="openBulkModal('OFF')"
                                        class="bg-slate-600/80 hover:bg-slate-500 text-white px-2.5 py-1.5 text-xs rounded-lg flex items-center gap-1 transition font-medium">
                                        <i class="fa-solid fa-power-off text-xs"></i>
                                        <span class="hidden sm:inline">All OFF</span>
                                    </button>
                                @endif
                                <button {{ $acs->count() >= 15 ? 'disabled' : '' }}
                                    onclick="{{ $acs->count() >= 15 ? '' : 'openModal()' }}"
                                    class="bg-blue-600 hover:bg-blue-500 text-white px-2.5 py-1.5 text-xs rounded-lg flex items-center gap-1 transition font-medium {{ $acs->count() >= 15 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span class="hidden sm:inline">Add AC</span>
                                </button>
                                <button id="editAcBtn" onclick="openEditModal()" {{ !$firstAc ? 'disabled' : '' }}
                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-indigo-500/30 text-indigo-400 hover:bg-indigo-500/10 transition {{ !$firstAc ? 'opacity-40 cursor-not-allowed' : '' }}">
                                    <i class="fa-solid fa-pen text-xs"></i>
                                </button>
                                <form id="deleteForm" method="POST" onsubmit="return confirmDelete(event)"
                                    action="{{ $firstAc ? '/ac/' . $firstAc->id : '#' }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" {{ !$firstAc ? 'disabled' : '' }}
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10 transition {{ !$firstAc ? 'opacity-40 cursor-not-allowed' : '' }}">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>

                    <div id="dropdownAC" class="absolute">
                        @foreach ($acs as $ac)
                            <div data-id="{{ $ac->id }}"
                                onclick="selectAC({{ $ac->id }}, 'AC {{ $ac->ac_number }} {{ $ac->name }}')"
                                class="px-4 py-2.5 text-sm text-gray-300 hover:bg-blue-500/20 hover:text-white cursor-pointer transition whitespace-nowrap first:rounded-t-xl last:rounded-b-xl">
                                <span class="font-semibold text-white">AC {{ $ac->ac_number }}</span>
                                {{ $ac->name }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- AC PANELS -->
                <div class="pt-1">
                    @foreach ($acs as $ac)
                        <div id="ac-{{ $ac->id }}" class="ac-panel {{ $loop->first ? '' : 'hidden' }}"
                            data-ac-id="{{ $ac->id }}"
                            data-ac-number="{{ $ac->ac_number }}"
                            data-ac-name="{{ $ac->name }}"
                            data-ac-brand="{{ $ac->brand }}">
                            <div class="grid grid-cols-1 md:grid-cols-[300px_1fr] lg:grid-cols-[340px_1fr] gap-4">

                                <!-- STATUS (mobile only) -->
                                <div class="grid grid-cols-2 gap-2 mb-1 md:hidden">
                                    <div class="ac-card text-center py-2 px-3"><p class="text-xs text-gray-500 mb-0.5">POWER</p><p class="text-sm font-bold text-green-400">{{ $ac->status?->power ?? 'OFF' }}</p></div>
                                    <div class="ac-card text-center py-2 px-3"><p class="text-xs text-gray-500 mb-0.5">TEMP</p><p class="text-sm font-bold text-blue-400">{{ $ac->status?->set_temperature ?? 24 }}°C</p></div>
                                    <div class="ac-card text-center py-2 px-3"><p class="text-xs text-gray-500 mb-0.5">MODE</p><p class="text-sm font-bold text-purple-400">{{ strtoupper($ac->status?->mode ?? 'COOL') }}</p></div>
                                    <div class="ac-card text-center py-2 px-3"><p class="text-xs text-gray-500 mb-0.5">FAN</p><p class="text-sm font-bold text-cyan-400">{{ strtoupper($ac->status?->fan_speed ?? 'AUTO') }}</p></div>
                                    <div class="ac-card text-center py-2 px-3 col-span-2"><p class="text-xs text-gray-500 mb-0.5">SWING</p><p class="text-sm font-bold text-indigo-400">{{ strtoupper($ac->status?->swing ?? 'OFF') }}</p></div>
                                </div>

                                <!-- LEFT: Power + Temp -->
                                <div class="ac-card flex flex-col items-center justify-center text-center gap-5 py-8">
                                    <form action="/ac/{{ $ac->id }}/toggle" method="POST"
                                        class="power-form"
                                        data-ac-name="AC {{ $ac->ac_number }}{{ $ac->name ? ' ' . $ac->name : '' }}"
                                        data-ac-power="{{ $ac->status?->power ?? 'OFF' }}">
                                        @csrf
                                        <button type="submit"
                                            class="power-btn w-20 h-20 rounded-full flex items-center justify-center
                                            {{ ($ac->status?->power ?? 'OFF') === 'ON'
                                                ? 'bg-green-500 ring-4 ring-green-500/25 shadow-lg shadow-green-900/40'
                                                : 'bg-slate-700 ring-4 ring-white/05' }}
                                            text-white text-2xl hover:opacity-80 transition">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>
                                    <div>
                                        <div class="text-5xl font-bold text-blue-400">
                                            <span class="temp-value">{{ $ac->status?->set_temperature ?? 24 }}</span>°C
                                        </div>
                                        <p class="text-xs text-gray-500 tracking-widest mt-1.5">SET TEMPERATURE</p>
                                    </div>
                                    <div class="flex gap-4">
                                        <button type="button"
                                            onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) - 1 }})"
                                            class="temp-btn w-10 h-10 bg-white/06 hover:bg-white/10 text-white rounded-full text-xl border border-white/10 transition">−</button>
                                        <button type="button"
                                            onclick="setTemp({{ $ac->id }}, {{ ($ac->status?->set_temperature ?? 24) + 1 }})"
                                            class="temp-btn w-10 h-10 bg-blue-600 hover:bg-blue-500 text-white rounded-full text-xl transition">+</button>
                                    </div>
                                </div>

                                <!-- RIGHT -->
                                <div class="flex flex-col gap-3">
                                    <!-- Status strip (desktop) -->
                                    <div class="hidden md:grid grid-cols-5 gap-2">
                                        @foreach ([
                                            ['POWER', $ac->status?->power ?? 'OFF', 'text-green-400'],
                                            ['TEMP', ($ac->status?->set_temperature ?? 24).'°C', 'text-blue-400'],
                                            ['MODE', strtoupper($ac->status?->mode ?? 'COOL'), 'text-purple-400'],
                                            ['FAN', strtoupper($ac->status?->fan_speed ?? 'AUTO'), 'text-cyan-400'],
                                            ['SWING', strtoupper($ac->status?->swing ?? 'OFF'), 'text-indigo-400'],
                                        ] as [$lbl, $val, $col])
                                            <div class="ac-card text-center py-3 px-2">
                                                <p class="text-xs text-gray-500 mb-1">{{ $lbl }}</p>
                                                <p class="text-sm font-bold {{ $col }}">{{ $val }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Mode -->
                                    <div class="ac-card">
                                        <p class="text-xs text-gray-500 font-semibold tracking-widest mb-3">MODE</p>
                                        <div class="grid grid-cols-5 gap-2">
                                            @foreach (['cool'=>['fa-snowflake','Cool'],'heat'=>['fa-fire','Heat'],'dry'=>['fa-droplet','Dry'],'fan'=>['fa-fan','Fan'],'auto'=>['fa-rotate','Auto']] as $m=>[$icon,$lbl])
                                                <form action="/ac/{{ $ac->id }}/mode/{{ $m }}" method="POST" class="control-form">
                                                    @csrf
                                                    <button type="submit" class="mode-btn {{ strtolower($ac->status?->mode ?? 'cool') === $m ? 'active' : '' }}">
                                                        <i class="fa-solid {{ $icon }} text-sm"></i>
                                                        <span>{{ $lbl }}</span>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Fan Speed -->
                                    <div class="ac-card">
                                        <p class="text-xs text-gray-500 font-semibold tracking-widest mb-3">FAN SPEED</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach (['auto'=>['fa-fan','Auto'],'low'=>['fa-wind','Low'],'medium'=>['fa-gauge-simple','Medium'],'high'=>['fa-gauge-high','High']] as $s=>[$icon,$lbl])
                                                <form action="/ac/{{ $ac->id }}/fan-speed/{{ $s }}" method="POST" class="control-form">
                                                    @csrf
                                                    <button type="submit" class="mode-btn {{ strtolower($ac->status?->fan_speed ?? 'auto') === $s ? 'active' : '' }}">
                                                        <i class="fa-solid {{ $icon }} text-sm"></i>
                                                        <span>{{ $lbl }}</span>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Swing -->
                                    <div class="ac-card">
                                        <p class="text-xs text-gray-500 font-semibold tracking-widest mb-3">SWING</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach (['off'=>['fa-ban','Diam'],'full'=>['fa-arrows-up-down','Full'],'half'=>['fa-compress','Setengah'],'down'=>['fa-arrow-down','Bawah']] as $sw=>[$icon,$lbl])
                                                <form action="/ac/{{ $ac->id }}/swing/{{ $sw }}" method="POST" class="control-form">
                                                    @csrf
                                                    <button type="submit" class="mode-btn {{ strtolower($ac->status?->swing ?? 'off') === $sw ? 'active' : '' }}">
                                                        <i class="fa-solid {{ $icon }} text-sm"></i>
                                                        <span>{{ $lbl }}</span>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Timer -->
                                    <div class="ac-card">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 tracking-widest">
                                                <i class="fa-solid fa-clock text-amber-400"></i> TIMER
                                            </div>
                                            <button id="btnTimer-{{ $ac->id }}" onclick="toggleTimer({{ $ac->id }})"
                                                class="bg-blue-600/20 hover:bg-blue-600/40 text-blue-400 px-3 py-1 rounded-full text-xs font-medium transition">
                                                Set Timer
                                            </button>
                                        </div>
                                        <div id="timerView-{{ $ac->id }}">
                                            @if ($ac->timer_on || $ac->timer_off)
                                                <div class="flex gap-4 text-sm">
                                                    @if ($ac->timer_on)<span class="text-green-400 font-medium"><i class="fa-solid fa-circle-play mr-1 text-xs"></i>ON {{ \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') }}</span>@endif
                                                    @if ($ac->timer_off)<span class="text-red-400 font-medium"><i class="fa-solid fa-circle-stop mr-1 text-xs"></i>OFF {{ \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') }}</span>@endif
                                                </div>
                                            @else
                                                <p class="text-gray-600 text-xs">Belum ada timer</p>
                                            @endif
                                        </div>
                                        <form id="timerEdit-{{ $ac->id }}" class="hidden timer-form mt-3"
                                            action="/ac/{{ $ac->id }}/schedule" method="POST">
                                            @csrf
                                            <input type="hidden" name="ac_id" value="{{ $ac->id }}">
                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">TURN ON</p>
                                                    <input type="time" name="timer_on"
                                                        value="{{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->format('H:i') : '' }}"
                                                        class="w-full bg-white/06 text-white border border-white/10 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 mb-1">TURN OFF</p>
                                                    <input type="time" name="timer_off"
                                                        value="{{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->format('H:i') : '' }}"
                                                        class="w-full bg-white/06 text-white border border-white/10 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="save-timer-btn flex-1 bg-blue-600 hover:bg-blue-500 text-white py-2 rounded-lg text-sm font-medium transition">Simpan</button>
                                                <button type="button" onclick="toggleTimer({{ $ac->id }})"
                                                    class="flex-1 bg-white/06 hover:bg-white/10 text-gray-400 py-2 rounded-lg text-sm transition">Batal</button>
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

@include('components.bottom-nav')

<!-- Power Modal -->
<div id="powerModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm px-4" style="display:none;align-items:center;justify-content:center;">
    <div class="bg-[#0c1628] border border-white/10 text-white p-6 rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="flex justify-center mb-4">
            <div id="powerModalIcon" class="w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-lg"><i class="fa-solid fa-power-off"></i></div>
        </div>
        <h2 class="text-lg font-bold text-center mb-1">Konfirmasi Power</h2>
        <p id="powerModalDesc" class="text-sm text-gray-400 text-center mb-6"></p>
        <div class="flex gap-3">
            <button onclick="cancelPower()" class="flex-1 py-2.5 rounded-xl border border-white/15 text-gray-300 hover:bg-white/10 transition text-sm">Batal</button>
            <button id="powerModalConfirm" onclick="confirmPower()" class="flex-1 py-2.5 rounded-xl text-white text-sm font-semibold transition">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<!-- Bulk Modal -->
@if (in_array(Auth::user()->role, ['admin', 'operator']))
<div id="bulkModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm px-4" style="display:none;align-items:center;justify-content:center;">
    <div class="bg-[#0c1628] border border-white/10 text-white p-6 rounded-2xl w-full max-w-sm shadow-2xl">
        <div class="flex justify-center mb-4">
            <div id="bulkModalIcon" class="w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-lg"><i class="fa-solid fa-power-off"></i></div>
        </div>
        <h2 class="text-lg font-bold text-center mb-1">Kontrol Semua AC</h2>
        <p id="bulkModalDesc" class="text-sm text-gray-400 text-center mb-2"></p>
        <p class="text-xs text-gray-500 text-center mb-6"><span class="text-white font-semibold">{{ ucwords($room->name) }}</span> &bull; {{ $acs->count() }} unit</p>
        <div class="flex gap-3">
            <button onclick="closeBulkModal()" class="flex-1 py-2.5 rounded-xl border border-white/15 text-gray-300 hover:bg-white/10 transition text-sm">Batal</button>
            <form id="bulkForm" method="POST" action="/rooms/{{ $room->id }}/ac/bulk-power" class="flex-1">
                @csrf
                <input type="hidden" name="power" id="bulkPowerInput" value="">
                <button id="bulkModalConfirm" type="submit" class="w-full py-2.5 rounded-xl text-white text-sm font-semibold transition">Ya, Lanjutkan</button>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Add AC Modal -->
@auth
    @if (in_array(Auth::user()->role, ['admin', 'operator']))
    <div id="modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm px-4" style="display:none;align-items:center;justify-content:center;">
        <div class="bg-[#0c1628] border border-white/10 text-white p-6 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold">Tambah AC Unit</h2>
                <button onclick="closeModal()" class="w-8 h-8 rounded-xl hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="addACForm" method="POST" action="/rooms/{{ $room->id }}/ac">
                @csrf
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Nomor AC</label>
                        <input type="number" name="ac_number" placeholder="1" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Nama AC</label>
                        <input type="text" name="name" placeholder="Unit A" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Brand</label>
                        <input type="text" name="brand" placeholder="Daikin" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none placeholder-gray-600">
                    </div>
                </div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded-lg text-sm font-semibold transition">Buat AC Unit</button>
            </form>
        </div>
    </div>
    @endif
@endauth

<!-- Edit AC Modal -->
@auth
    @if (in_array(Auth::user()->role, ['admin', 'operator']))
    <div id="editModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm px-4" style="display:none;align-items:center;justify-content:center;">
        <div class="bg-[#0c1628] border border-white/10 text-white p-6 rounded-2xl w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold">Edit AC Unit</h2>
                <button onclick="closeEditModal()" class="w-8 h-8 rounded-xl hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="editACForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="space-y-3 mb-4">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Nomor AC</label>
                        <input type="number" id="editAcNumber" name="ac_number" min="1" max="15" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-600">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Nama AC</label>
                        <input type="text" id="editAcName" name="name" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-600">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Brand</label>
                        <input type="text" id="editAcBrand" name="brand" required
                            class="w-full bg-white/06 border border-white/10 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-600">
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
    @endif
@endauth

<script>
let currentAcId = null;

function showToast(msg, type = 'info') {
    document.querySelector('.toast')?.remove();
    const t = document.createElement('div');
    t.className = `toast ${type}`; t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.animation = 'slideOut 0.3s ease'; setTimeout(() => t.remove(), 300); }, 3000);
}
function showLoading(btn) { btn.classList.add('btn-loading'); btn.disabled = true; btn.textContent = ''; }

function openEditModal() {
    if (!currentAcId) return;
    const panel = document.getElementById('ac-' + currentAcId);
    if (!panel) return;
    document.getElementById('editAcNumber').value = panel.dataset.acNumber || '';
    document.getElementById('editAcName').value   = panel.dataset.acName   || '';
    document.getElementById('editAcBrand').value  = panel.dataset.acBrand  || '';
    document.getElementById('editACForm').action  = '/ac/' + currentAcId;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
document.getElementById('editModal')?.addEventListener('click', e => {
    if (e.target === document.getElementById('editModal')) closeEditModal();
});

function openModal() {
    if ({{ $acs->count() }} >= 15) { showToast('Maksimal 15 AC sudah tercapai', 'error'); return; }
    document.getElementById('modal').style.display = 'flex';
}
function closeModal() { document.getElementById('modal').style.display = 'none'; document.querySelector('#modal form')?.reset(); }
document.getElementById('modal')?.addEventListener('click', e => { if (e.target === document.getElementById('modal')) closeModal(); });

function setTemp(id, temp) {
    if (temp < 16) temp = 16; if (temp > 30) temp = 30;
    document.querySelectorAll(`#ac-${id} .temp-btn`).forEach(b => { b.disabled = true; b.style.opacity = '0.5'; });
    const form = document.createElement('form');
    form.method = 'POST'; form.action = `/ac/${id}/temp/${temp}`;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrf) { const i = document.createElement('input'); i.type = 'hidden'; i.name = '_token'; i.value = csrf; form.appendChild(i); }
    document.body.appendChild(form); form.submit();
}

function toggleTimer(id) {
    const view = document.getElementById('timerView-' + id);
    const edit = document.getElementById('timerEdit-' + id);
    const btn  = document.getElementById('btnTimer-' + id);
    if (!view || !edit || !btn) return;
    const editing = edit.classList.contains('hidden');
    view.classList.toggle('hidden', editing);
    edit.classList.toggle('hidden', !editing);
    btn.textContent = editing ? 'Batal' : 'Set Timer';
}
document.querySelectorAll('.timer-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const on = this.querySelector('[name="timer_on"]').value;
        const off = this.querySelector('[name="timer_off"]').value;
        if (on === off && on !== '') { e.preventDefault(); showToast('Timer ON dan OFF tidak boleh sama', 'error'); return; }
        const btn = this.querySelector('.save-timer-btn'); if (btn) showLoading(btn);
    });
});

function toggleDropdown() { document.getElementById('dropdownAC')?.classList.toggle('show'); }
function selectAC(id, name) {
    currentAcId = id;
    localStorage.setItem('selectedAC', id);
    const span = document.getElementById('selectedAC'); if (span) span.innerText = name;
    document.querySelectorAll('.ac-panel').forEach(el => el.classList.add('hidden'));
    document.getElementById('ac-' + id)?.classList.remove('hidden');
    const df = document.getElementById('deleteForm'); if (df) df.action = '/ac/' + id;
    document.getElementById('dropdownAC')?.classList.remove('show');
}
document.addEventListener('click', e => {
    const dd = document.getElementById('dropdownAC');
    const tr = document.getElementById('selectedAC')?.parentElement;
    if (dd && tr && !dd.contains(e.target) && !tr.contains(e.target)) dd.classList.remove('show');
});

function confirmDelete(e) {
    e.preventDefault();
    if (confirm('Hapus AC ini? Tindakan ini tidak dapat dibatalkan.')) e.target.submit();
    return false;
}

let pendingPowerForm = null;
document.querySelectorAll('.power-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault(); pendingPowerForm = this;
        const turnOn = (this.dataset.acPower || 'OFF').toUpperCase() !== 'ON';
        const modal = document.getElementById('powerModal');
        const icon  = document.getElementById('powerModalIcon');
        const desc  = document.getElementById('powerModalDesc');
        const conf  = document.getElementById('powerModalConfirm');
        if (turnOn) {
            icon.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-lg bg-green-500';
            conf.className = 'flex-1 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition';
            desc.textContent = `Nyalakan ${this.dataset.acName || 'AC ini'}?`;
        } else {
            icon.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl shadow-lg bg-slate-600';
            conf.className = 'flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition';
            desc.textContent = `Matikan ${this.dataset.acName || 'AC ini'}?`;
        }
        modal.style.display = 'flex';
    });
});
function confirmPower() { document.getElementById('powerModal').style.display = 'none'; if (pendingPowerForm) { pendingPowerForm.submit(); pendingPowerForm = null; } }
function cancelPower() { document.getElementById('powerModal').style.display = 'none'; pendingPowerForm = null; }
document.getElementById('powerModal')?.addEventListener('click', e => { if (e.target === document.getElementById('powerModal')) cancelPower(); });

function openBulkModal(power) {
    const icon = document.getElementById('bulkModalIcon');
    const desc = document.getElementById('bulkModalDesc');
    const conf = document.getElementById('bulkModalConfirm');
    document.getElementById('bulkPowerInput').value = power;
    if (power === 'ON') {
        icon.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl bg-green-500';
        conf.className = 'w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition';
        desc.textContent = 'Nyalakan SEMUA AC di ruangan ini?';
    } else {
        icon.className = 'w-14 h-14 rounded-full flex items-center justify-center text-2xl bg-slate-600';
        conf.className = 'w-full py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition';
        desc.textContent = 'Matikan SEMUA AC di ruangan ini?';
    }
    document.getElementById('bulkModal').style.display = 'flex';
}
function closeBulkModal() { document.getElementById('bulkModal').style.display = 'none'; }
document.getElementById('bulkModal')?.addEventListener('click', e => { if (e.target === document.getElementById('bulkModal')) closeBulkModal(); });

document.querySelectorAll('.control-form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.mode-btn');
        if (btn) { btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-sm"></i>'; btn.style.opacity = '0.7'; btn.style.pointerEvents = 'none'; }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    @if (session('new_ac_id'))
        const id = "{{ session('new_ac_id') }}";
        localStorage.setItem('selectedAC', id);
        const el = document.querySelector(`#dropdownAC div[data-id="${id}"]`);
        selectAC(id, el ? el.innerText.trim() : "{{ $firstAc ? 'AC '.$firstAc->ac_number.' '.$firstAc->name : '' }}");
        @if (session('success')) showToast("{{ session('success') }}", 'success'); @endif
    @else
        const saved = localStorage.getItem('selectedAC');
        if (saved && document.getElementById('ac-' + saved)) {
            const el = document.querySelector(`#dropdownAC div[data-id="${saved}"]`);
            selectAC(saved, el ? el.innerText.trim() : "{{ $firstAc ? 'AC '.$firstAc->ac_number.' '.$firstAc->name : '' }}");
        } else {
            localStorage.removeItem('selectedAC');
            @if ($firstAc) selectAC({{ $firstAc->id }}, "{{ 'AC '.$firstAc->ac_number.' '.$firstAc->name }}"); @endif
        }
    @endif
    @if (session('success') && !session('new_ac_id')) showToast("{{ session('success') }}", 'success'); @endif
    @if (session('error')) showToast("{{ session('error') }}", 'error'); @endif
    @if ($errors->any()) showToast("{{ $errors->first() }}", 'error'); @endif
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeEditModal(); cancelPower(); closeBulkModal(); document.getElementById('dropdownAC')?.classList.remove('show'); }
});
if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);
</script>
@include('components.sidebar-scripts')
</body>
</html>
