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
        height: 60px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 24px;
        background: rgba(7, 16, 31, 0.72);
        -webkit-backdrop-filter: blur(16px);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--line-soft);
        color: var(--ink-0);
        position: sticky; top: 0;
        z-index: 30;
    }
    @media (max-width: 1024px) { .main-header { padding: 0 16px; } }

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
</style>
