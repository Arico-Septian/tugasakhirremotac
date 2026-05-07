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
        sidebar.classList.toggle('close');
    }
}

document.getElementById('overlay')?.addEventListener('click', function () {
    document.getElementById('sidebar')?.classList.remove('open');
    this.classList.remove('active');
    document.body.style.overflow = '';
});

/* ===== MENU LINK: close sidebar on mobile nav ===== */
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
                setTimeout(() => { window.location.href = this.href; }, 220);
            }
        }
    });
});

/* ===== IDLE TIMEOUT ===== */
(function () {
    const role = "{{ Auth::check() ? Auth::user()->role : '' }}";
    const idleMs = role === 'admin' ? 600000 : role === 'operator' ? 300000 : 120000;
    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const form = document.createElement('form');
            form.method = 'POST'; form.action = '/logout'; form.style.display = 'none';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
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
</script>
