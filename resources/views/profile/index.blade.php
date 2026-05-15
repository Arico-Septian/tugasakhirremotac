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
                    <i class="fa-solid fa-bars text-xs"></i>
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

</script>
@include('components.sidebar-scripts')
</body>
</html>
