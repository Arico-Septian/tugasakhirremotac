<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @include('components.sidebar-styles')
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
        @include('components.sidebar')

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

                        {{-- Stats --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
                            <div class="stat-card acc-cyan">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Total Users</p>
                                        <p class="stat-num-lg">{{ $totalUsers }}</p>
                                        <p class="stat-sub">+{{ $newUsersThisWeek ?? 0 }} minggu ini</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-mint">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Online Now</p>
                                        <p class="stat-num-lg">{{ $onlineUsers }}</p>
                                        <p class="stat-sub">{{ $onlinePercentage }}% sedang aktif</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-user-check"></i></div>
                                </div>
                            </div>
                            <div class="stat-card acc-lavender">
                                <span class="accent-bar"></span>
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="stat-label-sm">Administrators</p>
                                        <p class="stat-num-lg">{{ $adminUsers }}</p>
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
                                        <p class="stat-num-lg">{{ $inactiveUsers ?? 0 }}</p>
                                        <p class="stat-sub">User dinonaktifkan</p>
                                    </div>
                                    <div class="stat-icon"><i class="fa-solid fa-user-slash"></i></div>
                                </div>
                            </div>
                        </div>

                        {{-- User table card --}}
                        <div class="tbl-wrap">
                            <div class="tbl-toolbar">
                                <form method="GET" action="/users" style="flex:1;max-width:none;">
                                    <label class="search-input">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <input name="search" value="{{ request('search') }}"
                                            placeholder="Search by username…" autocomplete="off">
                                    </label>
                                </form>
                                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                    <div class="segmented">
                                        <a class="seg {{ !request('role') ? 'active' : '' }}" href="/users">All</a>
                                        <a class="seg {{ request('role') == 'admin' ? 'active' : '' }}"
                                            href="/users?role=admin">Admin</a>
                                        <a class="seg {{ request('role') == 'operator' ? 'active' : '' }}"
                                            href="/users?role=operator">Operator</a>
                                        <a class="seg {{ request('role') == 'user' ? 'active' : '' }}"
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
                                    @forelse ($users as $user)
                                        @php
                                            $isOnline = $user->isOnline ?? false;
                                            $isActive = $user->is_active ?? true;
                                            $initials = strtoupper(substr($user->name, 0, 1));
                                            $handle = '@' . strtolower(str_replace(' ', '', $user->name));
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    @php
                                        $colors = ['cyan', 'mint', 'lavender', 'coral'];
                                        $colorIndex = ($user->id - 1) % 4;
                                        $colorName = $colors[$colorIndex];
                                    @endphp
                                    <div class="user-avatar-sm" style="background:var(--{{ $colorName }});">
                                                        {{ $initials }}
                                                    </div>
                                                    <div class="user-info">
                                                        <p class="user-name">{{ $user->name }}</p>
                                                        <p class="user-handle">{{ $handle }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-role {{ $user->role }}">
                                                    @if ($user->role == 'admin')
                                                        ADMINISTRATOR
                                                    @elseif ($user->role == 'operator')
                                                        OPERATOR
                                                    @else
                                                        USER
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div class="status-cell">
                                                    <span class="status-dot {{ $isOnline ? 'online' : '' }}"></span>
                                                    {{ $isOnline ? 'Online' : 'Offline' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="status-cell">
                                                    <span class="status-dot {{ $isActive ? 'active' : '' }}"></span>
                                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="actions-cell">
                                                    @if ($user->id !== Auth::user()->id)
                                                        <form action="/users/status/{{ $user->id }}" method="POST"
                                                            class="inline"
                                                            onsubmit="return confirm('{{ $isActive ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn-icon"
                                                                style="{{ $isActive ? 'color:var(--coral);background:var(--coral-soft);border-color:var(--coral-soft-2);' : 'color:var(--mint);background:var(--mint-soft);border-color:var(--mint-soft-2);' }}"
                                                                title="{{ $isActive ? 'Deactivate user' : 'Activate user' }}">
                                                                <i
                                                                    class="fa-solid {{ $isActive ? 'fa-user-slash' : 'fa-user-check' }} text-[10px]"></i>
                                                            </button>
                                                        </form>
                                                        <button
                                                            onclick="editRole({{ $user->id }}, '{{ $user->role }}')"
                                                            type="button" class="btn-icon lavender" title="Edit role">
                                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                                        </button>
                                                        <button onclick="deleteUser({{ $user->id }})" type="button"
                                                            class="btn-icon danger" title="Delete user">
                                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                <div class="empty-state">
                                                    <div class="empty-icon"><i class="fa-solid fa-users"></i></div>
                                                    <p class="empty-title">No users found</p>
                                                    <p class="empty-sub">Try adjusting your search or filter</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @if ($users->hasPages())
                                <div class="tbl-footer">
                                    <p>
                                        Menampilkan <span class="text-mono"
                                            style="color:var(--ink-1);">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</span>
                                        dari <span class="text-mono"
                                            style="color:var(--ink-1);">{{ $users->total() }}</span> user
                                    </p>
                                    <div class="pager">
                                        @php
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
                                        @endphp

                                        @if ($users->onFirstPage())
                                            <span class="disabled"><i class="fa-solid fa-chevron-left text-[9px]"></i></span>
                                        @else
                                            <a href="{{ $users->previousPageUrl() }}"><i class="fa-solid fa-chevron-left text-[9px]"></i></a>
                                        @endif

                                        @foreach ($pages as $p)
                                            @if ($p === '...')
                                                <span class="disabled">…</span>
                                            @elseif ($p == $current)
                                                <span class="active text-mono">{{ $p }}</span>
                                            @else
                                                <a class="text-mono" href="{{ $users->url($p) }}">{{ $p }}</a>
                                            @endif
                                        @endforeach

                                        @if ($users->hasMorePages())
                                            <a href="{{ $users->nextPageUrl() }}"><i class="fa-solid fa-chevron-right text-[9px]"></i></a>
                                        @else
                                            <span class="disabled"><i class="fa-solid fa-chevron-right text-[9px]"></i></span>
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

    {{-- Modal: add user --}}
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
                @csrf
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

    {{-- Modal: edit role --}}
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
                @csrf
                @method('PUT')
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
    @include('components.sidebar-scripts')
</body>

</html>
