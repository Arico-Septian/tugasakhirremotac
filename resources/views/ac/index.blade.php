<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucwords($room->name) }} — AC Control</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('components.sidebar-styles')
    <style>
        .ac-panel { transition: opacity .2s, transform .2s; }
        .ac-panel.hidden { display: none; }

        .selector-bar {
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-xl);
            padding: 10px 12px;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            box-shadow: var(--inset-hi);
        }
        .selector {
            position: relative;
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 12px;
            background: var(--panel-2);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            cursor: pointer;
            color: var(--ink-0);
            font-size: 12.5px; font-weight: 600;
            transition: var(--t-base);
            user-select: none;
        }
        .selector:hover { background: var(--panel-3); }
        .selector i { color: var(--ink-3); font-size: 10px; }

        #dropdownAC {
            position: absolute; top: 44px; left: 0;
            min-width: 220px;
            background: var(--bg-2);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            box-shadow: var(--shadow-lg);
            opacity: 0; visibility: hidden;
            transform: translateY(6px);
            transition: var(--t-base);
            z-index: 40;
            overflow: hidden;
        }
        #dropdownAC.show { opacity: 1; visibility: visible; transform: translateY(0); }
        #dropdownAC > div {
            padding: 10px 14px; font-size: 12.5px;
            color: var(--ink-1);
            cursor: pointer; transition: var(--t-fast);
            display: flex; align-items: center; gap: 8px;
        }
        #dropdownAC > div:hover { background: var(--cyan-soft); color: var(--cyan); }
        #dropdownAC > div .num { color: var(--ink-3); font-family: 'JetBrains Mono', monospace; font-size: 11px; }

        .stat-mini {
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-md);
            padding: 9px 8px;
            text-align: center;
        }
        .stat-mini .lbl {
            font-size: 9.5px; letter-spacing: 0.08em; text-transform: uppercase;
            color: var(--ink-3); font-weight: 700;
        }
        .stat-mini .val {
            font-size: 13px; font-weight: 700; margin-top: 4px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--ink-0);
        }

        /* === Temperature Ring === */
        .temp-ring {
            width: 240px; height: 240px;
            border-radius: 50%;
            padding: 3px;
            background: conic-gradient(
                from 215deg,
                transparent 0deg,
                rgba(77, 212, 255, 0.85) 60deg,
                rgba(180, 163, 255, 0.85) 130deg,
                rgba(180, 163, 255, 0.25) 175deg,
                transparent 200deg
            );
            position: relative;
            box-shadow: 0 20px 60px rgba(77, 212, 255, 0.10);
        }
        .temp-ring-inner {
            width: 100%; height: 100%;
            border-radius: 50%;
            background:
                radial-gradient(circle at 50% 45%, rgba(18, 32, 66, 0.95), rgba(7, 16, 31, 0.98) 70%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 6px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }
        .ring-label {
            font-size: 10px; letter-spacing: 0.20em; text-transform: uppercase;
            color: var(--ink-3); font-weight: 700;
            margin: 0;
        }
        .ring-temp {
            font-family: 'Inter', sans-serif;
            font-size: 64px; font-weight: 700;
            color: var(--ink-0);
            letter-spacing: -0.04em;
            line-height: 1;
            display: inline-flex; align-items: flex-start;
        }
        .ring-temp .unit {
            font-size: 18px; color: var(--ink-2);
            margin-left: 2px; margin-top: 6px;
            font-weight: 600;
        }
        .ring-summary {
            font-size: 11.5px; color: var(--ink-3);
            margin: 2px 0 0;
            letter-spacing: 0.02em;
        }

        /* === Control Row (− power +) === */
        .ctrl-row {
            display: inline-flex; align-items: center; gap: 22px;
        }
        .ctrl-btn {
            width: 44px; height: 44px;
            border-radius: 50%;
            font-size: 18px; font-weight: 500;
            color: var(--ink-1);
            background: var(--panel-1);
            border: 1px solid var(--line);
            cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            transition: var(--t-base);
        }
        .ctrl-btn:hover:not(:disabled) {
            background: var(--panel-3);
            border-color: var(--line-strong);
            color: var(--ink-0);
        }
        .ctrl-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .power-btn {
            width: 56px; height: 56px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer;
            background: var(--panel-2);
            border: 1px solid var(--line);
            color: var(--ink-3);
            font-size: 20px;
            transition: var(--t-base);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.02);
        }
        .power-btn:hover { transform: scale(1.04); }
        .power-btn.on {
            background: radial-gradient(circle at center, var(--mint), var(--mint-d));
            color: #07101f;
            border-color: transparent;
            box-shadow:
                0 0 0 4px rgba(110, 231, 183, 0.18),
                0 0 30px rgba(110, 231, 183, 0.45);
        }

        /* === Min/Max chips === */
        .ring-chips {
            display: inline-flex; gap: 8px;
        }
        .ring-chip {
            font-size: 10.5px;
            color: var(--ink-3);
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            padding: 5px 12px;
            border-radius: 999px;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.02em;
        }

        /* Slim power form wrapper to keep stepper inline */
        .power-form-inline { display: inline-flex; }

        /* === Mode buttons (2x2) — vertical stacked, larger === */
        .mode-btn-v {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 8px;
            padding: 18px 10px;
            background: var(--panel-1);
            border: 1px solid var(--line);
            border-radius: var(--r-lg);
            font-size: 13px; font-weight: 600;
            color: var(--ink-2);
            cursor: pointer; font-family: inherit;
            transition: var(--t-base);
            width: 100%; min-height: 86px;
        }
        .mode-btn-v .icon-wrap {
            width: 34px; height: 34px;
            border-radius: 10px;
            background: var(--panel-2);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 14px;
            color: var(--ink-3);
            transition: var(--t-base);
        }
        .mode-btn-v:hover {
            background: var(--panel-2); border-color: var(--line-strong);
            color: var(--ink-0);
        }
        .mode-btn-v:hover .icon-wrap { background: var(--panel-3); color: var(--ink-1); }
        .mode-btn-v.active {
            background: linear-gradient(180deg, rgba(77, 212, 255, 0.14), rgba(77, 212, 255, 0.04));
            border-color: var(--cyan);
            color: var(--cyan);
            box-shadow: 0 0 0 1px var(--cyan-soft) inset, 0 8px 22px rgba(77, 212, 255, 0.14);
        }
        .mode-btn-v.active .icon-wrap {
            background: rgba(77, 212, 255, 0.16);
            color: var(--cyan);
        }
        .mode-btn-v[data-mode="heat"].active { color: var(--coral); border-color: var(--coral); box-shadow: 0 0 0 1px rgba(248,113,113,0.20) inset, 0 8px 22px rgba(248,113,113,0.14); background: linear-gradient(180deg, rgba(248,113,113,0.14), rgba(248,113,113,0.04)); }
        .mode-btn-v[data-mode="heat"].active .icon-wrap { background: rgba(248,113,113,0.18); color: var(--coral); }
        .mode-btn-v[data-mode="dry"].active { color: var(--lavender); border-color: var(--lavender); box-shadow: 0 0 0 1px rgba(180,163,255,0.20) inset, 0 8px 22px rgba(180,163,255,0.14); background: linear-gradient(180deg, rgba(180,163,255,0.14), rgba(180,163,255,0.04)); }
        .mode-btn-v[data-mode="dry"].active .icon-wrap { background: rgba(180,163,255,0.18); color: var(--lavender); }
        .mode-btn-v[data-mode="fan"].active { color: var(--mint); border-color: var(--mint); box-shadow: 0 0 0 1px rgba(110,231,183,0.20) inset, 0 8px 22px rgba(110,231,183,0.14); background: linear-gradient(180deg, rgba(110,231,183,0.14), rgba(110,231,183,0.04)); }
        .mode-btn-v[data-mode="fan"].active .icon-wrap { background: rgba(110,231,183,0.18); color: var(--mint); }

        /* === Horizontal buttons (icon + label inline) for Fan/Swing === */
        .mode-btn-h {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 6px;
            padding: 11px 10px;
            background: var(--panel-1);
            border: 1px solid var(--line);
            border-radius: var(--r-md);
            font-size: 12.5px; font-weight: 600;
            color: var(--ink-2);
            cursor: pointer; font-family: inherit;
            transition: var(--t-base);
            width: 100%;
            white-space: nowrap;
        }
        .mode-btn-h i { font-size: 11.5px; color: var(--ink-3); transition: var(--t-base); }
        .mode-btn-h:hover {
            background: var(--panel-2); border-color: var(--line-strong);
            color: var(--ink-0);
        }
        .mode-btn-h:hover i { color: var(--ink-1); }
        .mode-btn-h.active {
            background: linear-gradient(180deg, rgba(77, 212, 255, 0.14), rgba(77, 212, 255, 0.04));
            border-color: var(--cyan); color: var(--cyan);
            box-shadow: 0 0 0 1px var(--cyan-soft) inset, 0 6px 18px rgba(77, 212, 255, 0.14);
        }
        .mode-btn-h.active i { color: var(--cyan); }

        /* === Timer panel === */
        .timer-state {
            display: flex; gap: 12px;
            margin-top: 4px;
        }
        .timer-card {
            flex: 1; min-width: 0;
            padding: 12px 14px;
            background: var(--panel-1);
            border: 1px solid var(--line-soft);
            border-radius: var(--r-lg);
            display: flex; align-items: center; gap: 12px;
        }
        .timer-card .t-icon {
            width: 32px; height: 32px;
            border-radius: 9px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 12px;
            background: var(--panel-2);
            color: var(--ink-3);
            flex-shrink: 0;
        }
        .timer-card.is-on .t-icon { background: rgba(110,231,183,0.16); color: var(--mint); }
        .timer-card.is-off .t-icon { background: rgba(248,113,113,0.16); color: var(--coral); }
        .timer-card .t-meta { min-width: 0; }
        .timer-card .t-label {
            font-size: 9.5px; letter-spacing: 0.10em; text-transform: uppercase;
            color: var(--ink-3); font-weight: 700;
            margin: 0;
        }
        .timer-card .t-value {
            font-family: 'JetBrains Mono', monospace;
            font-size: 16px; font-weight: 700;
            color: var(--ink-0);
            margin: 2px 0 0;
            letter-spacing: -0.01em;
        }
        .timer-card .t-value.empty { color: var(--ink-4); font-weight: 500; font-size: 13px; }
        .timer-empty {
            text-align: center;
            padding: 18px 12px;
            background: var(--panel-1);
            border: 1px dashed var(--line);
            border-radius: var(--r-lg);
            color: var(--ink-3);
            font-size: 12.5px;
        }
        .timer-empty i { color: var(--ink-4); margin-bottom: 6px; display: block; font-size: 18px; }
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
                <a href="/rooms" class="hidden lg:inline-flex btn-icon" title="Back">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </a>
                <button onclick="window.history.back()" class="lg:hidden btn-icon" title="Back">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                </button>
                <div class="app-header-title">
                    <h1>{{ ucwords($room->name) }}</h1>
                    <p>AC control panel</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="pill {{ ($room->device_status ?? 'offline') === 'online' ? 'pill-online' : 'pill-error' }}">
                    <span class="dot"></span>
                    <span>ESP {{ ($room->device_status ?? 'offline') === 'online' ? 'Online' : 'Offline' }}</span>
                </span>
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner space-y-3">
                    @php $firstAc = $acs->first(); @endphp

                    {{-- AC SELECTOR + ACTIONS --}}
                    <div class="selector-bar">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="selector" onclick="toggleDropdown()">
                                <i class="fa-solid fa-snowflake" style="color:var(--cyan);font-size:11px;"></i>
                                <span id="selectedAC">
                                    {{ $firstAc ? 'AC ' . $firstAc->ac_number . ' · ' . $firstAc->name : 'No AC' }}
                                </span>
                                <i class="fa-solid fa-chevron-down"></i>

                                <div id="dropdownAC">
                                    @foreach ($acs as $ac)
                                        <div data-id="{{ $ac->id }}"
                                             onclick="selectAC({{ $ac->id }}, 'AC {{ $ac->ac_number }} · {{ $ac->name }}')">
                                            <span class="num">#{{ $ac->ac_number }}</span>
                                            <span>{{ $ac->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <span class="kbd hidden sm:inline">{{ strtoupper($room->name) }}</span>
                        </div>

                        @auth
                            @if (in_array(Auth::user()->role, ['admin', 'operator']))
                                <div class="flex items-center gap-1.5">
                                    @if ($acs->count() > 0)
                                        <button type="button" onclick="openBulkModal('ON')" class="btn btn-mint btn-sm">
                                            <i class="fa-solid fa-power-off text-[10px]"></i>
                                            <span class="hidden sm:inline">All ON</span>
                                        </button>
                                        <button type="button" onclick="openBulkModal('OFF')" class="btn btn-soft btn-sm">
                                            <i class="fa-solid fa-power-off text-[10px]"></i>
                                            <span class="hidden sm:inline">All OFF</span>
                                        </button>
                                    @endif
                                    <button type="button" {{ $acs->count() >= 15 ? 'disabled' : '' }}
                                            onclick="{{ $acs->count() >= 15 ? '' : 'openModal()' }}"
                                            class="btn btn-primary btn-sm {{ $acs->count() >= 15 ? 'disabled' : '' }}">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                        <span class="hidden sm:inline">Add AC</span>
                                    </button>
                                    <button id="editAcBtn" type="button" onclick="openEditModal()"
                                            {{ !$firstAc ? 'disabled' : '' }}
                                            class="btn-icon lavender {{ !$firstAc ? 'disabled' : '' }}" title="Edit AC">
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                    </button>
                                    <form id="deleteForm" method="POST" onsubmit="return confirmDelete(event)"
                                          action="{{ $firstAc ? '/ac/' . $firstAc->id : '#' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" {{ !$firstAc ? 'disabled' : '' }}
                                                class="btn-icon danger {{ !$firstAc ? 'disabled' : '' }}" title="Delete AC">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>

                    {{-- AC PANELS --}}
                    @foreach ($acs as $ac)
                        <div id="ac-{{ $ac->id }}" class="ac-panel {{ $loop->first ? '' : 'hidden' }}"
                             data-ac-id="{{ $ac->id }}"
                             data-ac-number="{{ $ac->ac_number }}"
                             data-ac-name="{{ $ac->name }}"
                             data-ac-brand="{{ $ac->brand }}">
                            <div class="grid grid-cols-1 md:grid-cols-[300px_1fr] lg:grid-cols-[340px_1fr] gap-3">

                                {{-- LEFT: Temp ring + Power + Stepper --}}
                                @php
                                    $curTemp  = $ac->status?->set_temperature ?? 24;
                                    $curMode  = ucfirst(strtolower($ac->status?->mode ?? 'Cool'));
                                    $curFan   = ucfirst(strtolower($ac->status?->fan_speed ?? 'Auto'));
                                    $curSwing = strtolower($ac->status?->swing ?? 'off');
                                    $swingLabel = match($curSwing) {
                                        'off' => 'Diam', 'full' => 'Full', 'half' => '½', 'down' => 'Bawah', default => ucfirst($curSwing)
                                    };
                                    $isPowerOn = ($ac->status?->power ?? 'OFF') === 'ON';
                                @endphp
                                <div class="panel" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:24px;padding:32px 20px;">
                                    <p class="eyebrow" style="width:100%;text-align:center;margin:0;"><i class="fa-solid fa-temperature-three-quarters"></i> Set Temperature</p>

                                    <div class="temp-ring">
                                        <div class="temp-ring-inner">
                                            <p class="ring-label">Target</p>
                                            <div class="ring-temp">
                                                <span class="temp-value">{{ $curTemp }}</span><span class="unit">°C</span>
                                            </div>
                                            <p class="ring-summary">{{ $curMode }} · {{ $curFan }} · {{ $swingLabel }}</p>
                                        </div>
                                    </div>

                                    <div class="ctrl-row">
                                        <button type="button" class="ctrl-btn" onclick="setTemp({{ $ac->id }}, {{ $curTemp - 1 }})" title="Turunkan suhu">
                                            <i class="fa-solid fa-minus"></i>
                                        </button>
                                        <form action="/ac/{{ $ac->id }}/toggle" method="POST" class="power-form power-form-inline"
                                              data-ac-name="AC {{ $ac->ac_number }}{{ $ac->name ? ' · ' . $ac->name : '' }}"
                                              data-ac-power="{{ $ac->status?->power ?? 'OFF' }}">
                                            @csrf
                                            <button type="submit" class="power-btn {{ $isPowerOn ? 'on' : '' }}" title="Toggle power">
                                                <i class="fa-solid fa-power-off"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="ctrl-btn" onclick="setTemp({{ $ac->id }}, {{ $curTemp + 1 }})" title="Naikkan suhu">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>

                                    <div class="ring-chips">
                                        <span class="ring-chip">16°C min</span>
                                        <span class="ring-chip">30°C max</span>
                                    </div>
                                </div>

                                {{-- RIGHT --}}
                                <div class="flex flex-col gap-3">
                                    {{-- Status strip --}}
                                    <div class="grid grid-cols-5 gap-2">
                                        <div class="stat-mini">
                                            <p class="lbl">Power</p>
                                            <p class="val" style="color:var(--mint);">{{ $ac->status?->power ?? 'OFF' }}</p>
                                        </div>
                                        <div class="stat-mini">
                                            <p class="lbl">Temp</p>
                                            <p class="val" style="color:var(--cyan);">{{ ($ac->status?->set_temperature ?? 24) }}°</p>
                                        </div>
                                        <div class="stat-mini">
                                            <p class="lbl">Mode</p>
                                            <p class="val" style="color:var(--lavender);">{{ strtoupper($ac->status?->mode ?? 'COOL') }}</p>
                                        </div>
                                        <div class="stat-mini">
                                            <p class="lbl">Fan</p>
                                            <p class="val" style="color:var(--cyan);">{{ strtoupper($ac->status?->fan_speed ?? 'AUTO') }}</p>
                                        </div>
                                        <div class="stat-mini">
                                            <p class="lbl">Swing</p>
                                            <p class="val" style="color:var(--lavender);">{{ strtoupper($ac->status?->swing ?? 'OFF') }}</p>
                                        </div>
                                    </div>

                                    {{-- Mode --}}
                                    <div class="panel">
                                        <p class="eyebrow" style="margin-bottom:12px;">Mode</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach (['cool'=>['fa-snowflake','Cool'],'heat'=>['fa-fire','Heat'],'dry'=>['fa-droplet','Dry'],'fan'=>['fa-fan','Fan']] as $m=>[$icon,$lbl])
                                                <form action="/ac/{{ $ac->id }}/mode/{{ $m }}" method="POST" class="control-form">
                                                    @csrf
                                                    <button type="submit" class="mode-btn-h {{ strtolower($ac->status?->mode ?? 'cool') === $m ? 'active' : '' }}">
                                                        <i class="fa-solid {{ $icon }}"></i><span>{{ $lbl }}</span>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Fan --}}
                                    <div class="panel">
                                        <p class="eyebrow" style="margin-bottom:12px;">Fan Speed</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach (['auto'=>['fa-rotate','Auto'],'low'=>['fa-equals','Low'],'medium'=>['fa-bars','Med'],'high'=>['fa-gauge-high','High']] as $s=>[$icon,$lbl])
                                                <form action="/ac/{{ $ac->id }}/fan-speed/{{ $s }}" method="POST" class="control-form">
                                                    @csrf
                                                    <button type="submit" class="mode-btn-h {{ strtolower($ac->status?->fan_speed ?? 'auto') === $s ? 'active' : '' }}">
                                                        <i class="fa-solid {{ $icon }}"></i><span>{{ $lbl }}</span>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Swing --}}
                                    <div class="panel">
                                        <p class="eyebrow" style="margin-bottom:12px;">Swing</p>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach (['off'=>['fa-ban','Diam'],'full'=>['fa-arrows-up-down','Full'],'half'=>['fa-equals','½'],'down'=>['fa-arrow-down','Bawah']] as $sw=>[$icon,$lbl])
                                                <form action="/ac/{{ $ac->id }}/swing/{{ $sw }}" method="POST" class="control-form">
                                                    @csrf
                                                    <button type="submit" class="mode-btn-h {{ strtolower($ac->status?->swing ?? 'off') === $sw ? 'active' : '' }}">
                                                        <i class="fa-solid {{ $icon }}"></i><span>{{ $lbl }}</span>
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Timer --}}
                                    <div class="panel">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="eyebrow" style="color:var(--amber);margin:0;"><i class="fa-solid fa-clock"></i> Set Timer</p>
                                            <button id="btnTimer-{{ $ac->id }}" type="button" onclick="toggleTimer({{ $ac->id }})" class="btn btn-soft btn-xs">
                                                <i class="fa-solid fa-pen text-[9px]"></i>
                                                <span>Edit</span>
                                            </button>
                                        </div>
                                        <div id="timerView-{{ $ac->id }}">
                                            @if ($ac->timer_on || $ac->timer_off)
                                                <div class="timer-state">
                                                    <div class="timer-card {{ $ac->timer_on ? 'is-on' : '' }}">
                                                        <span class="t-icon"><i class="fa-solid fa-circle-play"></i></span>
                                                        <div class="t-meta">
                                                            <p class="t-label">Turn On</p>
                                                            @if ($ac->timer_on)
                                                                <p class="t-value">{{ \Carbon\Carbon::parse($ac->timer_on)->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                                                            @else
                                                                <p class="t-value empty">—</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="timer-card {{ $ac->timer_off ? 'is-off' : '' }}">
                                                        <span class="t-icon"><i class="fa-solid fa-circle-stop"></i></span>
                                                        <div class="t-meta">
                                                            <p class="t-label">Turn Off</p>
                                                            @if ($ac->timer_off)
                                                                <p class="t-value">{{ \Carbon\Carbon::parse($ac->timer_off)->setTimezone('Asia/Jakarta')->format('H:i') }}</p>
                                                            @else
                                                                <p class="t-value empty">—</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="timer-empty">
                                                    <i class="fa-regular fa-clock"></i>
                                                    Belum ada timer terpasang
                                                </div>
                                            @endif
                                        </div>
                                        <form id="timerEdit-{{ $ac->id }}" class="hidden timer-form" action="/ac/{{ $ac->id }}/schedule" method="POST">
                                            @csrf
                                            <input type="hidden" name="ac_id" value="{{ $ac->id }}">
                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                <div class="field">
                                                    <label class="field-label"><i class="fa-solid fa-circle-play text-[9px]" style="color:var(--mint);"></i> Turn ON</label>
                                                    <input class="input text-mono" type="time" name="timer_on"
                                                           value="{{ $ac->timer_on ? \Carbon\Carbon::parse($ac->timer_on)->format('H:i') : '' }}">
                                                </div>
                                                <div class="field">
                                                    <label class="field-label"><i class="fa-solid fa-circle-stop text-[9px]" style="color:var(--coral);"></i> Turn OFF</label>
                                                    <input class="input text-mono" type="time" name="timer_off"
                                                           value="{{ $ac->timer_off ? \Carbon\Carbon::parse($ac->timer_off)->format('H:i') : '' }}">
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="button" class="btn btn-ghost btn-sm flex-1" onclick="toggleTimer({{ $ac->id }})">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-sm flex-1 save-timer-btn">
                                                    <i class="fa-solid fa-check text-[10px]"></i>
                                                    <span>Simpan</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if ($acs->count() === 0)
                        <div class="panel">
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fa-solid fa-snowflake"></i></div>
                                <p class="empty-title">Belum ada AC unit</p>
                                <p class="empty-sub">Tambahkan AC unit pertama untuk mulai mengontrol</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

{{-- Power confirm modal --}}
<div id="powerModal" class="modal-backdrop">
    <div class="modal" style="max-width:380px;">
        <div class="modal-body text-center" style="padding-top:22px;">
            <div id="powerModalIcon" class="confirm-icon info"><i class="fa-solid fa-power-off"></i></div>
            <h2 style="font-size:16px;font-weight:600;color:var(--ink-0);margin:0 0 4px;">Konfirmasi Power</h2>
            <p id="powerModalDesc" class="text-sm" style="color:var(--ink-2);margin:0;"></p>
        </div>
        <div class="modal-footer" style="padding-top:6px;">
            <button type="button" onclick="cancelPower()" class="btn btn-ghost flex-1">Batal</button>
            <button type="button" id="powerModalConfirm" onclick="confirmPower()" class="btn btn-primary flex-1">Lanjutkan</button>
        </div>
    </div>
</div>

{{-- Bulk modal --}}
@if (in_array(Auth::user()->role, ['admin', 'operator']))
<div id="bulkModal" class="modal-backdrop">
    <div class="modal" style="max-width:400px;">
        <div class="modal-body text-center" style="padding-top:22px;">
            <div id="bulkModalIcon" class="confirm-icon info"><i class="fa-solid fa-power-off"></i></div>
            <h2 style="font-size:16px;font-weight:600;color:var(--ink-0);margin:0 0 4px;">Kontrol Semua AC</h2>
            <p id="bulkModalDesc" class="text-sm" style="color:var(--ink-2);margin:0 0 4px;"></p>
            <p class="text-xs" style="color:var(--ink-3);"><span style="color:var(--ink-0);font-weight:600;">{{ ucwords($room->name) }}</span> · {{ $acs->count() }} unit</p>
        </div>
        <div class="modal-footer" style="padding-top:6px;">
            <button type="button" onclick="closeBulkModal()" class="btn btn-ghost flex-1">Batal</button>
            <form id="bulkForm" method="POST" action="/rooms/{{ $room->id }}/ac/bulk-power" class="flex-1">
                @csrf
                <input type="hidden" name="power" id="bulkPowerInput" value="">
                <button id="bulkModalConfirm" type="submit" class="btn btn-primary btn-block">Lanjutkan</button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Add AC modal --}}
