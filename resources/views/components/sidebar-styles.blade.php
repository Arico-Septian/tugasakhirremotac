<style>
/* ===== SIDEBAR SHARED STYLES ===== */
.sidebar {
    width: 256px;
    flex-shrink: 0;
    position: fixed;
    top: 0; left: 0;
    height: 100%;
    z-index: 50;
    overflow: hidden;
    transition: width 0.25s cubic-bezier(0.4,0,0.2,1);
}
.sidebar.close { width: 72px; }
.sidebar.close .menu-text,
.sidebar.close .sidebar-label { display: none !important; }
.sidebar.close .profile-full  { display: none; }
.sidebar.close .profile-collapse { display: block; }
.sidebar.close .sidebar-nav-item { justify-content: center; padding: 10px; border-left-color: transparent !important; }
.sidebar.close .sidebar-toggle i { transform: rotate(180deg); }

/* Nav items */
.sidebar-nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.15s ease;
    border-left: 2px solid transparent;
    position: relative;
    cursor: pointer;
}
.sidebar-nav-item:hover {
    background: rgba(255,255,255,0.05);
    color: #e2e8f0;
}
.sidebar-nav-item.active {
    background: rgba(59,130,246,0.1);
    color: #60a5fa;
    border-left-color: #3b82f6;
    font-weight: 600;
}
.sidebar-icon {
    width: 18px;
    text-align: center;
    flex-shrink: 0;
    font-size: 14px;
    transition: transform 0.15s ease;
}
.sidebar-nav-item:hover .sidebar-icon { transform: scale(1.1); }

/* Layout */
.layout {
    display: flex;
    height: 100vh;
    width: 100vw;
    position: relative;
    z-index: 1;
}
.main-content {
    margin-left: 256px;
    width: calc(100% - 256px);
    height: 100vh;
    display: flex;
    flex-direction: column;
    transition: margin-left 0.25s cubic-bezier(0.4,0,0.2,1),
                width 0.25s cubic-bezier(0.4,0,0.2,1);
    overflow: hidden;
}
.sidebar.close ~ .main-content {
    margin-left: 72px;
    width: calc(100% - 72px);
}
.main-header {
    flex-shrink: 0;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    background: rgba(12, 22, 40, 0.9);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 1px 12px rgba(0,0,0,0.3);
    color: white;
    z-index: 30;
}
.page-body {
    flex: 1;
    overflow-y: auto;
    scroll-behavior: smooth;
    padding-bottom: 32px;
}
.page-body::-webkit-scrollbar { width: 5px; }
.page-body::-webkit-scrollbar-track { background: transparent; }
.page-body::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 999px;
}
.page-body::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

/* Mobile */
@media (max-width: 1024px) {
    .main-content { margin-left: 0 !important; width: 100% !important; }
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
        width: 256px !important;
        will-change: transform;
    }
    .sidebar.open { transform: translateX(0); }
    .sidebar-toggle { display: none !important; }
}

/* Overlay */
#overlay { opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
#overlay.active { opacity: 1; pointer-events: auto; }

/* Global */
* { box-sizing: border-box; }
html, body {
    height: 100%;
    margin: 0; padding: 0;
    overflow: hidden;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    -webkit-font-smoothing: antialiased;
}
.custom-bg {
    background:
        linear-gradient(rgba(5,10,40,0.75), rgba(5,10,40,0.85)),
        url('/images/wallpaper.jpeg') no-repeat center center;
    background-size: cover;
    background-attachment: fixed;
    position: fixed;
    width: 100%; height: 100%;
}
</style>
