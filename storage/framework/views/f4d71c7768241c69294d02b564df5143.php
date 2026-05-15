
<div class="notif-bell-wrap" id="notifBellWrap" style="position:relative;">
    <button type="button" id="notifBellBtn" class="btn-icon" title="Notifikasi" onclick="toggleNotifPanel()">
        <i class="fa-regular fa-bell text-xs"></i>
        <span id="notifBadge" class="notif-badge" style="display:none;">0</span>
    </button>

    <div id="notifPanel" class="notif-panel">
        <div class="notif-panel-header">
            <div>
                <p class="eyebrow" style="margin:0;"><i class="fa-regular fa-bell"></i> Notifikasi</p>
                <p style="margin:2px 0 0;font-size:11px;color:var(--ink-3);"><span id="notifPanelCount">0</span> belum dibaca</p>
            </div>
            <button type="button" onclick="markAllNotifRead()" class="btn btn-ghost btn-xs" id="notifMarkAllBtn">
                <i class="fa-solid fa-check-double text-[9px]"></i>
                <span>Tandai semua</span>
            </button>
        </div>
        <div id="notifPanelBody" class="notif-panel-body">
            <div class="notif-loading">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
        </div>
        <div class="notif-panel-footer">
            <a href="/notifications" class="btn btn-soft btn-block btn-sm">
                Lihat Semua
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>

<style>
.notif-badge {
    position: absolute; top: -4px; right: -4px;
    min-width: 18px; height: 18px;
    border-radius: 999px;
    background: var(--coral);
    color: #fff;
    font-size: 10px; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 5px;
    border: 2px solid var(--bg-1);
    box-shadow: 0 0 10px rgba(248,113,113,.45);
    font-family: 'JetBrains Mono', monospace;
}
.notif-panel {
    position: absolute; top: calc(100% + 10px); right: 0;
    width: 360px; max-width: calc(100vw - 32px);
    background: var(--bg-2);
    border: 1px solid var(--line);
    border-radius: var(--r-xl);
    box-shadow: var(--shadow-lg);
    opacity: 0; visibility: hidden;
    transform: translateY(-8px);
    transition: var(--t-base);
    z-index: 50;
    overflow: hidden;
    -webkit-backdrop-filter: blur(20px);
    backdrop-filter: blur(20px);
}
.notif-panel.show { opacity: 1; visibility: visible; transform: translateY(0); }
.notif-panel-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid var(--line-soft);
    background: var(--panel-1);
}
.notif-panel-body {
    max-height: 420px; overflow-y: auto;
}
.notif-loading {
    text-align: center; padding: 40px 16px; color: var(--ink-3);
}
.notif-empty {
    text-align: center; padding: 40px 16px; color: var(--ink-3); font-size: 13px;
}
.notif-empty i { font-size: 28px; color: var(--ink-4); margin-bottom: 10px; display: block; }
.notif-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--line-soft);
    cursor: pointer;
    transition: var(--t-fast);
    text-decoration: none; color: inherit;
}
.notif-item:hover { background: var(--panel-1); }
.notif-item:last-child { border-bottom: none; }
.notif-item.unread { background: rgba(77, 212, 255, 0.04); }
.notif-item.unread:hover { background: rgba(77, 212, 255, 0.08); }
.notif-icon {
    width: 32px; height: 32px;
    border-radius: 9px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    background: var(--panel-2); color: var(--ink-3);
}
.notif-icon.error   { background: rgba(248,113,113,.16); color: var(--coral); }
.notif-icon.warning { background: rgba(251,191,36,.16); color: var(--amber); }
.notif-icon.success { background: rgba(110,231,183,.16); color: var(--mint); }
.notif-icon.info    { background: rgba(77,212,255,.16); color: var(--cyan); }
.notif-meta { flex: 1; min-width: 0; }
.notif-title {
    font-size: 13px; font-weight: 600;
    color: var(--ink-0);
    margin: 0;
    line-height: 1.35;
    display: flex; align-items: center; gap: 6px;
}
.notif-unread-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--cyan); box-shadow: 0 0 8px var(--cyan);
    flex-shrink: 0;
}
.notif-msg {
    font-size: 12px; color: var(--ink-2);
    margin: 3px 0 0;
    line-height: 1.45;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.notif-time {
    font-size: 10.5px; color: var(--ink-4);
    margin-top: 4px;
    font-family: 'JetBrains Mono', monospace;
}
.notif-panel-footer {
    padding: 10px 12px;
    border-top: 1px solid var(--line-soft);
    background: var(--panel-1);
}
@media (max-width: 480px) {
    .notif-panel {
        position: fixed;
        top: 64px; right: 12px; left: 12px;
        width: auto;
    }
}
</style>

<script>
let notifPollInterval = null;
let notifPanelOpen = false;

function toggleNotifPanel() {
    const panel = document.getElementById('notifPanel');
    if (!panel) return;
    notifPanelOpen = !panel.classList.contains('show');
    panel.classList.toggle('show', notifPanelOpen);
    if (notifPanelOpen) loadNotifPanel();
}

function closeNotifPanel() {
    document.getElementById('notifPanel')?.classList.remove('show');
    notifPanelOpen = false;
}

document.addEventListener('click', (e) => {
    const wrap = document.getElementById('notifBellWrap');
    if (wrap && !wrap.contains(e.target)) closeNotifPanel();
});

function notifIconFor(severity, type) {
    const map = {
        device_offline: 'fa-plug-circle-exclamation',
        temp_alert: 'fa-temperature-three-quarters',
        schedule_run: 'fa-calendar-check',
        system: 'fa-gear',
        info: 'fa-circle-info',
    };
    return map[type] || 'fa-bell';
}

function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function renderNotifItems(items) {
    const body = document.getElementById('notifPanelBody');
    if (!body) return;

    if (!items || items.length === 0) {
        body.innerHTML = `
            <div class="notif-empty">
                <i class="fa-regular fa-bell-slash"></i>
                Belum ada notifikasi
            </div>`;
        return;
    }

    body.innerHTML = items.map(n => `
        <a href="${n.link || '#'}" class="notif-item ${n.is_unread ? 'unread' : ''}"
           data-id="${n.id}" onclick="markNotifRead(event, ${n.id}, '${n.link || ''}')">
            <span class="notif-icon ${n.severity}"><i class="fa-solid ${notifIconFor(n.severity, n.type)}"></i></span>
            <div class="notif-meta">
                <p class="notif-title">
                    ${n.is_unread ? '<span class="notif-unread-dot"></span>' : ''}
                    ${escapeHtml(n.title)}
                </p>
                ${n.message ? `<p class="notif-msg">${escapeHtml(n.message)}</p>` : ''}
                <p class="notif-time"><i class="fa-regular fa-clock text-[9px]"></i> ${escapeHtml(n.time_ago)}</p>
            </div>
        </a>`).join('');
}

function loadNotifPanel() {
    const body = document.getElementById('notifPanelBody');
    if (body) body.innerHTML = '<div class="notif-loading"><i class="fa-solid fa-spinner fa-spin"></i></div>';

    fetch('/notifications/recent', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            renderNotifItems(data.items);
            updateNotifBadge(data.unread_count);
        })
        .catch(() => {
            if (body) body.innerHTML = '<div class="notif-empty"><i class="fa-solid fa-circle-exclamation"></i>Gagal memuat notifikasi</div>';
        });
}

