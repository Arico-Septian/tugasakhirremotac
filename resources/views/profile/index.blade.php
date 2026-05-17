<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile — SmartAC</title>
    <link href="/css/app.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite('resources/js/app.js')


    @include('components.sidebar-styles')

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
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="app-header-title">
                    <h1>My Profile</h1>
                    <p>Account &amp; security settings</p>
                </div>
            </div>
        </header>

        <div class="page-body">
            <div class="app-content">
                <div class="app-content-inner" style="max-width:640px;margin:0 auto;">

                    {{-- Identity Card --}}
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
                                    style="position:absolute;right:-2px;bottom:-2px;width:26px;height:26px;border-radius:999px;background:#0ea5e9;border:2px solid var(--panel-1);color:#0b1220;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.25);">
                                <i class="fa-solid fa-camera text-[10px]"></i>
                            </button>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 style="font-size:18px;font-weight:600;color:var(--ink-0);margin:0;letter-spacing:-0.01em;">{{ $user->name }}</h2>
                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                <span class="badge-role {{ $user->role }}" style="padding:4px 10px;border-radius:6px;font-size:10px;font-weight:600;text-transform:uppercase;">{{ $user->role }}</span>
                            </div>
                            @if ($user->avatar)
                                <form method="POST" action="{{ route('profile.avatar.delete') }}" style="margin-top:8px;display:inline-block;"
                                      onsubmit="return confirm('Hapus foto profil?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            style="background:transparent;border:none;color:#ff5577;font-size:11px;font-weight:600;cursor:pointer;padding:0;">
                                        <i class="fa-solid fa-trash text-[9px]"></i> Hapus foto
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Hidden avatar upload form --}}
                    <form id="avatarForm" method="POST" action="{{ route('profile.avatar.upload') }}"
                          enctype="multipart/form-data" style="display:none;">
                        @csrf
                        <input type="file" id="avatarInput" name="avatar"
                               accept="image/jpeg,image/png,image/webp"
                               onchange="handleAvatarSelect(this)">
                    </form>

                    {{-- Avatar preview modal --}}
                    <div id="avatarPreviewModal"
                         style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(7,16,31,0.72);backdrop-filter:blur(6px);align-items:center;justify-content:center;padding:16px;">
                        <div style="max-width:360px;width:100%;background:var(--panel-1);border:1px solid var(--line);border-radius:18px;padding:22px;box-shadow:0 20px 60px -20px rgba(0,0,0,0.6);">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                                <span style="width:34px;height:34px;border-radius:10px;background:var(--cyan-soft);border:1px solid var(--cyan-soft-2);display:inline-flex;align-items:center;justify-content:center;color:var(--cyan);">
                                    <i class="fa-solid fa-camera"></i>
                                </span>
                                <div>
                                    <h3 style="margin:0;font-size:15px;font-weight:700;color:var(--ink-0);">Konfirmasi Foto Profil</h3>
                                    <p style="margin:2px 0 0;font-size:12px;color:var(--ink-3);">Cek preview di bawah sebelum upload</p>
                                </div>
                            </div>
                            <div style="display:flex;justify-content:center;margin-bottom:14px;">
                                <img id="avatarPreviewImg" alt="Preview"
                                     style="width:160px;height:160px;border-radius:14px;object-fit:cover;border:1px solid var(--line);background:var(--panel-2);">
                            </div>
                            <p id="avatarPreviewMeta" style="margin:0 0 14px;font-size:12px;color:var(--ink-3);text-align:center;"></p>
                            <div style="display:flex;gap:8px;">
                                <button type="button" id="avatarPreviewCancel" class="btn btn-ghost" style="flex:1;">Batal</button>
                                <button type="button" id="avatarPreviewConfirm" class="btn btn-primary" style="flex:1;">
                                    <i class="fa-solid fa-cloud-arrow-up text-[11px]"></i>
                                    <span>Upload</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Account Information --}}
                    <div class="panel panel-lg mt-4">
                        <div class="panel-header" style="margin-bottom:16px;">
                            <p class="eyebrow"><i class="fa-solid fa-user-circle"></i> Account Information</p>
                            <h3 class="panel-title">Account Details</h3>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <p style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Last Login</p>
                                <p style="font-size:14px;color:var(--ink-0);margin:0;">
                                    {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                                </p>
                            </div>
                            <div>
                                <p style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Last Activity</p>
                                <p style="font-size:14px;color:var(--ink-0);margin:0;">
                                    {{ $user->last_activity ? $user->last_activity->diffForHumans() : '-' }}
                                </p>
                            </div>
                            <div>
                                <p style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:6px;">Join Date</p>
                                <p style="font-size:14px;color:var(--ink-0);margin:0;">
                                    {{ $user->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@include('components.bottom-nav')

<script>
(function () {
    const modal      = document.getElementById('avatarPreviewModal');
    const previewImg = document.getElementById('avatarPreviewImg');
    const previewMeta= document.getElementById('avatarPreviewMeta');
    const cancelBtn  = document.getElementById('avatarPreviewCancel');
    const confirmBtn = document.getElementById('avatarPreviewConfirm');
    const form       = document.getElementById('avatarForm');
    const input      = document.getElementById('avatarInput');
    let currentObjectUrl = null;

    function openModal() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeModal({ clearInput = true } = {}) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
        previewImg.src = '';
        if (clearInput) input.value = '';
    }
    function formatBytes(b) {
        if (b < 1024) return b + ' B';
        if (b < 1024 * 1024) return (b / 1024).toFixed(1) + ' KB';
        return (b / (1024 * 1024)).toFixed(2) + ' MB';
    }

    window.handleAvatarSelect = function (el) {
        const file = el.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            (window.smToast || alert)('Ukuran file maksimal 2 MB.', 'error');
            el.value = '';
            return;
        }
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            (window.smToast || alert)('Format yang didukung: JPG, PNG, WEBP.', 'error');
            el.value = '';
            return;
        }
        if (currentObjectUrl) URL.revokeObjectURL(currentObjectUrl);
        currentObjectUrl = URL.createObjectURL(file);
        previewImg.src = currentObjectUrl;
        previewMeta.textContent = `${file.name} · ${formatBytes(file.size)}`;
        openModal();
    };

    cancelBtn.addEventListener('click', () => closeModal());
    confirmBtn.addEventListener('click', () => {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[11px]"></i><span>Mengupload...</span>';
        closeModal({ clearInput: false });
        form.submit();
    });
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
})();
</script>
@include('components.sidebar-scripts')
</body>
</html>

