</div><!-- .main-wrap -->
</div><!-- .app-shell -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
// ── Sidebar toggle (mobile) ──────────────────────────────────
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

// Show hamburger on mobile
(function() {
    var btn = document.getElementById('sidebarToggle');
    if (btn) btn.style.display = 'grid';
    // Only show on mobile
    function checkWidth() {
        if (btn) btn.style.display = window.innerWidth <= 768 ? 'grid' : 'none';
    }
    checkWidth();
    window.addEventListener('resize', checkWidth);
})();

// ── Notification panel ───────────────────────────────────────
function toggleNotifPanel() {
    const p = document.getElementById('notifPanel');
    if (p) p.classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const btn   = document.getElementById('notifBtn');
    const panel = document.getElementById('notifPanel');
    if (panel && btn && !btn.contains(e.target) && !panel.contains(e.target)) {
        panel.classList.remove('open');
    }
});

// ── Toast helper ─────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `toast-nbs ${type}`;
    t.innerHTML = `<div class="toast-dot"></div><span>${msg}</span>`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; t.style.transition = '.3s'; }, 2500);
    setTimeout(() => t.remove(), 2900);
}

// ── Close all context menus on outside click ─────────────────
document.addEventListener('click', function(e) {
    document.querySelectorAll('.ctx-menu.open').forEach(m => {
        if (!m.previousElementSibling?.contains(e.target) && !m.contains(e.target))
            m.classList.remove('open');
    });
});

// ── Flash toast from URL param ────────────────────────────────
(function() {
    const p = new URLSearchParams(window.location.search);
    const msg  = p.get('msg');
    const type = p.get('type');
    if (msg) {
        const map = { success: 'success', warning: 'warning', danger: 'error', 'alert-success': 'success', 'alert-warning': 'warning', 'alert-danger': 'error' };
        showToast(decodeURIComponent(msg), map[type] || 'success');
        // Clean URL
        const clean = new URL(window.location.href);
        clean.searchParams.delete('msg'); clean.searchParams.delete('type');
        window.history.replaceState({}, '', clean.toString());
    }
})();
</script>
</body>
</html>
