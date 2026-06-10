<?php
include_once 'encabezado.php';
include "../controller/filtros.php";

function statusClass($s) {
    return match($s) { 'pendiente'=>'status-pending','en_progreso'=>'status-progress','completada'=>'status-done','cancelada'=>'status-cancelled',default=>'' };
}
function statusLabel($s) {
    return match($s) { 'pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','cancelada'=>'Cancelada',default=>$s };
}
function tipoLabel($t) {
    return match($t) { 'quiz'=>'Quiz','parcial'=>'Parcial','examen_final'=>'Examen','proyecto'=>'Proyecto','otro'=>'Otro',default=>'Tarea' };
}
function getUrgencia($fecha, $hora, $estado) {
    if (in_array($estado, ['completada','cancelada'])) return null;
    $now = new DateTime();
    $dl  = new DateTime($fecha.' '.($hora??'23:59:00'));
    $h   = $now->diff($dl)->days * 24 + $now->diff($dl)->h;
    if ($dl < $now)  return 'urgent';
    if ($h <= 24)    return 'urgent';
    if ($h <= 72)    return 'soon';
    return null;
}
?>
<div class="page-content">
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <div class="page-header-eyebrow">Semestre activo</div>
                <h1 class="page-header-title">Mis actividades</h1>
                <p class="page-header-sub">Todas las actividades de tus materias.</p>
            </div>
        </div>
    </div>

    <!-- Filter bar -->
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:20px;">
        <!-- Status filter -->
        <div style="display:flex;gap:5px;flex-wrap:wrap;">
            <?php foreach(['todos'=>'Todas','pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','cancelada'=>'Cancelada'] as $k=>$lbl): ?>
            <a href="?filter=<?= $k ?>&id_materia=<?= htmlspecialchars($id_materia_seleccionada) ?>&busqueda=<?= urlencode($busqueda_mostrada) ?>"
               style="font-size:12px;padding:5px 12px;border-radius:20px;border:1px solid var(--border);font-family:var(--font-mono);text-decoration:none;
                      background:<?= $filter===$k?'var(--accent-dim)':'transparent' ?>;
                      color:<?= $filter===$k?'var(--accent)':'var(--text-secondary)' ?>;
                      border-color:<?= $filter===$k?'var(--accent)':'var(--border)' ?>;transition:.15s;">
                <?= $lbl ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Subject filter dropdown -->
        <div class="relative" style="margin-left:auto;">
            <button class="btn-ghost" onclick="document.getElementById('mtrDrop').classList.toggle('open')">
                <i class="bi bi-journal-bookmark"></i>
                <?= $id_materia_seleccionada ? htmlspecialchars(array_column($materias,'nombre_materia','id_materia')[$id_materia_seleccionada]??'Materia') : 'Todas las materias' ?>
                <i class="bi bi-chevron-down" style="font-size:10px;"></i>
            </button>
            <div class="ctx-menu" id="mtrDrop" style="min-width:200px;right:0;top:calc(100% + 4px);">
                <a class="ctx-item" href="?filter=<?= urlencode($filter) ?>&busqueda=<?= urlencode($busqueda_mostrada) ?>">Todas las materias</a>
                <div class="ctx-sep"></div>
                <?php foreach ($materias as $m): ?>
                <a class="ctx-item" href="?filter=<?= urlencode($filter) ?>&id_materia=<?= $m['id_materia'] ?>&busqueda=<?= urlencode($busqueda_mostrada) ?>">
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($m['color']??'#4af0c8') ?>;margin-right:4px;"></span>
                    <?= htmlspecialchars($m['nombre_materia']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Search -->
        <form action="" method="get" style="display:flex;align-items:center;">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="hidden" name="id_materia" value="<?= htmlspecialchars($id_materia_seleccionada) ?>">
            <div style="display:flex;align-items:center;gap:8px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:6px 12px;">
                <i class="bi bi-search" style="color:var(--text-tertiary);font-size:12px;"></i>
                <input type="text" name="busqueda" class="form-control-nbs" style="background:transparent;border:none;padding:0;width:160px;font-size:13px;"
                       placeholder="Buscar…" value="<?= htmlspecialchars($busqueda_mostrada) ?>">
            </div>
        </form>
    </div>

    <!-- Tasks -->
    <?php if (!empty($tareas_filtradas)): ?>
    <div class="tasks-grid">
        <?php foreach ($tareas_filtradas as $t):
            $urg    = getUrgencia($t['fecha_cierre'], $t['hora_cierre']??'23:59:00', $t['estado']);
            $isDone = in_array($t['estado'], ['completada','cancelada']);
            $clr    = $t['color'] ?? '#4af0c8';
            
        ?>
        <div class="task-card <?= $isDone?'is-done':'' ?>">
            <div class="task-card-header">
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                        <span class="task-type-chip" style="background:<?= $clr ?>22;color:<?= $clr ?>;"><?= tipoLabel($t['tipo_tarea']??'tarea') ?></span>
                        <span style="font-size:10px;color:var(--text-tertiary);font-family:var(--font-mono);">
                            <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:<?= $clr ?>;margin-right:3px;"></span>
                            <?= htmlspecialchars($t['nombre_materia']) ?>
                        </span>
                    </div>
                    <div class="task-title"><?= htmlspecialchars($t['nombre_tarea']) ?></div>
                </div>
                <div class="relative" style="flex-shrink:0;">
                    <?php if (!empty($t['link'])): ?>
                    <a href="<?= htmlspecialchars($t['link']) ?>" target="_blank" class="btn-icon" title="Abrir enlace en nueva pestaña">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <?php endif; ?>
                    <button class="btn-icon" id="tc-btn-<?= $t['id_tarea'] ?>"
                        data-nombre="<?= htmlspecialchars($t['nombre_tarea'], ENT_QUOTES) ?>"
                        onclick="toggleCtx('tc-<?= $t['id_tarea'] ?>')">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    
                    <div class="ctx-menu" id="tc-<?= $t['id_tarea'] ?>">
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'pendiente')">⏳ Pendiente</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'en_progreso')">🔄 En progreso</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'completada')">✅ Completada</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'cancelada')">🚫 Cancelada</div>
                        <div class="ctx-sep"></div>
                        <a class="ctx-item" href="detalle_materia.php?id_materia=<?= $t['id_materia'] ?>"><i class="bi bi-arrow-right-circle"></i> Ver materia</a>
                        <div class="ctx-item danger" onclick="eliminarTarea(<?= $t['id_tarea'] ?>, document.getElementById('tc-btn-<?= $t['id_tarea'] ?>').dataset.nombre)">
                            <i class="bi bi-trash3"></i> Eliminar
                        </div>
                    </div>
                </div>
            </div>
            <div class="task-card-body">
                <?php if ($urg): ?>
                <div class="deadline-banner <?= $urg ?>">
                    <?= $urg==='urgent' ? '🔴 Urgente' : '🟡 Próximamente' ?>
                </div>
                <?php endif; ?>
                <p class="task-desc"><?= htmlspecialchars($t['descripcion_tarea']) ?></p>
            </div>
            <div class="task-card-footer">
                <div class="task-deadline <?= $urg??'' ?>">
                    <i class="bi bi-calendar3"></i>
                    <?= date('d/m/Y', strtotime($t['fecha_cierre'])) ?>
                    <span style="background:var(--bg-overlay);padding:1px 5px;border-radius:3px;font-size:10px;">
                        <?= substr($t['hora_cierre']??'23:59:00',0,5) ?>
                    </span>
                </div>
                <span class="status-badge <?= statusClass($t['estado']) ?>"><?= statusLabel($t['estado']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-search"></i></div>
        <div class="empty-title">Sin resultados</div>
        <div class="empty-sub">Ajusta los filtros o la búsqueda.</div>
        <a href="tareas.php" class="btn-ghost" style="text-decoration:none;">Limpiar filtros</a>
    </div>
    <?php endif; ?>
</div>

<!-- Formulario oculto para eliminar tarea -->
<form id="frmDelTarea" action="../controller/eliminar_tarea.php" method="post" style="display:none">
    <input type="hidden" name="id_tarea" id="delTareaId">
    <input type="hidden" name="redirect" value="tareas.php">
</form>

<script>
// ── Cierre global de menús al hacer click fuera ──────────────
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));
    }
});

function toggleCtx(id) {
    const m = document.getElementById(id);
    const isOpen = m.classList.contains('open');

    document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));

    if (!isOpen) {
        m.classList.add('open');
        setTimeout(() => {
            const rect = m.getBoundingClientRect();
            if (rect.right > window.innerWidth - 10) {
                m.classList.add('align-left');
            } else {
                m.classList.remove('align-left');
            }
        }, 0);
    }
}

function cambiarEstado(id, estado) {
    document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));
    fetch('../controller/cmb_estado.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_tarea=${id}&nuevo_estado=${encodeURIComponent(estado)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast('Estado actualizado.', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            Swal.fire('Error', d.message || 'No se pudo actualizar.', 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    });
}

function eliminarTarea(id, nombre) {
    document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));
    Swal.fire({
        title: '¿Eliminar actividad?',
        html: `<span style="color:var(--text-secondary);font-size:14px;">Se eliminará <strong style="color:#eef0f5">${nombre}</strong>.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('delTareaId').value = id;
            document.getElementById('frmDelTarea').submit();
        }
    });
}
</script>
<?php include_once 'footer.php'; ?>
