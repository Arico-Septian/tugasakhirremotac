<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>User Management — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php echo $__env->make('components.sidebar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        .stat-card .stat-label-sm {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-3);
        }

        .stat-card .stat-num-lg {
            font-family: 'JetBrains Mono', monospace;
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            margin: 8px 0 6px;
        }

        .stat-card .stat-sub {
            font-size: 11px;
            color: var(--ink-3);
        }

        .stat-card.acc-cyan .stat-num-lg     { color: var(--cyan); }
        .stat-card.acc-mint .stat-num-lg     { color: var(--mint); }
        .stat-card.acc-lavender .stat-num-lg { color: var(--lavender); }
        .stat-card.acc-coral .stat-num-lg    { color: var(--coral); }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--panel-1);
            border-bottom: 1px solid var(--line-soft);
        }

        th {
            padding: 12px 16px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-3);
        }

        tbody tr {
            border-bottom: 1px solid var(--line-soft);
            transition: background var(--t-fast);
        }

        tbody tr:hover {
            background: var(--panel-1);
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        td {
            padding: 14px 16px;
            vertical-align: middle;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .user-info {
            min-width: 0;
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink-0);
            margin: 0;
            line-height: 1.2;
        }

        .user-handle {
            font-size: 12px;
            color: var(--ink-3);
            margin: 3px 0 0;
        }

        .user-avatar-sm {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: var(--bg-1);
            flex-shrink: 0;
        }

        .status-cell {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--ink-2);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            background: var(--ink-3);
        }

        .status-dot.online {
            background: var(--mint);
        }

        .status-dot.active {
            background: var(--mint);
        }

        .actions-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: flex-end;
        }

        @media (max-width: 720px) {
            th, td {
                padding: 10px 12px;
                font-size: 12px;
            }

            .user-avatar-sm {
                width: 32px;
                height: 32px;
                font-size: 12px;
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
                        <h1>User Management</h1>
                        <p>Manage system users &amp; roles</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
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

                        
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                            <div class="stat-card acc-cyan">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Total Users</p>
                                        <p class="stat-num-lg"><?php echo e($totalUsers); ?></p>
                                        <p class="stat-sub">+<?php echo e($newUsersThisWeek ?? 0); ?> minggu ini</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-mint">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Online Now</p>
                                        <p class="stat-num-lg"><?php echo e($onlineUsers); ?></p>
                                        <p class="stat-sub"><?php echo e($onlinePercentage); ?>% sedang aktif</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-lavender">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Administrators</p>
                                        <p class="stat-num-lg"><?php echo e($adminUsers); ?></p>
                                        <p class="stat-sub">System privileges</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-coral">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Inactive</p>
                                        <p class="stat-num-lg"><?php echo e($inactiveUsers ?? 0); ?></p>
                                        <p class="stat-sub">User dinonaktifkan</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-user-slash"></i></div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="tbl-wrap">
                            <div class="tbl-toolbar">
                                <form method="GET" action="/users" style="flex:1;max-width:none;">
                                    <label class="search-input">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input name="search" value="<?php echo e(request('search')); ?>"
                                            placeholder="Search by username…" autocomplete="off">
                                    </label>
                                </form>
                                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                    <div class="segmented">
                                        <a class="seg <?php echo e(!request('role') ? 'active' : ''); ?>" href="/users">All</a>
                                        <a class="seg <?php echo e(request('role') == 'admin' ? 'active' : ''); ?>"
                                            href="/users?role=admin">Admin</a>
                                        <a class="seg <?php echo e(request('role') == 'operator' ? 'active' : ''); ?>"
                                            href="/users?role=operator">Operator</a>
                                        <a class="seg <?php echo e(request('role') == 'user' ? 'active' : ''); ?>"
                                            href="/users?role=user">User</a>
                                    </div>
                                    <button onclick="openModal()" type="button" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-user-plus text-[10px]"></i> Add User
                                    </button>
                                </div>
                            </div>

                            <table id="user-list">
                                <thead>
                                    <tr>
                                        <th style="width:30%">USER</th>
                                        <th style="width:15%">ROLE</th>
                                        <th style="width:15%">STATUS</th>
                                        <th style="width:15%">ACTIVE</th>
                                        <th style="width:25%;text-align:right;padding-right:24px;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $isOnline = $user->isOnline ?? false;
                                            $isActive = $user->is_active ?? true;
                                            $initials = strtoupper(substr($user->name, 0, 1));
                                            $handle = '@' . strtolower(str_replace(' ', '', $user->name));
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <?php
                                        $colors = ['cyan', 'mint', 'lavender', 'coral'];
                                        $colorIndex = ($user->id - 1) % 4;
                                        $colorName = $colors[$colorIndex];
                                    ?>
                                    <div class="user-avatar-sm" style="background:var(--<?php echo e($colorName); ?>);">
                                                        <?php echo e($initials); ?>

                                                    </div>
                                                    <div class="user-info">
                                                        <p class="user-name"><?php echo e($user->name); ?></p>
                                                        <p class="user-handle"><?php echo e($handle); ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-role <?php echo e($user->role); ?>">
                                                    <?php if($user->role == 'admin'): ?>
                                                        ADMINISTRATOR
                                                    <?php elseif($user->role == 'operator'): ?>
                                                        OPERATOR
                                                    <?php else: ?>
                                                        USER
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="status-cell">
                                                    <span class="status-dot <?php echo e($isOnline ? 'online' : ''); ?>"></span>
                                                    <?php echo e($isOnline ? 'Online' : 'Offline'); ?>

                                                </div>
                                            </td>
                                            <td>
                                                <div class="status-cell">
                                                    <span class="status-dot <?php echo e($isActive ? 'active' : ''); ?>"></span>
                                                    <?php echo e($isActive ? 'Active' : 'Inactive'); ?>

                                                </div>
                                            </td>
                                            <td>
                                                <div class="actions-cell">
                                                    <?php if($user->id !== Auth::user()->id): ?>
                                                        <form action="/users/status/<?php echo e($user->id); ?>" method="POST"
                                                            class="inline"
                                                            onsubmit="return confirm('<?php echo e($isActive ? 'Nonaktifkan' : 'Aktifkan'); ?> user ini?')">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit"
                                                                class="btn-icon"
                                                                style="<?php echo e($isActive ? 'color:var(--coral);background:var(--coral-soft);border-color:var(--coral-soft-2);' : 'color:var(--mint);background:var(--mint-soft);border-color:var(--mint-soft-2);'); ?>"
                                                                title="<?php echo e($isActive ? 'Deactivate user' : 'Activate user'); ?>">
                                                                <i
                                                                    class="fa-solid <?php echo e($isActive ? 'fa-user-slash' : 'fa-user-check'); ?> text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                        <button
                                                            onclick="editRole(<?php echo e($user->id); ?>, '<?php echo e($user->role); ?>')"
                                                            type="button" class="btn-icon lavender" title="Edit role">
                                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                                        </button>
                                                        <button onclick="deleteUser(<?php echo e($user->id); ?>)" type="button"
                                                            class="btn-icon danger" title="Delete user">
                                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <div class="empty-icon"><i class="fa-solid fa-users"></i></div>
                                                    <p class="empty-title">No users found</p>
                                                    <p class="empty-sub">Try adjusting your search or filter</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <?php if($users->hasPages()): ?>
                                <div class="tbl-footer">
                                    <p>
                                        Menampilkan <span class="text-mono"
                                            style="color:var(--ink-1);"><?php echo e($users->firstItem() ?? 0); ?>–<?php echo e($users->lastItem() ?? 0); ?></span>
                                        dari <span class="text-mono"
                                            style="color:var(--ink-1);"><?php echo e($users->total()); ?></span> user
                                    </p>
                                    <div class="pager">
                                        <?php
                                            $current = $users->currentPage();
                                            $last = $users->lastPage();
                                            $pages = [];
                                            if ($last <= 7) {
                                                $pages = range(1, $last);
                                            } else {
                                                $pages[] = 1;
                                                if ($current > 3) $pages[] = '...';
                                                for ($i = max(2, $current - 1); $i <= min($last - 1, $current + 1); $i++) $pages[] = $i;
                                                if ($current < $last - 2) $pages[] = '...';
                                                $pages[] = $last;
                                            }
                                        ?>

                                        <?php if($users->onFirstPage()): ?>
                                            <span class="disabled"><i class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                        <?php else: ?>
                                            <a href="<?php echo e($users->previousPageUrl()); ?>"><i class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                        <?php endif; ?>

                                        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($p === '...'): ?>
                                                <span class="disabled">…</span>
                                            <?php elseif($p == $current): ?>
                                                <span class="active text-mono"><?php echo e($p); ?></span>
                                            <?php else: ?>
                                                <a class="text-mono" href="<?php echo e($users->url($p)); ?>"><?php echo e($p); ?></a>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                        <?php if($users->hasMorePages()): ?>
                                            <a href="<?php echo e($users->nextPageUrl()); ?>"><i class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                        <?php else: ?>
                                            <span class="disabled"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
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

    
    <div id="modal" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <div>
                    <p class="eyebrow"><i class="fa-solid fa-plus"></i> New</p>
                    <h2>Add new user</h2>
                    <p class="sub">Pengguna baru akan menerima akses dengan role yang dipilih</p>
                </div>
                <button type="button" class="modal-close" onclick="closeModal()"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="addUserForm" method="POST" action="/users">
                <?php echo csrf_field(); ?>
                <div class="modal-body space-y-3">
                    <div class="field">
                        <label class="field-label">Name</label>
                        <input class="input" type="text" name="name" placeholder="John Doe" required>
                    </div>
                    <div class="field">
                        <label class="field-label">Password</label>
                        <input class="input" type="password" name="password" placeholder="Minimum 6 characters"
                            required>
                    </div>
                    <div class="field">
                        <label class="field-label">Role</label>
                        <select class="input select" name="role">
                            <option value="admin">Admin — full system access</option>
                            <option value="operator">Operator — manage rooms &amp; AC</option>
                            <option value="user" selected>User — view only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create user</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="editRoleModal" class="modal-backdrop">
        <div class="modal">
            <div class="modal-header">
                <div>
                    <p class="eyebrow" style="color:var(--lavender);"><i class="fa-solid fa-pen"></i> Edit</p>
                    <h2>Edit user role</h2>
                </div>
                <button type="button" class="modal-close" onclick="closeEditModal()"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editRoleForm" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="field">
                        <label class="field-label">Role</label>
                        <select class="input select" name="role" id="edit_user_role">
                            <option value="admin">Admin</option>
                            <option value="operator">Operator</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update role</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modal')?.classList.add('is-open');
        }

        function closeModal() {
            document.getElementById('modal')?.classList.remove('is-open');
            document.querySelector('#modal form')?.reset();
        }

        function openEditModal(userId, currentRole) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_user_role').value = currentRole;
            document.getElementById('editRoleForm').action = `/users/${userId}`;
            document.getElementById('editRoleModal')?.classList.add('is-open');
        }

        function closeEditModal() {
            document.getElementById('editRoleModal')?.classList.remove('is-open');
        }

        function editRole(id, role) {
            openEditModal(id, role);
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeModal();
                closeEditModal();
            }
        });
        document.getElementById('modal')?.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeModal();
        });
        document.getElementById('editRoleModal')?.addEventListener('click', e => {
            if (e.target === e.currentTarget) closeEditModal();
        });

        function deleteUser(id) {
            if (!confirm('Hapus user ini? Tindakan ini tidak dapat dibatalkan.')) return;
            fetch(`/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Delete failed');
                    return r.json();
                })
                .then(() => {
                    window.smToast('User deleted', 'success');
                    setTimeout(() => location.reload(), 800);
                })
                .catch(() => window.smToast('Failed to delete user', 'error'));
        }

        let pingInterval = null;

        function startActivityPing() {
            if (pingInterval) clearInterval(pingInterval);
            pingInterval = setInterval(() => {
                if (!document.hidden) {
                    fetch('/update-activity', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    }).catch(() => {});
                }
            }, 60000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            startActivityPing();
            setSystemStatus(navigator.onLine);
            <?php if(session('success')): ?>
                window.smToast("<?php echo e(session('success')); ?>", 'success');
            <?php endif; ?>
            <?php if(session('error')): ?>
                window.smToast("<?php echo e(session('error')); ?>", 'error');
            <?php endif; ?>
            <?php if($errors->any()): ?>
                window.smToast("<?php echo e($errors->first()); ?>", 'error');
            <?php endif; ?>
        });
        window.addEventListener('beforeunload', () => {
            if (pingInterval) clearInterval(pingInterval);
        });

        function setSystemStatus(online) {
            const el = document.getElementById('systemStatus');
            if (!el) return;
            el.className = 'pill ' + (online ? 'pill-online' : 'pill-offline');
            el.innerHTML = `<span class="dot"></span><span>${online ? 'Online' : 'Offline'}</span>`;
        }
        window.addEventListener('online', () => setSystemStatus(true));
        window.addEventListener('offline', () => setSystemStatus(false));
    </script>
    <?php echo $__env->make('components.sidebar-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>

</html>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/users/index.blade.php ENDPATH**/ ?>