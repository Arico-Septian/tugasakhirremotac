<script>
/* ===== SIDEBAR TOGGLE ===== */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    if (!sidebar) return;

    if (window.innerWidth <= 1024) {
        const isOpen = sidebar.classList.toggle('open');
        overlay?.classList.toggle('active', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    } else {
        // collapse on desktop
        sidebar.classList.toggle('collapsed');
        try {
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
        } catch (e) {}
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    if (sidebar && window.innerWidth > 1024) {
        try {
            if (localStorage.getItem('sidebarCollapsed') === '1') sidebar.classList.add('collapsed');
        } catch (e) {}
    }
});

/* ===== SIDEBAR NOTIF BADGE (synced with bell poll) ===== */
function syncSidebarNotifBadge(count) {
    const b = document.getElementById('sidebarNotifBadge');
    if (!b) return;
    if (count > 0) {
        b.textContent = count > 99 ? '99+' : String(count);
        b.style.display = 'inline-flex';
    } else {
        b.style.display = 'none';
    }
}
function pollSidebarNotifBadge() {
    fetch('/notifications/unread-count', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(d => syncSidebarNotifBadge(d.count))
        .catch(() => {});
}
document.addEventListener('DOMContentLoaded', () => {
    pollSidebarNotifBadge();
    setInterval(() => { if (!document.hidden) pollSidebarNotifBadge(); }, 30000);
});

document.getElementById('overlay')?.addEventListener('click', function () {
    document.getElementById('sidebar')?.classList.remove('open');
    this.classList.remove('active');
    document.body.style.overflow = '';
});

/* Close mobile sidebar after nav (with brief delay for visual close) */
document.querySelectorAll('.menu-link').forEach(link => {
    link.addEventListener('click', function (e) {
        if (window.innerWidth <= 1024) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (sidebar?.classList.contains('open')) {
                e.preventDefault();
                sidebar.classList.remove('open');
                overlay?.classList.remove('active');
                document.body.style.overflow = '';
                setTimeout(() => { window.location.href = this.href; }, 200);
            }
        }
    });
});

/* ===== IDLE TIMEOUT ===== */
(function () {
    const role  = "<?php echo e(Auth::check() ? Auth::user()->role : ''); ?>";
    const idleMs = role === 'admin' ? 600000 : role === 'operator' ? 300000 : 120000;
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const form = document.createElement('form');
            form.method = 'POST'; form.action = '/logout'; form.style.display = 'none';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }, idleMs);
    }
    ['mousemove','keypress','click','scroll','touchstart'].forEach(ev => {
        document.addEventListener(ev, resetTimer, { passive: true });
    });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) resetTimer(); });
    resetTimer();
})();

/* ===== TOAST helper (used across pages) ===== */
window.smToast = function (msg, type = 'info') {
    document.querySelectorAll('.toast').forEach(t => t.remove());
    const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info', warn: 'fa-triangle-exclamation' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span class="icon"><i class="fa-solid ${icons[type] || icons.info}"></i></span><span>${msg}</span>`;
    document.body.appendChild(t);
    setTimeout(() => { t.classList.add('toast-out'); setTimeout(() => t.remove(), 240); }, 2800);
};
</script>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/components/sidebar-scripts.blade.php ENDPATH**/ ?>