@auth
    @if (in_array(Auth::user()->role, ['admin', 'operator']))
        <div id="modal" class="modal-backdrop">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow"><i class="fa-solid fa-plus"></i> New</p>
                        <h2>Tambah AC Unit</h2>
                    </div>
                    <button type="button" class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="addACForm" method="POST" action="/rooms/{{ $room->id }}/ac">
                    @csrf
                    <div class="modal-body space-y-3">
                        <div class="field">
                            <label class="field-label">Nomor AC</label>
                            <input class="input text-mono" type="number" name="ac_number" placeholder="1" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Nama AC</label>
                            <input class="input" type="text" name="name" placeholder="Unit A" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Brand</label>
                            <input class="input" type="text" name="brand" placeholder="Daikin" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">Buat AC Unit</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="editModal" class="modal-backdrop">
            <div class="modal">
                <div class="modal-header">
                    <div>
                        <p class="eyebrow" style="color:var(--lavender);"><i class="fa-solid fa-pen"></i> Edit</p>
                        <h2>Edit AC Unit</h2>
                    </div>
                    <button type="button" class="modal-close" onclick="closeEditModal()"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <form id="editACForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    <div class="modal-body space-y-3">
                        <div class="field">
                            <label class="field-label">Nomor AC</label>
                            <input class="input text-mono" id="editAcNumber" type="number" name="ac_number" min="1" max="15" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Nama AC</label>
                            <input class="input" id="editAcName" type="text" name="name" required>
                        </div>
                        <div class="field">
                            <label class="field-label">Brand</label>
                            <input class="input" id="editAcBrand" type="text" name="brand" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth

