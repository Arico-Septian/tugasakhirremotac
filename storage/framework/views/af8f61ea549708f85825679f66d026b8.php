<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Notifikasi — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
        <?php echo $__env->make('components.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="main-content">
            <header class="main-header">
                <div class="flex items-center gap-3">
                    <button onclick="toggleSidebar()" class="lg:hidden btn-icon" title="Menu">
                        <i class="fa-solid fa-bars text-xs"></i>
                    </button>
                    <div class="app-header-title">
                        <h1>Notifikasi</h1>
                        <p><?php echo e($unreadCount); ?> belum dibaca dari <?php echo e($notifications->total()); ?> total</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if($unreadCount > 0): ?>
                        <form action="/notifications/read-all" method="POST" id="markAllForm">
                            <?php echo csrf_field(); ?>
                            <button type="button" onclick="bulkMarkAllRead()" class="btn btn-soft btn-sm">
                                <i class="fa-solid fa-check-double text-[10px]"></i>
                                <span>Tandai semua dibaca</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php echo $__env->make('components.notification-bell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $iconMap = [
                                        'device_offline' => 'fa-plug-circle-exclamation',
                                        'temp_alert' => 'fa-temperature-three-quarters',
                                        'schedule_run' => 'fa-calendar-check',
                                        'system' => 'fa-gear',
                                    ];
                                    $icon = $iconMap[$n->type] ?? 'fa-bell';
                                ?>
                                <div class="nlist-item <?php echo e($n->isUnread() ? 'unread' : ''); ?>"
                                    data-id="<?php echo e($n->id); ?>">
                                    <span class="nlist-icon <?php echo e($n->severity); ?>">
                                        <i class="fa-solid <?php echo e($icon); ?>"></i>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <?php if($n->isUnread()): ?>
                                                <span
                                                    style="width:7px;height:7px;border-radius:50%;background:var(--cyan);box-shadow:0 0 8px var(--cyan);"></span>
                                            <?php endif; ?>
                                            <p class="text-sm font-semibold" style="color:var(--ink-0);margin:0;">
                                                <?php echo e($n->title); ?></p>
                                        </div>
                                        <?php if($n->message): ?>
                                            <p class="text-xs mt-1" style="color:var(--ink-2);line-height:1.5;">
                                                <?php echo e($n->message); ?></p>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-3 mt-2 text-mono"
                                            style="font-size:10.5px;color:var(--ink-4);">
                                            <span><i class="fa-regular fa-clock text-[9px]"></i>
                                                <?php echo e($n->created_at->diffForHumans()); ?></span>
                                            <span>·</span>
                                            <span><?php echo e($n->created_at->format('d M Y H:i')); ?></span>
                                            <?php if($n->link): ?>
                                                <span>·</span>
                                                <a href="<?php echo e($n->link); ?>"
                                                    onclick="markNotifReadInline(event, <?php echo e($n->id); ?>, '<?php echo e($n->link); ?>')"
                                                    style="color:var(--cyan);">Buka detail →</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="nlist-actions">
                                        <?php if($n->isUnread()): ?>
                                            <button onclick="markNotifReadInline(event, <?php echo e($n->id); ?>, null)"
                                                class="btn-icon" title="Tandai dibaca">
                                                <i class="fa-solid fa-check text-[11px]"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if($n->user_id): ?>
                                            <button onclick="deleteNotif(<?php echo e($n->id); ?>)" class="btn-icon danger"
                                                title="Hapus">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-regular fa-bell-slash"></i></div>
                                    <p class="empty-title">Belum ada notifikasi</p>
                                    <p class="empty-sub">Notifikasi sistem & alert akan muncul di sini</p>
                                </div>
                            <?php endif; ?>

                            <?php if($notifications->hasPages()): ?>
                                <div class="tbl-footer">
                                    <p>Page <?php echo e($notifications->currentPage()); ?> of <?php echo e($notifications->lastPage()); ?>

                                    </p>
                                    <div class="pager">
                                        <?php if($notifications->onFirstPage()): ?>
                                            <span class="disabled"><i
                                                    class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                        <?php else: ?>
                                            <a href="<?php echo e($notifications->previousPageUrl()); ?>"><i
                                                    class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                        <?php endif; ?>
                                        <span class="active text-mono"><?php echo e($notifications->currentPage()); ?></span>
                                        <?php if($notifications->hasMorePages()): ?>
                                            <a href="<?php echo e($notifications->nextPageUrl()); ?>"><i
                                                    class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                        <?php else: ?>
                                            <span class="disabled"><i
                                                    class="fa-solid fa-chevron-right text-[9px]"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo $__env->make('components.bottom-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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

    <?php echo $__env->make('components.sidebar-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views\notifications\index.blade.php ENDPATH**/ ?>