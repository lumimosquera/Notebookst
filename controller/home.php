<?php
// ── POST antes de cualquier output ──────────────────────────
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();
require '../model/conexion.php';

if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
$user_id = intval($_SESSION['user_id']);

// Paleta curada de colores
$palette = ['#4af0c8','#63b3ed','#f5a623','#fc5c7d','#a78bfa','#34d399','#f59e0b','#60a5fa','#e879f9','#f87171'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = trim($_POST['nombre']);
    $link = isset($_POST['link']) ? trim($_POST['link']) : '';
    
    if (!empty($nombre)) {
        $color = $palette[array_rand($palette)];
        
        // Validar URL si se proporciona
        $error_msg = '';
        if (!empty($link) && !filter_var($link, FILTER_VALIDATE_URL)) {
            $error_msg = "El enlace debe ser una URL válida (http:// o https://)";
        }
        
        if (!empty($error_msg)) {
            ob_end_clean();
            header("Location: home.php?msg=" . urlencode($error_msg) . "&type=danger");
            exit;
        }
        
        try {
            $link_value = !empty($link) ? $link : null;
            $pdo->prepare("INSERT INTO materias (nombre_materia, id_usuario, color, link) VALUES (?, ?, ?, ?)")
                ->execute([$nombre, $user_id, $color, $link_value]);
            ob_end_clean();
            header("Location: home.php?msg=" . urlencode("Materia creada.") . "&type=success");
            exit;
        } catch (PDOException $e) {
            ob_end_clean();
            header("Location: home.php?msg=" . urlencode("Error al crear materia.") . "&type=danger");
            exit;
        }
    }
}
ob_end_clean();