<script>
let currentAcId = null;

function openEditModal() {
    if (!currentAcId) return;
    const panel = document.getElementById('ac-' + currentAcId);
    if (!panel) return;
    document.getElementById('editAcNumber').value = panel.dataset.acNumber || '';
    document.getElementById('editAcName').value   = panel.dataset.acName   || '';
    document.getElementById('editAcBrand').value  = panel.dataset.acBrand  || '';
    document.getElementById('editACForm').action  = '/ac/' + currentAcId;
    document.getElementById('editModal')?.classList.add('is-open');
}
function closeEditModal() { document.getElementById('editModal')?.classList.remove('is-open'); }
document.getElementById('editModal')?.addEventListener('click', e => { if (e.target === document.getElementById('editModal')) closeEditModal(); });

function openModal() {
    if ({{ $acs->count() }} >= 15) { window.smToast('Maksimal 15 AC sudah tercapai', 'error'); return; }
    document.getElementById('modal')?.classList.add('is-open');
}
function closeModal() {
    document.getElementById('modal')?.classList.remove('is-open');
    document.querySelector('#modal form')?.reset();
}
document.getElementById('modal')?.addEventListener('click', e => { if (e.target === document.getElementById('modal')) closeModal(); });

function setTemp(id, temp) {
    if (temp < 16) temp = 16; if (temp > 30) temp = 30;
    document.querySelectorAll(`#ac-${id} .ctrl-row .ctrl-btn`).forEach(b => { b.disabled = true; b.style.opacity = '0.5'; });
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
    btn.innerHTML = editing
        ? '<i class="fa-solid fa-xmark text-[9px]"></i><span>Batal</span>'
        : '<i class="fa-solid fa-pen text-[9px]"></i><span>Edit</span>';
}
document.querySelectorAll('.timer-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const on = this.querySelector('[name="timer_on"]').value;
        const off = this.querySelector('[name="timer_off"]').value;
        if (on === off && on !== '') { e.preventDefault(); window.smToast('Timer ON dan OFF tidak boleh sama', 'error'); return; }
        const btn = this.querySelector('.save-timer-btn'); if (btn) btn.classList.add('is-loading');
    });
});

