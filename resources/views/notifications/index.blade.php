<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifikasi — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('components.sidebar-styles')
    <style>
        .nlist-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 18px;
            border-bottom: 1px solid var(--line-soft);
            transition: var(--t-fast);
        }

        .nlist-item:hover {
            background: var(--panel-1);
        }

        .nlist-item.unread {
            background: rgba(77, 212, 255, 0.05);
        }

        .nlist-item:last-child {
            border-bottom: none;
        }

        .nlist-icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            background: var(--panel-2);
            color: var(--ink-3);
        }

        .nlist-icon.error {
            background: rgba(248, 113, 113, .16);
            color: var(--coral);
        }

        .nlist-icon.warning {
            background: rgba(251, 191, 36, .16);
            color: var(--amber);
        }

        .nlist-icon.success {
            background: rgba(110, 231, 183, .16);
            color: var(--mint);
        }

        .nlist-icon.info {
            background: rgba(77, 212, 255, .16);
            color: var(--cyan);
        }

        .nlist-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
            opacity: 0;
            transition: var(--t-fast);
        }

        .nlist-item:hover .nlist-actions {
            opacity: 1;
        }

        @media (max-width: 720px) {
            .nlist-actions {
                opacity: 1;
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
                        <h1>Notifikasi</h1>
                        <p>{{ $unreadCount }} belum dibaca dari {{ $notifications->total() }} total</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if ($unreadCount > 0)
                        <form action="/notifications/read-all" method="POST" id="markAllForm">
                            @csrf
                            <button type="button" onclick="bulkMarkAllRead()" class="btn btn-soft btn-sm">
                                <i class="fa-solid fa-check-double text-[10px]"></i>
                                <span>Tandai semua dibaca</span>
                            </button>
                        </form>
                    @endif
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
                        <div class="tbl-wrap">
                            @forelse ($notifications as $n)
                                @php
                                    $iconMap = [
                                        'device_offline' => 'fa-plug-circle-exclamation',
                                        'temp_alert' => 'fa-temperature-three-quarters',
                                        'schedule_run' => 'fa-calendar-check',
                                        'system' => 'fa-gear',
                                    ];
                                    $icon = $iconMap[$n->type] ?? 'fa-bell';
                                @endphp
                                <div class="nlist-item {{ $n->isUnread() ? 'unread' : '' }}"
                                    data-id="{{ $n->id }}">
                                    <span class="nlist-icon {{ $n->severity }}">
                                        <i class="fa-solid {{ $icon }}"></i>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            @if ($n->isUnread())
                                                <span
                                                    style="width:7px;height:7px;border-radius:50%;background:var(--cyan);box-shadow:0 0 8px var(--cyan);"></span>
                                            @endif
                                            <p class="text-sm font-semibold" style="color:var(--ink-0);margin:0;">
                                                {{ $n->title }}</p>
                                        </div>
                                        @if ($n->message)
                                            <p class="text-xs mt-1" style="color:var(--ink-2);line-height:1.5;">
                                                {{ $n->message }}</p>
                                        @endif
                                        <div class="flex items-center gap-3 mt-2 text-mono"
                                            style="font-size:10.5px;color:var(--ink-4);">
                                            <span><i class="fa-regular fa-clock text-[9px]"></i>
                                                {{ $n->created_at->diffForHumans() }}</span>
                                            <span>·</span>
                                            <span>{{ $n->created_at->format('d M Y H:i') }}</span>
                                            @if ($n->link)
                                                <span>·</span>
                                                <a href="{{ $n->link }}"
                                                    onclick="markNotifReadInline(event, {{ $n->id }}, '{{ $n->link }}')"
                                                    style="color:var(--cyan);">Buka detail →</a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="nlist-actions">
                                        @if ($n->isUnread())
                                            <button onclick="markNotifReadInline(event, {{ $n->id }}, null)"
                                                class="btn-icon" title="Tandai dibaca">
                                                <i class="fa-solid fa-check text-[11px]"></i>
                                            </button>
                                        @endif
                                        @if ($n->user_id)
                                            <button onclick="deleteNotif({{ $n->id }})" class="btn-icon danger"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                                    <p class="empty-title">Belum ada notifikasi</p>
                                    <p class="empty-sub">Notifikasi sistem & alert akan muncul di sini</p>
                                </div>
                            @endforelse

                            @if ($notifications->hasPages())
                                <div class="tbl-footer">
                                    <p>Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}
                                    </p>
                                    <div class="pager">
                                        @if ($notifications->onFirstPage())
                                            <span class="disabled"><i
                                                    class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                        @else
                                            <a href="{{ $notifications->previousPageUrl() }}"><i
                                                    class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                        @endif
                                        <span class="active text-mono">{{ $notifications->currentPage() }}</span>
                                        @if ($notifications->hasMorePages())
                                            <a href="{{ $notifications->nextPageUrl() }}"><i
                                                    class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                        @else
                                            <span class="disabled"><i
                                                    class="fa-solid fa-chevron-right text-[9px]"></i></span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.bottom-nav')

    <script>
        function markNotifReadInline(e, id, redirectTo) {
            if (e) e.preventDefault?.();
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(() => {
                if (redirectTo) {
                    window.location.href = redirectTo;
                    return;
                }
                const item = document.querySelector(`.nlist-item[data-id="${id}"]`);
                if (item) item.classList.remove('unread');
            });
        }

        function deleteNotif(id) {
            if (!confirm('Hapus notifikasi ini?')) return;
            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(() => {
                document.querySelector(`.nlist-item[data-id="${id}"]`)?.remove();
                if (window.smToast) window.smToast('Notifikasi dihapus', 'success');
            });
        }

        function bulkMarkAllRead() {
            fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            }).then(() => location.reload());
        }

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