// ── Stats ────────────────────────────────────────────────────
$stmtStats = $pdo->prepare("
    SELECT
        COUNT(DISTINCT m.id_materia) AS materias,
        COUNT(t.id_tarea)            AS total,
        SUM(t.estado = 'completada') AS completadas,
        SUM(t.estado = 'pendiente')  AS pendientes,
        SUM(t.estado = 'en_progreso')AS en_progreso,
        SUM(t.estado NOT IN ('completada','cancelada')
            AND CONCAT(t.fecha_cierre,' ',COALESCE(t.hora_cierre,'23:59:00')) < NOW()
        ) AS vencidas
    FROM materias m
    LEFT JOIN tareas t ON m.id_materia = t.id_materia
    WHERE m.id_usuario = ?
");
$stmtStats->execute([$user_id]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// ── Materias con progreso ────────────────────────────────────
$stmtM = $pdo->prepare("
    SELECT m.*,
           COUNT(t.id_tarea)            AS total_tareas,
           SUM(t.estado = 'completada') AS completadas,
           SUM(t.estado NOT IN ('completada','cancelada')
               AND CONCAT(t.fecha_cierre,' ',COALESCE(t.hora_cierre,'23:59:00')) < NOW()
           ) AS vencidas,
           MIN(CASE WHEN t.estado NOT IN('completada','cancelada')
               THEN CONCAT(t.fecha_cierre,' ',COALESCE(t.hora_cierre,'23:59:00')) END
           ) AS next_deadline
    FROM materias m
    LEFT JOIN tareas t ON m.id_materia = t.id_materia
    WHERE m.id_usuario = ?
    GROUP BY m.id_materia
    ORDER BY m.id_materia DESC
");
$stmtM->execute([$user_id]);
$materias = $stmtM->fetchAll(PDO::FETCH_ASSOC);

include_once 'encabezado.php';
?>

<div class="page-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <div class="page-header-eyebrow">Semestre activo</div>
                <h1 class="page-header-title">Dashboard</h1>
                <p class="page-header-sub">Bienvenido, <?= htmlspecialchars($nombre_usuario) ?>. Aquí está tu resumen académico.</p>
            </div>
            <button class="btn-primary-nbs" onclick="document.getElementById('modalMateria').classList.add('open')">
                <i class="bi bi-plus"></i> Nueva materia
            </button>
        </div>
    </div>

    <!-- Clock widget — Colombia (UTC-5) -->
    <div class="clock-widget">
        <div>
            <div class="clock-time">
                <span id="clockHM">--:--</span><span class="clock-seconds" id="clockSec">:--</span>
            </div>
        </div>
        <div class="clock-divider"></div>
        <div class="clock-meta">
            <div class="clock-date" id="clockDate">Cargando…</div>
            <div class="clock-tz"><i class="bi bi-geo-alt-fill" style="font-size:9px;"></i> Colombia · UTC−5</div>
        </div>
        <div class="clock-divider" style="margin-left:auto;"></div>
        <div class="clock-greeting" id="clockGreeting"></div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card stat-accent">
            <div class="stat-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <div class="stat-value"><?= $stats['materias'] ?></div>
            <div class="stat-label">Materias activas</div>
        </div>
        <div class="stat-card stat-amber">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-value"><?= $stats['pendientes'] ?></div>
            <div class="stat-label">Actividades pendientes</div>
        </div>
        <div class="stat-card stat-blue">
            <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="stat-value"><?= $stats['en_progreso'] ?></div>
            <div class="stat-label">En progreso</div>
        </div>
        <div class="stat-card stat-pink">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-value"><?= $stats['vencidas'] ?></div>
            <div class="stat-label">Vencidas</div>
        </div>
    </div>

    <!-- Courses -->
    <div class="section-header">
        <div class="flex items-center gap-2">
            <h2 class="section-title">Materias</h2>
            <span class="section-count"><?= count($materias) ?></span>
        </div>
    </div>

    <div class="courses-grid">
        <?php foreach ($materias as $m):
            $total     = intval($m['total_tareas']);
            $done      = intval($m['completadas']);
            $venc      = intval($m['vencidas']);
            $pct       = $total > 0 ? round($done / $total * 100) : 0;
            $pending   = $total - $done;
            // Next deadline
            $deadline_txt = '';
            if ($m['next_deadline']) {
                $dl  = new DateTime($m['next_deadline']);
                $now = new DateTime();
                $diff = $now->diff($dl);
                $h    = $diff->days * 24 + $diff->h;
                if ($dl < $now)       $deadline_txt = '<span style="color:var(--status-cancelled);font-size:11px;font-family:var(--font-mono);">⚠ Tarea vencida</span>';
                elseif ($h <= 24)     $deadline_txt = '<span style="color:var(--status-cancelled);font-size:11px;font-family:var(--font-mono);">🔴 Vence en '.$h.'h</span>';
                elseif ($h <= 72)     $deadline_txt = '<span style="color:var(--status-pending);font-size:11px;font-family:var(--font-mono);">🟡 Vence en '.$diff->days.' días</span>';
            }
        ?>
        <div class="course-card" onclick="location.href='detalle_materia.php?id_materia=<?= $m['id_materia'] ?>'">
            <div class="course-stripe" style="background:<?= htmlspecialchars($m['color']) ?>"></div>
            <div class="course-body">
                <div class="course-title"><?= htmlspecialchars($m['nombre_materia']) ?></div>
                <div class="course-meta">
                    <span><?= $total ?> actividades</span>
                    <?php if ($venc > 0): ?><span style="color:var(--status-cancelled);">· <?= $venc ?> vencida<?= $venc>1?'s':'' ?></span><?php endif; ?>
                </div>
                <?php if ($deadline_txt): ?>
                <div style="margin-bottom:10px;"><?= $deadline_txt ?></div>
                <?php endif; ?>
                <div class="course-progress-bar-bg">
                    <div class="course-progress-bar-fill"
                         style="width:<?= $pct ?>%;background:<?= htmlspecialchars($m['color']) ?>"></div>
                </div>
            </div>
            <div class="course-footer">
                <div class="flex gap-2">
                    <?php if ($pending > 0): ?>
                    <div class="task-pill">
                        <div class="task-pill-dot" style="background:var(--status-pending)"></div>
                        <?= $pending ?> pendiente<?= $pending>1?'s':'' ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($done > 0): ?>
                    <div class="task-pill">
                        <div class="task-pill-dot" style="background:var(--status-done)"></div>
                        <?= $done ?> lista<?= $done>1?'s':'' ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="course-actions" onclick="event.stopPropagation()">
                    <div class="relative">
                        <?php if (!empty($m['link'])): ?>
                        <a href="<?= htmlspecialchars($m['link']) ?>" target="_blank" class="btn-icon" title="Abrir enlace en nueva pestaña">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        <?php endif; ?>
                        <button class="btn-icon" onclick="toggleCtx('ctx-<?= $m['id_materia'] ?>')">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="ctx-menu" id="ctx-<?= $m['id_materia'] ?>">
                            <div class="ctx-item" onclick="location.href='detalle_materia.php?id_materia=<?= $m['id_materia'] ?>'">
                                <i class="bi bi-arrow-right-circle"></i> Ver materia
                            </div>
                            <div class="ctx-sep"></div>
                            <div class="ctx-item danger" onclick="event.preventDefault(); event.stopPropagation(); eliminarMateria(<?= $m['id_materia'] ?>, '<?= htmlspecialchars(addslashes($m['nombre_materia'])) ?>'); return false;">
                                <i class="bi bi-trash3"></i> Eliminar
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Add card -->
        <div class="course-card-add" onclick="document.getElementById('modalMateria').classList.add('open')">
            <div class="course-card-add-icon"><i class="bi bi-plus-circle"></i></div>
            <div class="course-card-add-label">Agregar materia</div>
        </div>
    </div>
</div>

<!-- MODAL NUEVA MATERIA -->
<div class="modal-nbs-backdrop" id="modalMateria" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-nbs">
        <div class="modal-nbs-header">
            <span class="modal-nbs-title">Nueva materia</span>
            <button class="btn-icon" onclick="document.getElementById('modalMateria').classList.remove('open')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="home.php" method="post">
            <div class="modal-nbs-body">
                <div class="form-group">
                    <label class="form-label-nbs">Nombre de la materia</label>
                    <input type="text" name="nombre" class="form-control-nbs"
                           placeholder="Ej: Cálculo Diferencial" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Enlace (opcional)</label>
                    <input type="url" name="link" class="form-control-nbs"
                           placeholder="Ej: https://classroom.google.com/..." 
                           title="Debe ser una URL válida (https://...)">
                </div>
                <p style="font-size:11.5px;color:var(--text-tertiary);">
                    <i class="bi bi-palette2"></i> Se asignará un color automáticamente.
                </p>
            </div>
            <div class="modal-nbs-footer">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modalMateria').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn-primary-nbs"><i class="bi bi-plus"></i> Crear materia</button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto eliminar -->
<form id="frmDelMateria" action="../controller/eliminar_materia.php" method="post" style="display:none">
    <input type="hidden" name="id" id="delMateriaId">
</form>

<script>
/* ── Hoist modals to <body> to escape any ancestor stacking context ── */
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('modalMateria');
    if (el && el.parentElement !== document.body) {
        document.body.appendChild(el);
    }
});
</script>

<script>
function toggleCtx(id) {
    const m = document.getElementById(id);
    document.querySelectorAll('.ctx-menu.open').forEach(x => { if(x.id !== id) x.classList.remove('open'); });
    m.classList.toggle('open');
    
    // Ajustar posición si está fuera de pantalla
    if (m.classList.contains('open')) {
        setTimeout(() => {
            const rect = m.getBoundingClientRect();
            const isOutOfBounds = rect.right > window.innerWidth - 10;
            
            if (isOutOfBounds && !m.classList.contains('align-left')) {
                m.classList.add('align-left');
            } else if (!isOutOfBounds && m.classList.contains('align-left')) {
                m.classList.remove('align-left');
            }
        }, 0);
    }
}
function eliminarMateria(id, nombre) {
    console.log('eliminarMateria llamada con id:', id, 'nombre:', nombre);
    
    // Validar que los elementos existen
    const formEl = document.getElementById('frmDelMateria');
    const inputEl = document.getElementById('delMateriaId');
    
    if (!formEl) {
        console.error('ERROR: Formulario frmDelMateria no encontrado');
        showToast('Error: Formulario no disponible', 'error');
        return;
    }
    
    if (!inputEl) {
        console.error('ERROR: Input delMateriaId no encontrado');
        showToast('Error: Campo de entrada no disponible', 'error');
        return;
    }
    
    console.log('Elementos encontrados. Abriendo confirmación...');
    
    Swal.fire({
        title: '¿Eliminar materia?',
        html: `
          <span style="color:var(--text-secondary);font-size:13px;line-height:1.6;">
            Se eliminará permanentemente
            <strong style="color:var(--text-primary);font-weight:600;">${nombre}</strong>
            y todas sus actividades asociadas.
          </span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        backdrop: true,
        allowOutsideClick: true,
        scrollbarPadding: false,
        position: 'center',
        customClass: {
            popup:    'swal2-popup',
            title:    'swal2-title',
            actions:  'swal2-actions',
        },
    }).then(r => {
        console.log('SweetAlert resultado:', r.isConfirmed);
        if (r.isConfirmed) {
            try {
                console.log('Asignando id al formulario:', id);
                inputEl.value = id;
                
                console.log('Enviando formulario:', formEl.action);
                formEl.submit();
                
                console.log('Formulario enviado');
                showToast('Eliminando materia...', 'success');
            } catch (error) {
                console.error('ERROR al enviar formulario:', error);
                showToast('Error al eliminar: ' + error.message, 'error');
            }
        } else {
            console.log('Eliminación cancelada por el usuario');
        }
    }).catch(error => {
        console.error('ERROR en SweetAlert:', error);
        showToast('Error: ' + error.message, 'error');
    });
}
</script>
<?php include_once 'footer.php'; ?>
