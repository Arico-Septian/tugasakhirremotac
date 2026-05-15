{{-- Layout shim: sidebar/header behavior. Most styling lives in app.css. --}}
<style>
    /* Hide all visual scrollbars (scroll wheel/touch tetap berfungsi) */
    * {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    *::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }

    /* 2025 Modern Wallpaper — vibrant layered gradients with dynamic lighting */
    html, body { height: 100%; overflow: hidden; }
    body {
        background:
            /* Vibrant cyan glow from top-left — primary accent */
            radial-gradient(700px 450px at 8% -15%, rgba(94, 208, 255, 0.18), transparent 55%),
            /* Rich purple/lavender glow from bottom-right — secondary accent */
            radial-gradient(550px 380px at 88% 115%, rgba(180, 163, 255, 0.14), transparent 60%),
            /* Warm accent from bottom-left — adds richness */
            radial-gradient(400px 300px at -5% 95%, rgba(251, 191, 36, 0.06), transparent 65%),
            /* Dynamic linear gradient with richer tones */
            linear-gradient(135deg,
                rgba(8, 15, 35, 0.62) 0%,
                rgba(12, 20, 45, 0.58) 25%,
                rgba(14, 26, 52, 0.54) 50%,
                rgba(10, 18, 40, 0.60) 75%,
                rgba(8, 14, 32, 0.64) 100%
            ),
            /* Wallpaper image — texture underneath */
            url('/images/wallpaper.jpeg') center/cover no-repeat fixed !important;
        background-blend-mode: multiply;
    }

    /* Enhanced color system with richer palette */
    :root {
        --panel-1: rgba(16, 28, 56, 0.94) !important;
        --panel-2: rgba(18, 34, 68, 0.96) !important;
        --bg-1:    #0f1a36 !important;
    }

    /* Main content area with vibrant glow overlay */
    .main-content {
        background:
            radial-gradient(600px 300px at 50% 50%, rgba(94, 208, 255, 0.06), transparent 70%),
            rgba(8, 14, 32, 0.32);
    }

    /* Backwards-compat aliases for legacy class names used across pages */
    .layout         { display: flex; min-height: 100vh; width: 100vw; position: relative; }
    .main-content   {
        margin-left: 248px;
        width: calc(100% - 248px);
        height: 100vh;
        display: flex;
        flex-direction: column;
        transition: margin-left .25s cubic-bezier(.4,0,.2,1), width .25s cubic-bezier(.4,0,.2,1);
        overflow: hidden;
    }
    .sidebar.close ~ .main-content,
    .app-sidebar.collapsed ~ .main-content {
        margin-left: 76px;
        width: calc(100% - 76px);
    }

    .main-header {
        flex-shrink: 0;
        height: 64px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 24px;
        /* 2025 header with vibrant glows and dynamic lighting */
        background:
            /* Cyan glow layer */
            radial-gradient(900px 300px at 50% -40%, rgba(94, 208, 255, 0.18), transparent 65%),
            /* Primary gradient with richer tone */
            linear-gradient(180deg, rgba(16, 32, 64, 0.99) 0%, rgba(12, 24, 52, 0.97) 100%) !important;
        border-bottom: 1px solid rgba(94, 208, 255, 0.14) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(94, 208, 255, 0.12);
        color: var(--ink-0);
        position: sticky; top: 0;
        z-index: 30;
    }
    /* Top accent line — vibrant glow */
    .main-header::before {
        content: '';
        position: absolute;
        top: 0; left: 16%; right: 16%;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(94, 208, 255, 0.50), transparent);
        filter: blur(0.5px);
        pointer-events: none;
        z-index: 2;
    }
    /* Bottom accent line — complementary glow */
    .main-header::after {
        content: '';
        position: absolute;
        left: 16%; right: 16%; bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(180, 163, 255, 0.28), transparent);
        pointer-events: none;
    }
    @media (max-width: 1024px) { .main-header { padding: 0 16px; } }

    /* Header title */
    .main-header .app-header-title h1 {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: var(--ink-0) !important;
        letter-spacing: -0.015em !important;
        margin: 0;
        line-height: 1.15;
    }
    .main-header .app-header-title p {
        font-size: 11.5px !important;
        color: var(--ink-3) !important;
        margin: 2px 0 0 !important;
        letter-spacing: 0.01em;
    }

    /* Right-cluster (notification bell + status pill) */
    .main-header > div:last-child {
        gap: 10px !important;
    }

    /* 2025 status pill with vibrant glassmorphism */
    .main-header .pill {
        padding: 6px 12px !important;
        border-radius: 999px !important;
        font-size: 11.5px !important;
        font-weight: 600;
        letter-spacing: 0.02em;
        background: rgba(94, 208, 255, 0.12) !important;
        border: 1px solid rgba(94, 208, 255, 0.26) !important;
        -webkit-backdrop-filter: blur(14px);
        backdrop-filter: blur(14px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    .main-header .pill .dot {
        width: 6px; height: 6px;
        margin-right: 2px;
    }

    /* 2025 notification bell with vibrant interactions */
    #notifBellBtn,
    .main-header .btn-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: rgba(94, 208, 255, 0.08) !important;
        border: 1px solid rgba(94, 208, 255, 0.16) !important;
        color: #8aa9c9 !important;
        transition: all 0.22s ease !important;
    }
    #notifBellBtn:hover,
    .main-header .btn-icon:hover {
        background: rgba(94, 208, 255, 0.16) !important;
        border-color: rgba(94, 208, 255, 0.40) !important;
        color: #5ed0ff !important;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px -2px rgba(94, 208, 255, 0.40);
    }

    /* 2025 notification badge with vibrant glow */
    #notifBadge,
    .notif-badge {
        background: linear-gradient(135deg, #ff6b9d, #ff4870) !important;
        color: #fff !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        min-width: 18px;
        height: 18px !important;
        border-radius: 999px !important;
        border: 2px solid rgba(10, 18, 36, 0.97) !important;
        box-shadow: 0 6px 18px -2px rgba(255, 75, 112, 0.70) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: -4px !important;
        right: -4px !important;
        padding: 0 4px;
    }

    .page-body {
        flex: 1;
        overflow-y: auto;
        scroll-behavior: smooth;
        padding-bottom: 24px;
        min-height: 0;
    }
    @media (max-width: 1024px) { .page-body { padding-bottom: 64px; } }

    /* Mobile sidebar */
    @media (max-width: 1024px) {
        .main-content   { margin-left: 0 !important; width: 100% !important; }
        .app-sidebar    { transform: translateX(-100%); width: 280px !important; }
        .app-sidebar.open { transform: translateX(0); box-shadow: 0 24px 48px rgba(0,0,0,.40); }
        .sidebar-toggle.desktop-only { display: none !important; }
    }

    /* 2025 backdrop with enhanced glassmorphism */
    #overlay {
        position: fixed; inset: 0; z-index: 40;
        background:
            radial-gradient(500px 400px at 50% 50%, rgba(94, 208, 255, 0.04), transparent 70%),
            rgba(0, 0, 0, 0.65);
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
        opacity: 0; pointer-events: none;
        transition: opacity .25s cubic-bezier(.4,0,.2,1);
    }
    #overlay.active { opacity: 1; pointer-events: auto; }

    .custom-bg { display: none; }

    /* 2025 sidebar with vibrant accent glows and dynamic depth */
    .app-sidebar {
        background:
            /* Cyan glow from top */
            radial-gradient(550px 400px at 50% -25%, rgba(94, 208, 255, 0.14), transparent 65%),
            /* Lavender glow from bottom-left */
            radial-gradient(450px 320px at 15% 125%, rgba(180, 163, 255, 0.12), transparent 65%),
            /* Primary gradient with richer tone */
            linear-gradient(180deg, rgba(14, 28, 56, 0.99), rgba(10, 20, 44, 1.0)) !important;
        border-right: 1px solid rgba(94, 208, 255, 0.14) !important;
        box-shadow: 0 0 32px rgba(0, 0, 0, 0.40), inset -1px 0 0 rgba(94, 208, 255, 0.10);
    }

    /* Top accent line with vibrant glow */
    .app-sidebar::before {
        content: '';
        position: absolute;
        top: 0; left: 16%; right: 16%;
        height: 2px;
        background: linear-gradient(90deg, transparent, rgba(94, 208, 255, 0.50), transparent);
        filter: blur(0.5px);
        pointer-events: none;
        z-index: 2;
    }

    /* 2025 brand with vibrant visual impact */
    .brand {
        border-bottom: 1px solid rgba(94, 208, 255, 0.14) !important;
        position: relative;
        background: linear-gradient(180deg, rgba(24, 40, 72, 0.6), rgba(12, 24, 48, 0.2));
    }
    .brand::after {
        content: '';
        position: absolute;
        left: 18px; right: 18px; bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(94, 208, 255, 0.32), rgba(180, 163, 255, 0.16), transparent);
    }
    .brand-logo {
        background: conic-gradient(from 220deg, #4dd9ff, #b8a3ff, #ff6b9d, #ffc857, #7aeed9, #4dd9ff) !important;
        box-shadow: 0 12px 40px -8px rgba(94, 208, 255, 0.60), 0 0 20px -4px rgba(180, 163, 255, 0.30), inset 0 1px 0 rgba(255, 255, 255, 0.35) !important;
        position: relative;
    }
    .brand-logo::after {
        content: '';
        position: absolute;
        inset: 3px;
        border-radius: 8px;
        background: rgba(8, 16, 36, 0.96);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .brand-logo i {
        position: relative;
        z-index: 2;
        color: #5ed0ff;
        filter: drop-shadow(0 0 8px rgba(94, 208, 255, 0.6));
    }
    .brand-text .sub {
        background: linear-gradient(90deg, #5ed0ff, #b8a3ff);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-weight: 700 !important;
    }

    /* 2025 nav section labels with vibrant accents */
    .nav-section-label {
        font-size: 9.5px !important;
        letter-spacing: 0.16em !important;
        color: #7fa5c4 !important;
        padding: 14px 12px 8px !important;
        margin-top: 8px !important;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        font-weight: 500;
    }
    .nav-section-label::before {
        content: '';
        width: 14px;
        height: 1px;
        background: linear-gradient(90deg, rgba(94, 208, 255, 0.60), transparent);
        flex-shrink: 0;
    }

    /* 2025 nav links with vibrant interactions and smooth animations */
    .nav-list { gap: 3px !important; }
    .nav-link {
        padding: 10px 12px !important;
        border-radius: 11px !important;
        font-size: 13px !important;
        position: relative;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .nav-link i {
        width: 28px !important; height: 28px !important;
        border-radius: 8px;
        background: rgba(94, 208, 255, 0.05);
        border: 1px solid rgba(94, 208, 255, 0.08);
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 12px !important;
        color: #8aa9c9 !important;
        transition: all 0.22s ease !important;
        flex-shrink: 0;
    }

    .nav-link:hover {
        background: rgba(94, 208, 255, 0.10) !important;
        color: var(--ink-0) !important;
        transform: translateX(2px);
    }
    .nav-link:hover i {
        background: linear-gradient(135deg, rgba(94, 208, 255, 0.18), rgba(180, 163, 255, 0.12));
        border-color: rgba(94, 208, 255, 0.36);
        color: #5ed0ff !important;
    }

    .nav-link.active {
        background:
            linear-gradient(90deg, rgba(94, 208, 255, 0.18) 0%, rgba(94, 208, 255, 0.06) 100%) !important;
        color: var(--ink-0) !important;
        font-weight: 600 !important;
        box-shadow: inset 0 1px 0 rgba(94, 208, 255, 0.14);
    }
    .nav-link.active i {
        background: linear-gradient(135deg, rgba(94, 208, 255, 0.28), rgba(180, 163, 255, 0.22));
        border-color: rgba(94, 208, 255, 0.50);
        color: #5ed0ff !important;
        box-shadow: 0 0 18px -2px rgba(94, 208, 255, 0.55);
    }
    .nav-link.active::before {
        width: 3px !important;
        background: linear-gradient(180deg, #5ed0ff, #b8a3ff) !important;
        top: 10px !important;
        bottom: 10px !important;
        border-radius: 0 3px 3px 0 !important;
        box-shadow: 0 0 16px rgba(94, 208, 255, 0.65);
    }

    /* Collapsed sidebar — icon-only state */
    .app-sidebar.collapsed .nav-link i { margin: 0 auto; }

    /* 2025 sidebar footer with vibrant accent borders */
    .sidebar-footer {
        border-top: 1px solid rgba(94, 208, 255, 0.14) !important;
        position: relative;
        background: linear-gradient(180deg, rgba(16, 28, 56, 0.08) 0%, rgba(0, 0, 0, 0.30) 100%);
    }
    .sidebar-footer::before {
        content: '';
        position: absolute;
        left: 18px; right: 18px; top: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(180, 163, 255, 0.32), rgba(94, 208, 255, 0.20), transparent);
    }

    .profile-full {
        border-radius: 11px !important;
        padding: 8px 10px !important;
        transition: all 0.22s ease !important;
    }
    .profile-full:hover {
        background: rgba(94, 208, 255, 0.10) !important;
    }
    .profile-full .avatar {
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .profile-full:hover .avatar {
        transform: scale(1.10);
        box-shadow: 0 8px 24px -4px rgba(94, 208, 255, 0.65);
    }
    .profile-info .role {
        font-size: 10px !important;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7fa5c4 !important;
        font-weight: 600;
        margin-top: 2px;
    }
    .icon-btn.danger {
        background: rgba(255, 107, 157, 0.12) !important;
        border: 1px solid rgba(255, 107, 157, 0.28) !important;
        color: #ff6b9d !important;
        border-radius: 8px !important;
        transition: all 0.22s ease;
    }
    .icon-btn.danger:hover {
        background: rgba(255, 107, 157, 0.20) !important;
        border-color: rgba(255, 107, 157, 0.45) !important;
        transform: translateY(-1px);
    }
</style>