function toggleDropdown() { document.getElementById('dropdownAC')?.classList.toggle('show'); }
function selectAC(id, name) {
    currentAcId = id;
    localStorage.setItem('selectedAC', id);
    const span = document.getElementById('selectedAC');
    if (span) span.textContent = name;
    document.querySelectorAll('.ac-panel').forEach(el => el.classList.add('hidden'));
    document.getElementById('ac-' + id)?.classList.remove('hidden');
    const df = document.getElementById('deleteForm');
    if (df) df.action = '/ac/' + id;
    document.getElementById('dropdownAC')?.classList.remove('show');
}
document.addEventListener('click', e => {
    const dd = document.getElementById('dropdownAC');
    const tr = document.querySelector('.selector');
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
        const icon = document.getElementById('powerModalIcon');
        const desc = document.getElementById('powerModalDesc');
        const conf = document.getElementById('powerModalConfirm');
        if (turnOn) {
            icon.className = 'confirm-icon success';
            conf.className = 'btn btn-mint flex-1';
            desc.textContent = `Nyalakan ${this.dataset.acName || 'AC ini'}?`;
        } else {
            icon.className = 'confirm-icon danger';
            conf.className = 'btn btn-danger flex-1';
            desc.textContent = `Matikan ${this.dataset.acName || 'AC ini'}?`;
        }
        document.getElementById('powerModal').classList.add('is-open');
    });
});
function confirmPower() {
    document.getElementById('powerModal').classList.remove('is-open');
    if (pendingPowerForm) { pendingPowerForm.submit(); pendingPowerForm = null; }
}
function cancelPower() {
    document.getElementById('powerModal').classList.remove('is-open');
    pendingPowerForm = null;
}
document.getElementById('powerModal')?.addEventListener('click', e => { if (e.target === document.getElementById('powerModal')) cancelPower(); });

