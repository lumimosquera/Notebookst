</div><!-- .main-wrap -->
</div><!-- .app-shell -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
// ── Theme toggle ─────────────────────────────────────────────
function toggleTheme() {
    const current = document.body.getAttribute('data-theme') || 'dark';
    const next    = current === 'dark' ? 'light' : 'dark';
    document.body.setAttribute('data-theme', next);
    localStorage.setItem('nbs_theme', next);
    updateThemeIcon(next);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (!icon) return;
    if (theme === 'light') {
        icon.className = 'bi bi-sun-fill';
        icon.style.color = 'var(--status-pending)';
    } else {
        icon.className = 'bi bi-moon-stars-fill';
        icon.style.color = '';
    }
}

// Apply icon state on load
(function() {
    const t = localStorage.getItem('nbs_theme') || 'dark';
    // Ensure body has correct theme (belt-and-suspenders for the inline script)
    document.body.setAttribute('data-theme', t);
    updateThemeIcon(t);
})();

// ── Colombia clock (UTC-5) ────────────────────────────────────
(function startClock() {
    const DAYS_ES   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const MONTHS_ES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

    function pad(n) { return String(n).padStart(2, '0'); }

    function getColombia() {
        // Colombia is always UTC-5 (no DST)
        const now   = new Date();
        const utc   = now.getTime() + now.getTimezoneOffset() * 60000;
        return new Date(utc - 5 * 3600000);
    }

    function tick() {
        const co  = getColombia();
        const h   = pad(co.getHours());
        const m   = pad(co.getMinutes());
        const s   = pad(co.getSeconds());
        const day = DAYS_ES[co.getDay()];
        const d   = co.getDate();
        const mon = MONTHS_ES[co.getMonth()];
        const yr  = co.getFullYear();

        // Topbar compact clock
        const topbar = document.getElementById('topbarClockTime');
        if (topbar) topbar.textContent = h + ':' + m;

        // Dashboard full clock
        const fullH = document.getElementById('clockHM');
        const fullS = document.getElementById('clockSec');
        const fullD = document.getElementById('clockDate');
        const fullG = document.getElementById('clockGreeting');
        if (fullH) fullH.textContent = h + ':' + m;
        if (fullS) fullS.textContent = ':' + s;
        if (fullD) fullD.textContent = day + ', ' + d + ' ' + mon + ' ' + yr;
        if (fullG) {
            const hr = co.getHours();
            fullG.textContent = hr < 12 ? 'Buenos días ☀️'
                              : hr < 18 ? 'Buenas tardes 🌤️'
                              :            'Buenas noches 🌙';
        }
    }

    tick();
    setInterval(tick, 1000);
})();

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

// ── Navigate to task from notification ───────────────────────
function navigateToTask(taskId, materiaId) {
    if (!materiaId) {
        showToast('Error: No se pudo determinar la materia.', 'error');
        return;
    }
    
    // Close notification panel
    const panel = document.getElementById('notifPanel');
    if (panel) panel.classList.remove('open');
    
    // Mark notification as visited (visual feedback)
    const notifItem = document.querySelector(`[data-task-id="${taskId}"]`);
    if (notifItem) {
        notifItem.style.opacity = '0.6';
    }
    
    // Navigate to task detail
    window.location.href = `detalle_materia.php?id_materia=${materiaId}&highlight_task=${taskId}`;

// ── Highlight task from notification navigation ───────────────
function highlightTaskFromNotification() {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight_task');
    
    if (!highlightId) return;
    
    // Wait for DOM to be ready
    setTimeout(() => {
        const taskCard = document.querySelector(`[data-task-id="${highlightId}"]`);
        if (taskCard) {
            // Add highlight class
            taskCard.classList.add('highlighted');
            
            // Scroll into view smoothly
            taskCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Remove highlight after 4 seconds
            setTimeout(() => {
                taskCard.classList.remove('highlighted');
            }, 4000);
        } else {
            showToast('Tarea no encontrada en esta materia.', 'warning');
        }
    }, 100);
}

// Call highlight function on page load
document.addEventListener('DOMContentLoaded', highlightTaskFromNotification);
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

// ── URL Input Validation ─────────────────────────────────────────
(function() {
    // Validate URL inputs on blur
    document.querySelectorAll('input[type="url"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !this.value.match(/^https?:\/\//)) {
                this.classList.add('is-invalid');
                this.title = 'URL debe comenzar con http:// o https://';
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
})();
</script>
</body>
</html>
