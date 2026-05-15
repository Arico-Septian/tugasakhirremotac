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

    /* 2025 Modern Wallpaper — clean, harmonious color palette with darker background */
    html, body { height: 100%; overflow: hidden; }
    body {
        background:
            /* Subtle cyan glow from top — primary accent */
            radial-gradient(800px 400px at 50% -20%, rgba(78, 215, 255, 0.10), transparent 60%),
            /* Complementary indigo glow from bottom-right — secondary accent */
            radial-gradient(600px 350px at 85% 110%, rgba(139, 162, 255, 0.06), transparent 65%),
            /* Darker gradient overlay for better visibility */
            linear-gradient(135deg,
                rgba(6, 10, 22, 0.72) 0%,
                rgba(9, 15, 36, 0.70) 25%,
                rgba(10, 18, 42, 0.68) 50%,
                rgba(8, 13, 34, 0.71) 75%,
                rgba(6, 10, 26, 0.74) 100%
            ),
            /* Wallpaper image — texture underneath */
            url('/images/wallpaper.jpeg') center/cover no-repeat fixed !important;
        background-blend-mode: multiply;
    }

    /* Modern color system — harmonious palette */
    :root {
        --panel-1: rgba(13, 21, 44, 0.93) !important;
        --panel-2: rgba(15, 25, 52, 0.95) !important;
        --bg-1:    #0d1530 !important;
    }

    /* Main content area with darker overlay */
    .main-content {
        background:
            radial-gradient(700px 280px at 50% 50%, rgba(78, 215, 255, 0.03), transparent 70%),
            rgba(6, 10, 22, 0.38);
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
        /* Clean modern header with subtle glow */
        background:
            /* Subtle cyan glow */
            radial-gradient(900px 300px at 50% -40%, rgba(78, 215, 255, 0.10), transparent 65%),
            /* Clean gradient — dark blue base */
            linear-gradient(180deg, rgba(13, 22, 46, 0.98) 0%, rgba(11, 18, 38, 0.96) 100%) !important;
        border-bottom: 1px solid rgba(78, 215, 255, 0.10) !important;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.30), inset 0 1px 0 rgba(78, 215, 255, 0.08);
        color: var(--ink-0);
        position: sticky; top: 0;
        z-index: 30;
    }
    /* Top accent line — subtle, elegant */
    .main-header::before {
        content: '';
        position: absolute;
        top: 0; left: 16%; right: 16%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(78, 215, 255, 0.30), transparent);
        pointer-events: none;
        z-index: 2;
    }
    /* Bottom accent line — complementary indigo */
    .main-header::after {
        content: '';
        position: absolute;
        left: 16%; right: 16%; bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(139, 162, 255, 0.14), transparent);
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

    /* Clean modern status pill with glassmorphism */
    .main-header .pill {
        padding: 6px 12px !important;
        border-radius: 999px !important;
        font-size: 11.5px !important;
        font-weight: 600;
        letter-spacing: 0.02em;
        background: rgba(78, 215, 255, 0.09) !important;
        border: 1px solid rgba(78, 215, 255, 0.18) !important;
        -webkit-backdrop-filter: blur(12px);
        backdrop-filter: blur(12px);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .main-header .pill .dot {
        width: 6px; height: 6px;
        margin-right: 2px;
    }

    /* Clean modern notification bell */
    #notifBellBtn,
    .main-header .btn-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: rgba(78, 215, 255, 0.06) !important;
        border: 1px solid rgba(78, 215, 255, 0.12) !important;
        color: #7a94b8 !important;
        transition: all 0.22s ease !important;
    }
    #notifBellBtn:hover,
    .main-header .btn-icon:hover {
        background: rgba(78, 215, 255, 0.12) !important;
        border-color: rgba(78, 215, 255, 0.30) !important;
        color: #4ed9ff !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 16px -2px rgba(78, 215, 255, 0.30);
    }

    /* Clean notification badge */
    #notifBadge,
    .notif-badge {
        background: linear-gradient(135deg, #ff6b7a, #ff5566) !important;
        color: #fff !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        min-width: 18px;
        height: 18px !important;
        border-radius: 999px !important;
        border: 2px solid rgba(10, 14, 28, 0.96) !important;
        box-shadow: 0 4px 12px -2px rgba(255, 107, 122, 0.50) !important;
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

    /* Clean modern backdrop with glassmorphism */
    #overlay {
        position: fixed; inset: 0; z-index: 40;
        background:
            radial-gradient(500px 350px at 50% 50%, rgba(78, 215, 255, 0.02), transparent 70%),
            rgba(0, 0, 0, 0.62);
        -webkit-backdrop-filter: blur(5px);
        backdrop-filter: blur(5px);
        opacity: 0; pointer-events: none;
        transition: opacity .25s cubic-bezier(.4,0,.2,1);
    }
    #overlay.active { opacity: 1; pointer-events: auto; }

    .custom-bg { display: none; }

    /* Clean modern sidebar with harmonious colors */
    .app-sidebar {
        background:
            /* Subtle cyan glow from top */
            radial-gradient(550px 380px at 50% -25%, rgba(78, 215, 255, 0.10), transparent 65%),
            /* Subtle indigo glow from bottom */
            radial-gradient(450px 300px at 15% 125%, rgba(139, 162, 255, 0.06), transparent 65%),
            /* Clean dark blue gradient */
            linear-gradient(180deg, rgba(12, 20, 42, 0.98), rgba(10, 16, 36, 0.99)) !important;
        border-right: 1px solid rgba(78, 215, 255, 0.10) !important;
        box-shadow: 0 0 28px rgba(0, 0, 0, 0.35), inset -1px 0 0 rgba(78, 215, 255, 0.06);
    }

    /* Top accent line — subtle and clean */
    .app-sidebar::before {
        content: '';
        position: absolute;
        top: 0; left: 16%; right: 16%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(78, 215, 255, 0.28), transparent);
        pointer-events: none;
        z-index: 2;
    }

    /* Clean, harmonious brand section */
    .brand {
        border-bottom: 1px solid rgba(78, 215, 255, 0.10) !important;
        position: relative;
        background: linear-gradient(180deg, rgba(18, 28, 56, 0.5), rgba(12, 18, 40, 0.15));
    }
    .brand::after {
        content: '';
        position: absolute;
        left: 18px; right: 18px; bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(78, 215, 255, 0.20), rgba(139, 162, 255, 0.10), transparent);
    }
    .brand-logo {
        background: conic-gradient(from 220deg, #4ed9ff, #8ba2ff, #6ee7b7, #4ed9ff) !important;
        box-shadow: 0 10px 32px -8px rgba(78, 215, 255, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.30) !important;
        position: relative;
    }
    .brand-logo::after {
        content: '';
        position: absolute;
        inset: 3px;
        border-radius: 8px;
        background: rgba(8, 14, 32, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .brand-logo i {
        position: relative;
        z-index: 2;
        color: #4ed9ff;
        filter: drop-shadow(0 0 6px rgba(78, 215, 255, 0.50));
    }
    .brand-text .sub {
        background: linear-gradient(90deg, #4ed9ff, #8ba2ff);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-weight: 700 !important;
    }

    /* Clean nav section labels */
    .nav-section-label {
        font-size: 9.5px !important;
        letter-spacing: 0.16em !important;
        color: #7a94b8 !important;
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
        background: linear-gradient(90deg, rgba(78, 215, 255, 0.40), transparent);
        flex-shrink: 0;
    }

    /* Clean nav links with subtle, modern interactions */
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
        background: rgba(78, 215, 255, 0.04);
        border: 1px solid rgba(78, 215, 255, 0.06);
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 12px !important;
        color: #7a94b8 !important;
        transition: all 0.22s ease !important;
        flex-shrink: 0;
    }

    .nav-link:hover {
        background: rgba(78, 215, 255, 0.08) !important;
        color: var(--ink-0) !important;
        transform: translateX(2px);
    }
    .nav-link:hover i {
        background: linear-gradient(135deg, rgba(78, 215, 255, 0.14), rgba(139, 162, 255, 0.08));
        border-color: rgba(78, 215, 255, 0.28);
        color: #4ed9ff !important;
    }

    .nav-link.active {
        background:
            linear-gradient(90deg, rgba(78, 215, 255, 0.12) 0%, rgba(78, 215, 255, 0.04) 100%) !important;
        color: var(--ink-0) !important;
        font-weight: 600 !important;
        box-shadow: inset 0 1px 0 rgba(78, 215, 255, 0.10);
    }
    .nav-link.active i {
        background: linear-gradient(135deg, rgba(78, 215, 255, 0.20), rgba(139, 162, 255, 0.14));
        border-color: rgba(78, 215, 255, 0.38);
        color: #4ed9ff !important;
        box-shadow: 0 0 14px -2px rgba(78, 215, 255, 0.40);
    }
    .nav-link.active::before {
        width: 3px !important;
        background: linear-gradient(180deg, #4ed9ff, #8ba2ff) !important;
        top: 10px !important;
        bottom: 10px !important;
        border-radius: 0 3px 3px 0 !important;
        box-shadow: 0 0 12px rgba(78, 215, 255, 0.45);
    }

    /* Collapsed sidebar — icon-only state */
    .app-sidebar.collapsed .nav-link i { margin: 0 auto; }

    /* Clean modern sidebar footer */
    .sidebar-footer {
        border-top: 1px solid rgba(78, 215, 255, 0.10) !important;
        position: relative;
        background: linear-gradient(180deg, rgba(12, 20, 42, 0.06) 0%, rgba(0, 0, 0, 0.20) 100%);
    }
    .sidebar-footer::before {
        content: '';
        position: absolute;
        left: 18px; right: 18px; top: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(78, 215, 255, 0.16), rgba(139, 162, 255, 0.08), transparent);
    }

    .profile-full {
        border-radius: 11px !important;
        padding: 8px 10px !important;
        transition: all 0.22s ease !important;
    }
    .profile-full:hover {
        background: rgba(78, 215, 255, 0.08) !important;
    }
    .profile-full .avatar {
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .profile-full:hover .avatar {
        transform: scale(1.08);
        box-shadow: 0 6px 18px -4px rgba(78, 215, 255, 0.40);
    }
    .profile-info .role {
        font-size: 10px !important;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #7a94b8 !important;
        font-weight: 600;
        margin-top: 2px;
    }
    .icon-btn.danger {
        background: rgba(255, 107, 122, 0.10) !important;
        border: 1px solid rgba(255, 107, 122, 0.20) !important;
        color: #ff6b7a !important;
        border-radius: 8px !important;
        transition: all 0.22s ease;
    }
    .icon-btn.danger:hover {
        background: rgba(255, 107, 122, 0.16) !important;
        border-color: rgba(255, 107, 122, 0.35) !important;
        transform: translateY(-1px);
    }
</style>
