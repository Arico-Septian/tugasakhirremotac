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
        .user-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line-soft);
            transition: background var(--t-fast);
        }

        .user-row:hover {
            background: var(--panel-1);
        }

        .user-row:last-child {
            border-bottom: none;
        }

        @media (max-width: 720px) {
            .user-row {
                grid-template-columns: 1fr;
            }

            .user-actions-wrap {
                justify-content: flex-start;
            }
        }

        .user-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }

        .avatar-status {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid var(--bg-1);
            background: var(--ink-3);
        }

        .avatar-status.online {
            background: var(--mint);
            box-shadow: 0 0 0 4px rgba(110, 231, 183, .18);
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

                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                            <div class="stat-card acc-cyan">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label">Total Users</p>
                                        <p class="stat-value"><?php echo e($totalUsers); ?></p>
                                        <p class="stat-meta" style="color:var(--mint);">+<?php echo e($newUsersThisWeek ?? 0); ?>

                                            this week</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-mint">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label">Online Now</p>
                                        <p class="stat-value"><?php echo e($onlineUsers); ?></p>
                                        <p class="stat-meta"><?php echo e($onlinePercentage); ?>% currently active</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-lavender">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label">Administrators</p>
                                        <p class="stat-value"><?php echo e($adminUsers); ?></p>
                                        <p class="stat-meta">System privileges</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="tbl-wrap">
                            <div class="tbl-toolbar">
                                <form method="GET" action="/users" class="flex-1 min-w-[200px] max-w-md">
                                    <label class="search-input">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input name="search" value="<?php echo e(request('search')); ?>"
                                            placeholder="Search by username…" autocomplete="off">
                                    </label>
                                </form>
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

                            <div class="flex items-center justify-between px-4 py-2.5"
                                style="background:rgba(255,255,255,0.02);border-bottom:1px solid var(--line-soft);">
                                <p class="text-xs" style="color:var(--ink-3);">
                                    Showing <span class="text-mono"
                                        style="color:var(--ink-0);"><?php echo e($users->count()); ?></span>
                                    of <span class="text-mono" style="color:var(--ink-0);"><?php echo e($totalUsers); ?></span>
                                    users
                                </p>
                                <span class="pill pill-online" style="padding:3px 10px;font-size:10.5px;">
                                    <span class="dot"></span><span>Live</span>
                                </span>
                            </div>

                            <div id="user-list">
                                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $isOnline = $user->isOnline ?? false;
                                        $statusText = $isOnline
                                            ? 'Online'
                                            : ($user->last_activity
                                                ? \Carbon\Carbon::parse($user->last_activity)->diffForHumans()
                                                : 'Offline');
                                        $isActive = $user->is_active ?? true;
                                    ?>
                                    <div class="user-row">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="user-avatar-wrap">
                                                <div class="avatar avatar-lg">
                                                    <?php echo e(strtoupper(substr($user->name, 0, 1))); ?></div>
                                                <span class="avatar-status <?php echo e($isOnline ? 'online' : ''); ?>"></span>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold"
                                                    style="color:var(--ink-0);line-height:1.2;"><?php echo e($user->name); ?></p>
                                                <p class="text-xs mt-1 flex items-center gap-1.5"
                                                    style="color:var(--ink-3);">
                                                    <i class="fa-regular fa-circle text-[8px] <?php echo e($isOnline ? 'text-mint' : ''); ?>"
                                                        style="<?php echo e($isOnline ? 'color:var(--mint);' : ''); ?>"></i>
                                                    <?php echo e($statusText); ?>

                                                </p>
                                            </div>
                                        </div>

                                        <div class="user-actions-wrap flex items-center gap-2 flex-wrap justify-end">
                                            <span class="badge-role <?php echo e($user->role); ?>">
                                                <?php if($user->role == 'admin'): ?>
                                                    <i class="fa-solid fa-crown text-[9px]"></i>Admin
                                                <?php elseif($user->role == 'operator'): ?>
                                                    <i class="fa-solid fa-gear text-[9px]"></i>Operator
                                                <?php else: ?>
                                                    <i class="fa-regular fa-user text-[9px]"></i>User
                                                <?php endif; ?>
                                            </span>

                                            <span class="pill <?php echo e($isActive ? 'pill-online' : 'pill-error'); ?>"
                                                style="padding:3px 9px;font-size:10px;">
                                                <span
                                                    class="dot"></span><span><?php echo e($isActive ? 'Active' : 'Inactive'); ?></span>
                                            </span>

                                            <?php if($user->id !== Auth::user()->id): ?>
                                                <form action="/users/status/<?php echo e($user->id); ?>" method="POST"
                                                    class="inline-flex"
                                                    onsubmit="return confirm('<?php echo e($isActive ? 'Nonaktifkan' : 'Aktifkan'); ?> user ini?')">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit"
                                                        class="btn-icon <?php echo e($isActive ? 'warning' : ''); ?>"
                                                        style="<?php echo e($isActive ? '' : 'color:var(--mint);background:var(--mint-soft);border-color:var(--mint-soft-2);'); ?>"
                                                        title="<?php echo e($isActive ? 'Deactivate user' : 'Activate user'); ?>">
                                                        <i
                                                            class="fa-solid <?php echo e($isActive ? 'fa-user-slash' : 'fa-user-check'); ?> text-[11px]"></i>
                                                    </button>
                                                </form>
                                                <button
                                                    onclick="editRole(<?php echo e($user->id); ?>, '<?php echo e($user->role); ?>')"
                                                    type="button" class="btn-icon lavender" title="Edit role">
                                                    <i class="fa-solid fa-pen text-[11px]"></i>
                                                </button>
                                                <button onclick="deleteUser(<?php echo e($user->id); ?>)" type="button"
                                                    class="btn-icon danger" title="Delete user">
                                                    <i class="fa-solid fa-trash text-[11px]"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fa-solid fa-users"></i></div>
                                        <p class="empty-title">No users found</p>
                                        <p class="empty-sub">Try adjusting your search or filter</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if($users->hasPages()): ?>
                                <div class="tbl-footer">
                                    <p>Page <?php echo e($users->currentPage()); ?> of <?php echo e($users->lastPage()); ?></p>
                                    <div class="pager"><?php echo e($users->links()); ?></div>
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