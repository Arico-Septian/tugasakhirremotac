<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Profile â€” SmartAC</title>
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

                    {{-- Activity Summary --}}
                    @if ($recentActivities->count() > 0 || $activitiesThisMonth > 0)
                    <div class="panel panel-lg mt-4">
                        <div class="panel-header" style="margin-bottom:16px;">
                            <p class="eyebrow"><i class="fa-solid fa-history"></i> Activity</p>
                            <h3 class="panel-title">Recent Activities</h3>
                            <p class="panel-subtitle">{{ $activitiesThisMonth }} activities this month</p>
                        </div>
                        @if ($recentActivities->count() > 0)
                            <div style="border-radius:10px;overflow:hidden;">
                                @foreach ($recentActivities as $activity)
                                <div style="padding:12px;border-bottom:1px solid rgba(14, 165, 233, 0.08);display:flex;justify-content:space-between;align-items:center;">
                                    <div>
                                        <p style="font-size:13px;font-weight:600;color:var(--ink-0);margin:0;">
                                            {{ $activity->activity }}
                                        </p>
                                        <p style="font-size:12px;color:var(--ink-3);margin:4px 0 0 0;">
                                            <span style="display:inline-block;margin-right:8px;">ðŸ  {{ $activity->room ?? '-' }}</span>
                                            @if ($activity->ac)
                                            <span style="display:inline-block;">â„ï¸ AC {{ $activity->ac }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <p style="font-size:11px;color:var(--ink-4);white-space:nowrap;margin-left:12px;">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p style="color:var(--ink-3);text-align:center;padding:20px;margin:0;">Belum ada aktivitas</p>
                        @endif
                    </div>
                    @endif

                    {{-- Role-Based Statistics --}}
                    @if (count($stats) > 0)
                    <div class="panel panel-lg mt-4">
                        <div class="panel-header" style="margin-bottom:16px;">
                            <p class="eyebrow"><i class="fa-solid fa-chart-bar"></i> Statistics</p>
                            <h3 class="panel-title">
                                @if ($user->isAdmin())
                                    System Overview
                                @else
                                    My Statistics
                                @endif
                            </h3>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;">
                            @foreach ($stats as $key => $value)
                            <div style="padding:16px;border-radius:10px;background:rgba(14, 165, 233, 0.06);border:1px solid rgba(14, 165, 233, 0.12);">
                                <p style="font-size:11px;color:var(--ink-3);text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:8px;">
                                    {{ str_replace('_', ' ', $key) }}
                                </p>
                                <p style="font-size:24px;font-weight:700;color:#0ea5e9;margin:0;">
                                    {{ $value }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif


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

