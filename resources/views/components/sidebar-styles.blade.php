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

    /* Wallpaper — gradient overlay di atas gambar dalam satu deklarasi background */
    html, body { height: 100%; overflow: hidden; }
    body {
        background:
            linear-gradient(rgba(7,16,31,0.55), rgba(7,16,31,0.55)),
            url('/images/wallpaper.jpeg') center/cover no-repeat fixed !important;
    }

    /* Override panel vars agar tidak transparan di atas wallpaper */
    :root {
        --panel-1: rgba(10, 20, 44, 0.88) !important;
        --panel-2: rgba(13, 26, 56, 0.92) !important;
        --bg-1:    #0c1830 !important;
    }

    /* Area konten utama diberi tint gelap agar wallpaper tidak terlalu kontras */
    .main-content {
        background: rgba(7, 16, 31, 0.45);
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
        /* Match sidebar's TOP region — solid navy, no glow (sama persis dengan brand area sidebar) */
        background: rgba(12, 22, 44, 0.96) !important;
        -webkit-backdrop-filter: blur(20px);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        color: var(--ink-0);
        position: sticky; top: 0;
        z-index: 30;
    }
    /* Subtle accent line under header (matches sidebar style) */
    .main-header::after {
        content: '';
        position: absolute;
        left: 16%; right: 16%; bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(94, 208, 255, 0.28), transparent);
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

    /* Status pill polish */
    .main-header .pill {
        padding: 6px 12px !important;
        border-radius: 999px !important;
        font-size: 11.5px !important;
        font-weight: 600;
        letter-spacing: 0.02em;
        backdrop-filter: blur(8px);
    }
    .main-header .pill .dot {
        width: 6px; height: 6px;
        margin-right: 2px;
    }

    /* Notification bell — premium look */
    #notifBellBtn,
    .main-header .btn-icon {
        width: 38px !important;
        height: 38px !important;
        border-radius: 10px !important;
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        color: var(--ink-2) !important;
        transition: all 0.22s ease !important;
    }
    #notifBellBtn:hover,
    .main-header .btn-icon:hover {
        background: rgba(94, 208, 255, 0.08) !important;
        border-color: rgba(94, 208, 255, 0.24) !important;
        color: var(--cyan) !important;
        transform: translateY(-1px);
    }

    /* Notification badge — refined */
    #notifBadge,
    .notif-badge {
        background: linear-gradient(135deg, #fb7185, #f43f5e) !important;
        color: #fff !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        min-width: 18px;
        height: 18px !important;
        border-radius: 999px !important;
        border: 2px solid rgba(10, 18, 36, 0.95) !important;
        box-shadow: 0 4px 12px -2px rgba(251, 113, 133, 0.5) !important;
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
    }
    @media (max-width: 1024px) { .page-body { padding-bottom: 88px; } }

    /* Mobile sidebar */
    @media (max-width: 1024px) {
        .main-content   { margin-left: 0 !important; width: 100% !important; }
        .app-sidebar    { transform: translateX(-100%); width: 280px !important; }
        .app-sidebar.open { transform: translateX(0); box-shadow: 0 24px 48px rgba(0,0,0,.40); }
        .sidebar-toggle.desktop-only { display: none !important; }
    }

    /* Backdrop */
    #overlay {
        position: fixed; inset: 0; z-index: 40;
        background: rgba(0, 0, 0, 0.55);
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
        opacity: 0; pointer-events: none;
        transition: opacity .25s cubic-bezier(.4,0,.2,1);
    }
    #overlay.active { opacity: 1; pointer-events: auto; }

    /* .custom-bg tidak lagi dibutuhkan — wallpaper sudah ada di body */
    .custom-bg { display: none; }

    /* ====================================================
       SIDEBAR ENHANCEMENT — premium polish
    ==================================================== */
    .app-sidebar {
        background:
            radial-gradient(420px 280px at 50% 0%, rgba(94, 208, 255, 0.08), transparent 70%),
            linear-gradient(180deg, rgba(12, 22, 44, 0.96), rgba(7, 14, 28, 0.98)) !important;
        border-right: 1px solid rgba(255, 255, 255, 0.06) !important;
        box-shadow: 0 0 40px rgba(0, 0, 0, 0.25);
    }

    /* Subtle top accent line */
    .app-sidebar::before {
        content: '';
        position: absolute;
        top: 0; left: 16%; right: 16%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(94, 208, 255, 0.32), transparent);
        pointer-events: none;
        z-index: 2;
    }

    /* ===== BRAND ===== */
    .brand {
        border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
        position: relative;
    }
    .brand::after {
        content: '';
        position: absolute;
        left: 18px; right: 18px; bottom: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(94, 208, 255, 0.18), transparent);
    }
    .brand-logo {
        background: conic-gradient(from 220deg, #5ed0ff, #b4a3ff, #fb7185, #fbbf24, #6ee7b7, #5ed0ff) !important;
        box-shadow: 0 8px 22px -8px rgba(94, 208, 255, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.25) !important;
        position: relative;
    }
    .brand-logo::after {
        content: '';
        position: absolute;
        inset: 3px;
        border-radius: 8px;
        background: rgba(7, 16, 31, 0.92);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .brand-logo i {
        position: relative;
        z-index: 2;
        color: var(--cyan);
    }
    .brand-text .sub {
        background: linear-gradient(90deg, var(--cyan), var(--lavender));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        font-weight: 700 !important;
    }

    /* ===== NAV SECTION LABELS ===== */
    .nav-section-label {
        font-size: 9.5px !important;
        letter-spacing: 0.16em !important;
        color: var(--ink-4) !important;
        padding: 14px 12px 8px !important;
        margin-top: 8px !important;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .nav-section-label::before {
        content: '';
        width: 14px;
        height: 1px;
        background: linear-gradient(90deg, rgba(94, 208, 255, 0.4), transparent);
        flex-shrink: 0;
    }

    /* ===== NAV LINKS ===== */
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
        background: rgba(255, 255, 255, 0.025);
        border: 1px solid transparent;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        font-size: 12px !important;
        color: var(--ink-3) !important;
        transition: all 0.22s ease !important;
        flex-shrink: 0;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.035) !important;
        color: var(--ink-0) !important;
        transform: translateX(2px);
    }
    .nav-link:hover i {
        background: rgba(94, 208, 255, 0.10);
        border-color: rgba(94, 208, 255, 0.20);
        color: var(--cyan) !important;
    }

    .nav-link.active {
        background:
            linear-gradient(90deg, rgba(94, 208, 255, 0.14) 0%, rgba(94, 208, 255, 0.02) 100%) !important;
        color: var(--ink-0) !important;
        font-weight: 600 !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
    }
    .nav-link.active i {
        background: linear-gradient(135deg, rgba(94, 208, 255, 0.20), rgba(180, 163, 255, 0.14));
        border-color: rgba(94, 208, 255, 0.36);
        color: var(--cyan) !important;
        box-shadow: 0 0 14px -3px rgba(94, 208, 255, 0.4);
    }
    .nav-link.active::before {
        width: 3px !important;
        background: linear-gradient(180deg, var(--cyan), var(--lavender)) !important;
        top: 10px !important;
        bottom: 10px !important;
        border-radius: 0 3px 3px 0 !important;
        box-shadow: 0 0 12px rgba(94, 208, 255, 0.5);
    }

    /* Collapsed sidebar — icon-only state */
    .app-sidebar.collapsed .nav-link i { margin: 0 auto; }

    /* ===== SIDEBAR FOOTER (Profile) ===== */
    .sidebar-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
        position: relative;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.20) 100%);
    }
    .sidebar-footer::before {
        content: '';
        position: absolute;
        left: 18px; right: 18px; top: -1px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(180, 163, 255, 0.18), transparent);
    }

    .profile-full {
        border-radius: 11px !important;
        padding: 8px 10px !important;
        transition: all 0.22s ease !important;
    }
    .profile-full:hover {
        background: rgba(255, 255, 255, 0.04) !important;
    }
    .profile-full .avatar {
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .profile-full:hover .avatar {
        transform: scale(1.05);
        box-shadow: 0 4px 16px -4px rgba(94, 208, 255, 0.5);
    }
    .profile-info .role {
        font-size: 10px !important;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--ink-4) !important;
        font-weight: 600;
        margin-top: 2px;
    }
    .icon-btn.danger {
        background: rgba(251, 113, 133, 0.08) !important;
        border: 1px solid rgba(251, 113, 133, 0.20) !important;
        color: var(--coral) !important;
        border-radius: 8px !important;
        transition: all 0.22s ease;
    }
    .icon-btn.danger:hover {
        background: rgba(251, 113, 133, 0.16) !important;
        border-color: rgba(251, 113, 133, 0.36) !important;
        transform: translateY(-1px);
    }
</style>
