<?php
// Guard: don't call session_start() if it was already called by the including page
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
// Guard: don't re-declare $pdo if the including page already required conexion.php
if (!isset($pdo)) require '../model/conexion.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nombre, correo, usuario, imagen FROM usuarios WHERE id_usuario = :id");
$stmt->execute([':id' => $user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$nombre_usuario = $result['nombre'] ?? 'Usuario';
$imagen_usuario = $result['imagen'] ?? '';

$_SESSION['nombre_usuario'] = $nombre_usuario;
$_SESSION['imagen_usuario']  = $imagen_usuario;

// Notificaciones de deadlines próximos
$stmtNotif = $pdo->prepare("
    SELECT t.id_tarea, t.nombre_tarea, t.fecha_cierre, t.hora_cierre, t.tipo_tarea,
           m.nombre_materia
    FROM tareas t
    INNER JOIN materias m ON t.id_materia = m.id_materia
    WHERE m.id_usuario = :uid
      AND t.estado NOT IN ('completada', 'cancelada')
      AND CONCAT(t.fecha_cierre, ' ', COALESCE(t.hora_cierre, '23:59:00')) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 72 HOUR)
    ORDER BY t.fecha_cierre ASC, t.hora_cierre ASC
    LIMIT 10
");
$stmtNotif->execute([':uid' => $user_id]);
$notificaciones = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);
$notif_count = count($notificaciones);

// Calcular urgencia de cada notif
function getNotifUrgency($fecha, $hora) {
    $now = new DateTime();
    $dl  = new DateTime($fecha . ' ' . ($hora ?? '23:59:00'));
    $h   = (int)(($now->diff($dl)->days * 24) + $now->diff($dl)->h);
    if ($h <= 24) return ['dot' => '#fc5c7d', 'label' => "Vence en {$h}h"];
    $days = $now->diff($dl)->days + 1;
    return ['dot' => '#f5a623', 'label' => "Vence en {$days} días"];
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notebookst</title>
    <link rel="icon" href="../asset/img/N.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Mono:wght@300;400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../asset/css/notebookst.css">
</head>
<body>
<script>
// Apply theme immediately before first paint to avoid flash
(function(){
  var t = localStorage.getItem('nbs_theme') || 'dark';
  document.body.setAttribute('data-theme', t);
})();
</script>
<div class="app-shell">

<!-- SIDEBAR OVERLAY (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-mark">
            <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 2h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" fill="#0d0f14" opacity=".7"/>
                <path d="M5 5h6M5 8h6M5 11h4" stroke="#0d0f14" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </div>
        <span class="sidebar-logo-text">Notebookst</span>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <div class="sidebar-section-label">Principal</div>
            <a href="home.php" class="nav-item <?= $current_page === 'home' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                Dashboard
            </a>
            <a href="tareas.php" class="nav-item <?= $current_page === 'tareas' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-check2-square"></i></span>
                Mis actividades
            </a>
            <a href="calendario.php" class="nav-item <?= $current_page === 'calendario' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-calendar3"></i></span>
                Calendario
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Cuenta</div>
            <a href="profile.php" class="nav-item <?= $current_page === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon"><i class="bi bi-person-fill"></i></span>
                Perfil
            </a>
            <a href="../model/logout.php" class="nav-item">
                <span class="nav-icon"><i class="bi bi-box-arrow-left"></i></span>
                Cerrar sesión
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="profile.php" class="user-row" style="text-decoration:none;">
            <img class="user-avatar"
                 src="<?= $imagen_usuario ? htmlspecialchars($imagen_usuario) : '../asset/img/N.png' ?>"
                 alt="Avatar">
            <div class="user-info">
                <div class="user-name truncate"><?= htmlspecialchars($nombre_usuario) ?></div>
                <div class="user-role">Estudiante</div>
            </div>
            <i class="bi bi-three-dots" style="color:var(--text-tertiary);font-size:13px;"></i>
        </a>
    </div>
</aside>

<!-- MAIN WRAP -->
<div class="main-wrap">

<!-- TOPBAR -->
<header class="topbar">
    <button class="btn-icon" id="sidebarToggle" onclick="toggleSidebar()" aria-label="Toggle sidebar" style="border:none;background:transparent;color:var(--text-secondary);font-size:17px;display:none;">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-title">
        <?php
        $titles = ['home' => 'Dashboard', 'tareas' => 'Mis actividades', 'calendario' => 'Calendario', 'detalle_materia' => 'Detalle', 'profile' => 'Perfil'];
        echo $titles[$current_page] ?? 'Notebookst';
        ?>
    </div>

    <div class="topbar-search" id="topbarSearch" style="position:relative;">
        <i class="bi bi-search" style="color:var(--text-tertiary);font-size:13px;flex-shrink:0;"></i>
        <input type="text" placeholder="Buscar actividades…" id="globalSearch"
               autocomplete="off"
               oninput="gsDebounce(this.value)"
               onkeydown="if(event.key==='Escape'){gsClose();this.blur();}"
               onfocus="if(this.value.trim().length>=2)gsSearch(this.value)">
        <!-- Search results dropdown -->
        <div id="gsDropdown" style="
            display:none;position:absolute;top:calc(100% + 8px);left:0;right:0;
            background:var(--bg-surface);border:1px solid var(--border);
            border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.4);
            z-index:99999;max-height:360px;overflow-y:auto;min-width:320px;">
        </div>
    </div>

    <div class="topbar-actions">
        <!-- Colombia clock (compact, topbar) -->
        <div style="font-family:var(--font-mono);font-size:12px;color:var(--text-secondary);
                    display:flex;align-items:center;gap:5px;padding:0 4px;"
             id="topbarClock" title="Hora Colombia (UTC-5)">
            <i class="bi bi-clock" style="font-size:11px;color:var(--text-tertiary);"></i>
            <span id="topbarClockTime">--:--</span>
        </div>

        <!-- Theme toggle -->
        <button class="btn-icon" id="themeToggle" onclick="toggleTheme()" title="Cambiar tema" aria-label="Cambiar tema claro/oscuro">
            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>

        <!-- Notif bell -->
        <div class="relative">
            <button class="btn-icon" id="notifBtn" onclick="toggleNotifPanel()" aria-label="Notificaciones">
                <i class="bi bi-bell-fill"></i>
            </button>
            <?php if ($notif_count > 0): ?>
            <div class="notif-badge"><?= $notif_count ?></div>
            <?php endif; ?>

            <!-- Notif panel -->
            <div class="notif-panel" id="notifPanel">
                <div class="notif-panel-header">
                    <span>Próximas entregas</span>
                    <span style="font-family:var(--font-mono);font-size:10px;color:var(--text-tertiary);"><?= $notif_count ?> pendientes</span>
                </div>
                <?php if (empty($notificaciones)): ?>
                    <div class="notif-empty">
                        <i class="bi bi-check-circle" style="font-size:22px;display:block;margin-bottom:6px;"></i>
                        Todo al día — sin entregas en 72h
                    </div>
                <?php else: ?>
                    <?php foreach ($notificaciones as $n):
                        $urg = getNotifUrgency($n['fecha_cierre'], $n['hora_cierre']);
                    ?>
                    <div class="notif-item">
                        <div class="notif-dot" style="background:<?= $urg['dot'] ?>;margin-top:5px;"></div>
                        <div>
                            <div class="notif-text">
                                <strong><?= htmlspecialchars($n['nombre_tarea']) ?></strong>
                                — <?= htmlspecialchars($n['nombre_materia']) ?>
                            </div>
                            <div class="notif-time"><?= $urg['label'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <img class="user-avatar"
             src="<?= $imagen_usuario ? htmlspecialchars($imagen_usuario) : '../asset/img/N.png' ?>"
             alt="Avatar" style="width:32px;height:32px;cursor:pointer;" onclick="location.href='profile.php'">
    </div>
</header>
<!-- END TOPBAR -->

<script>
// ── Búsqueda global ────────────────────────────────────────────
let _gsTimer = null;
function gsDebounce(val) {
    clearTimeout(_gsTimer);
    const v = val.trim();
    if (v.length < 2) { gsClose(); return; }
    _gsTimer = setTimeout(() => gsSearch(v), 280);
}

async function gsSearch(q) {
    const drop = document.getElementById('gsDropdown');
    drop.style.display = 'block';
    drop.innerHTML = '<div style="padding:14px 16px;font-size:12px;color:var(--text-tertiary);font-family:var(--font-mono);">Buscando…</div>';

    try {
        const res  = await fetch('../controller/buscar_tareas.php?q=' + encodeURIComponent(q));
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch(pe) {
            console.error('buscar_tareas respuesta no-JSON:', text);
            drop.innerHTML = '<div style="padding:14px 16px;font-size:12px;color:var(--status-cancelled);">Error al buscar (respuesta inválida).</div>';
            return;
        }
        if (!data.length) {
            drop.innerHTML = '<div style="padding:14px 16px;font-size:12px;color:var(--text-tertiary);font-family:var(--font-mono);">Sin resultados para "<strong>' + escGs(q) + '</strong>"</div>';
            return;
        }
        const STATUS = { pendiente:'#f5a623', en_progreso:'#63b3ed', completada:'#4af0c8', cancelada:'#fc5c7d' };
        const SLABEL = { pendiente:'Pendiente', en_progreso:'En progreso', completada:'Completada', cancelada:'Cancelada' };
        const EMOJI  = { tarea:'📄', quiz:'📝', parcial:'📋', examen_final:'🎯', proyecto:'🗂', otro:'📌' };
        drop.innerHTML = data.map(t => `
            <a href="detalle_materia.php?id_materia=${t.id_materia}&highlight_task=${t.id_tarea}"
               onclick="gsClose()"
               style="display:flex;align-items:center;gap:10px;padding:10px 14px;
                      text-decoration:none;color:var(--text-primary);
                      border-bottom:1px solid var(--border);transition:background .12s;"
               onmouseover="this.style.background='var(--bg-hover)'"
               onmouseout="this.style.background=''">
                <span style="font-size:16px;flex-shrink:0;">${EMOJI[t.tipo] || '📄'}</span>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ${escGs(t.nombre)}
                    </div>
                    <div style="font-size:11px;color:${t.mat_color};font-family:var(--font-mono);
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        ${escGs(t.materia)}
                    </div>
                </div>
                <div style="flex-shrink:0;text-align:right;">
                    <div style="font-family:var(--font-mono);font-size:10px;color:var(--text-tertiary);">
                        ${t.fecha}
                    </div>
                    <span style="font-size:10px;color:${STATUS[t.estado] || '#aaa'};font-family:var(--font-mono);">
                        ● ${SLABEL[t.estado] || t.estado}
                    </span>
                </div>
            </a>`).join('');
    } catch(e) {
        drop.innerHTML = '<div style="padding:14px 16px;font-size:12px;color:var(--status-cancelled);">Error al buscar.</div>';
    }
}

function gsClose() {
    const drop = document.getElementById('gsDropdown');
    if (drop) drop.style.display = 'none';
}

function escGs(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Cerrar al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('#topbarSearch')) gsClose();
});
</script>