function updateNotifBadge(count) {
    const badge = document.getElementById('notifBadge');
    const panelCount = document.getElementById('notifPanelCount');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    }
    if (panelCount) panelCount.textContent = count;
}

function markNotifRead(e, id, link) {
    e.preventDefault();
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json',
        },
    }).finally(() => {
        if (link && link !== '#' && link !== '') window.location.href = link;
    });
}

function markAllNotifRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json',
        },
    }).then(() => {
        loadNotifPanel();
        updateNotifBadge(0);
        if (window.smToast) window.smToast('Semua notifikasi ditandai dibaca', 'success');
    });
}

function pollUnreadCount() {
    fetch('/notifications/unread-count', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => updateNotifBadge(data.count))
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    pollUnreadCount();
    notifPollInterval = setInterval(() => { if (!document.hidden) pollUnreadCount(); }, 30000);

    // Real-time: push notif baru langsung tanpa nunggu polling 30s
    if (window.Echo) {
        window.Echo.channel('device-status')
            .listen('.NotificationCreated', () => {
                pollUnreadCount();
                const panel = document.getElementById('notifPanel');
                if (panel && panel.classList.contains('open') && typeof loadNotifPanel === 'function') {
                    loadNotifPanel();
                }
            });
    }
});

window.addEventListener('beforeunload', () => { if (notifPollInterval) clearInterval(notifPollInterval); });
</script>
<?php /**PATH C:\laragon\www\tugasakhirremotac\resources\views/components/notification-bell.blade.php ENDPATH**/ ?>