function openBulkModal(power) {
    const icon = document.getElementById('bulkModalIcon');
    const desc = document.getElementById('bulkModalDesc');
    const conf = document.getElementById('bulkModalConfirm');
    document.getElementById('bulkPowerInput').value = power;
    if (power === 'ON') {
        icon.className = 'confirm-icon success';
        conf.className = 'btn btn-mint btn-block';
        desc.textContent = 'Nyalakan SEMUA AC di ruangan ini?';
    } else {
        icon.className = 'confirm-icon danger';
        conf.className = 'btn btn-danger btn-block';
        desc.textContent = 'Matikan SEMUA AC di ruangan ini?';
    }
    document.getElementById('bulkModal').classList.add('is-open');
}
function closeBulkModal() { document.getElementById('bulkModal').classList.remove('is-open'); }
document.getElementById('bulkModal')?.addEventListener('click', e => { if (e.target === document.getElementById('bulkModal')) closeBulkModal(); });

document.querySelectorAll('.control-form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('.mode-btn-v, .mode-btn-h, .mode-btn');
        if (btn) {
            btn.style.opacity = '0.65'; btn.style.pointerEvents = 'none';
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    @if (session('new_ac_id'))
        const id = "{{ session('new_ac_id') }}";
        localStorage.setItem('selectedAC', id);
        const el = document.querySelector(`#dropdownAC div[data-id="${id}"]`);
        selectAC(id, el ? el.textContent.trim() : "{{ $firstAc ? 'AC '.$firstAc->ac_number.' · '.$firstAc->name : '' }}");
        @if (session('success')) window.smToast("{{ session('success') }}", 'success'); @endif
    @else
        const saved = localStorage.getItem('selectedAC');
        if (saved && document.getElementById('ac-' + saved)) {
            const el = document.querySelector(`#dropdownAC div[data-id="${saved}"]`);
            selectAC(saved, el ? el.textContent.trim() : "{{ $firstAc ? 'AC '.$firstAc->ac_number.' · '.$firstAc->name : '' }}");
        } else {
            localStorage.removeItem('selectedAC');
            @if ($firstAc) selectAC({{ $firstAc->id }}, "{{ 'AC '.$firstAc->ac_number.' · '.$firstAc->name }}"); @endif
        }
    @endif
    @if (session('success') && !session('new_ac_id')) window.smToast("{{ session('success') }}", 'success'); @endif
    @if (session('error')) window.smToast("{{ session('error') }}", 'error'); @endif
    @if ($errors->any()) window.smToast("{{ $errors->first() }}", 'error'); @endif
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal(); closeEditModal(); cancelPower(); closeBulkModal();
        document.getElementById('dropdownAC')?.classList.remove('show');
    }
});
if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);
</script>
@include('components.sidebar-scripts')
</body>
</html>
