<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Apply theme before any paint to avoid flash of dark/light --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme') || 'dark';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    @include('components.sidebar-styles')

    <style>
        /* ===== Theme toggle button ===== */
        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--panel-2);
            border: 1px solid var(--line-soft);
            color: var(--ink-1);
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .theme-toggle:hover {
            border-color: var(--line);
            color: var(--ink-0);
        }
        .theme-toggle .icon-sun { display: none; }
        .theme-toggle .icon-moon { display: inline-block; }
        html[data-theme="light"] .theme-toggle .icon-sun { display: inline-block; }
        html[data-theme="light"] .theme-toggle .icon-moon { display: none; }

        /* ===== Light theme overrides (POC: profile page) =====
           Higher specificity (html[data-theme]) + !important to beat sidebar-styles. */
        html[data-theme="light"] {
            --bg-0: #f5f7fa !important;
            --bg-1: #ffffff !important;
            --bg-2: #f8fafc !important;
            --bg-3: #e2e8f0 !important;

            --panel-1: rgba(255, 255, 255, 0.95) !important;
            --panel-2: rgba(255, 255, 255, 0.85) !important;
            --panel-3: rgba(15, 23, 42, 0.04) !important;

            --line-soft:   rgba(15, 23, 42, 0.06) !important;
            --line:        rgba(15, 23, 42, 0.10) !important;
            --line-strong: rgba(15, 23, 42, 0.18) !important;

            --ink-0: #0f172a !important;
            --ink-1: #1e293b !important;
            --ink-2: #475569 !important;
            --ink-3: #64748b !important;
            --ink-4: #94a3b8 !important;

            --cyan:     #0891b2 !important;
            --mint:     #059669 !important;
            --lavender: #7c3aed !important;
            --coral:    #e11d48 !important;
            --amber:    #d97706 !important;
        }

        /* Light theme: replace dark wallpaper + tint */
        html[data-theme="light"] body {
            background:
                linear-gradient(rgba(241, 245, 249, 0.7), rgba(241, 245, 249, 0.7)),
                url('/images/wallpaper.jpeg') center/cover no-repeat fixed !important;
        }

        html[data-theme="light"] .main-content {
            background: rgba(255, 255, 255, 0.55) !important;
        }

        html[data-theme="light"] .custom-bg {
            background: transparent !important;
        }

        /* Panels & inputs in light mode */
        html[data-theme="light"] .panel,
        html[data-theme="light"] .panel-lg {
            background: rgba(255, 255, 255, 0.92) !important;
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.03) !important;
        }

        html[data-theme="light"] .input {
            background: #ffffff !important;
            border: 1px solid rgba(15, 23, 42, 0.12) !important;
            color: #0f172a !important;
        }

        html[data-theme="light"] .input::placeholder {
            color: #94a3b8 !important;
        }

        html[data-theme="light"] .btn-icon {
            background: rgba(15, 23, 42, 0.04) !important;
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            color: #475569 !important;
        }

        html[data-theme="light"] .btn-icon:hover {
            background: rgba(15, 23, 42, 0.08) !important;
            color: #0f172a !important;
        }

        html[data-theme="light"] .main-header {
            background: rgba(255, 255, 255, 0.75) !important;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06) !important;
            backdrop-filter: blur(12px);
        }

        html[data-theme="light"] .theme-toggle {
            background: rgba(15, 23, 42, 0.04) !important;
            border: 1px solid rgba(15, 23, 42, 0.08) !important;
            color: #475569 !important;
        }

        html[data-theme="light"] .theme-toggle:hover {
            color: #0f172a !important;
        }

        html[data-theme="light"] .avatar {
            background: linear-gradient(135deg, #0891b2, #7c3aed) !important;
            color: #ffffff !important;
        }

        html[data-theme="light"] .badge-role.admin {
            background: rgba(225, 29, 72, 0.12) !important;
            color: #be123c !important;
        }

        html[data-theme="light"] .badge-role.operator {
            background: rgba(217, 119, 6, 0.12) !important;
            color: #b45309 !important;
        }

        html[data-theme="light"] .badge-role.user {
            background: rgba(8, 145, 178, 0.12) !important;
            color: #0e7490 !important;
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
                    <h1>My Profile</h1>
                    <p>Account &amp; security settings</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark mode">
                    <i class="fa-solid fa-moon text-xs icon-moon"></i>
                    <i class="fa-solid fa-sun text-xs icon-sun"></i>
                </button>
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner" style="max-width:640px;margin:0 auto;">

                    {{-- Identity card --}}
                    <div class="panel panel-lg" style="display:flex;align-items:center;gap:18px;">
                        <div class="avatar-wrap" style="position:relative;flex-shrink:0;">
                            @if ($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                     class="avatar avatar-xl"
                                     style="object-fit:cover;width:64px;height:64px;border-radius:999px;">
                            @else
                                <div class="avatar avatar-xl">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            @endif
                            <button type="button" id="avatarBtn"
                                    title="{{ $user->avatar ? 'Ubah foto' : 'Tambah foto' }}"
                                    onclick="document.getElementById('avatarInput').click()"
                                    style="position:absolute;right:-2px;bottom:-2px;width:26px;height:26px;border-radius:999px;background:var(--cyan);border:2px solid var(--panel-1);color:#0b1220;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-camera text-[10px]"></i>
                            </button>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 style="font-size:18px;font-weight:600;color:var(--ink-0);margin:0;letter-spacing:-0.01em;">{{ $user->name }}</h2>
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="badge-role {{ $user->role }}">{{ strtoupper($user->role) }}</span>
                                @if ($user->last_activity)
                                    <span class="text-xs" style="color:var(--ink-3);">
                                        Last active {{ $user->last_activity->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            @if ($user->avatar)
                                <form method="POST" action="{{ route('profile.avatar.delete') }}" style="margin-top:8px;display:inline-block;"
                                      onsubmit="return confirm('Hapus foto profil?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="background:transparent;border:none;color:var(--coral);font-size:11px;font-weight:600;cursor:pointer;padding:0;">
                                        <i class="fa-solid fa-trash text-[9px]"></i> Hapus foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Hidden avatar upload form (auto-submits on file select) --}}
                    <form id="avatarForm" method="POST" action="{{ route('profile.avatar.upload') }}"
                          enctype="multipart/form-data" style="display:none;">
                        @csrf
                        <input type="file" id="avatarInput" name="avatar"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="handleAvatarSelect(this)">
                    </form>

                    {{-- Change password --}}
                    <div class="panel panel-lg mt-4">
                        <div class="panel-header" style="margin-bottom:16px;">
                            <div>
                                <p class="eyebrow"><i class="fa-solid fa-key"></i> Security</p>
                                <h3 class="panel-title">Change password</h3>
                                <p class="panel-subtitle">Pilih password baru yang kuat dan tidak mudah ditebak</p>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success mb-4">
                                <i class="fa-solid fa-circle-check alert-icon"></i>
                                <div class="alert-body">{{ session('success') }}</div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-error mb-4">
                                <i class="fa-solid fa-circle-exclamation alert-icon"></i>
                                <div class="alert-body">{{ $errors->first() }}</div>
                            </div>
                        @endif

                        <form method="POST" action="/change-password" id="pwForm" class="space-y-4">
                            @csrf
                            <div class="field">
                                <label class="field-label">New password</label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock"></i>
                                    <input class="input" type="password" name="password" id="pwInput"
                                           placeholder="Min. 6 characters" required minlength="6" style="padding-right:42px;">
                                    <button type="button" onclick="togglePw('pwInput', this)"
                                            class="btn-icon" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:transparent;border-color:transparent;">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="field">
                                <label class="field-label">Confirm new password</label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock"></i>
                                    <input class="input" type="password" name="password_confirmation" id="pwConfirm"
                                           placeholder="Ulangi password baru" required style="padding-right:42px;">
                                    <button type="button" onclick="togglePw('pwConfirm', this)"
                                            class="btn-icon" style="position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;background:transparent;border-color:transparent;">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" id="pwBtn">
                                Update password
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
function handleAvatarSelect(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2 MB.');
        input.value = '';
        return;
    }
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowed.includes(file.type)) {
        alert('Format yang didukung: JPG, PNG, WEBP.');
        input.value = '';
        return;
    }
    document.getElementById('avatarForm').submit();
}

function toggleTheme() {
    const root = document.documentElement;
    const current = root.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try { localStorage.setItem('theme', next); } catch (e) {}
}

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.getElementById('pwForm')?.addEventListener('submit', function (e) {
    const pw  = document.getElementById('pwInput').value;
    const pw2 = document.getElementById('pwConfirm').value;
    if (pw !== pw2) {
        e.preventDefault();
        window.smToast('Password dan konfirmasi tidak cocok', 'error');
    }
});
</script>
@include('components.sidebar-scripts')
</body>
</html>
