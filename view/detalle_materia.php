<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
require '../model/conexion.php';

$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : 0;
$id_usuario = intval($_SESSION['user_id']);

if ($id_materia <= 0) { header('Location: home.php'); exit; }

$checkM = $pdo->prepare("SELECT * FROM materias WHERE id_materia = ? AND id_usuario = ?");
$checkM->execute([$id_materia, $id_usuario]);
$materia = $checkM->fetch(PDO::FETCH_ASSOC);
if (!$materia) { header('Location: home.php'); exit; }

// ── POST ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Editar tarea existente ───────────────────────────────
    if (isset($_POST['accion']) && $_POST['accion'] === 'editar_tarea') {
        $id_tarea_edit = intval($_POST['id_tarea_edit'] ?? 0);
        $tipos_validos = ['tarea','quiz','parcial','examen_final','proyecto','otro'];

        // Verificar que la tarea pertenece a esta materia y usuario
        $chkT = $pdo->prepare("SELECT t.id_tarea FROM tareas t
            INNER JOIN materias m ON m.id_materia = t.id_materia
            WHERE t.id_tarea = ? AND t.id_materia = ? AND m.id_usuario = ?");
        $chkT->execute([$id_tarea_edit, $id_materia, $id_usuario]);
        if (!$chkT->fetch()) {
            header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Tarea no encontrada.")."&type=danger");
            exit;
        }

        $e_nombre = trim($_POST['e_nombre_tarea']      ?? '');
        $e_desc   = trim($_POST['e_descripcion_tarea'] ?? '');
        $e_fecha  = trim($_POST['e_fecha_cierre']      ?? '');
        $e_hora   = trim($_POST['e_hora_cierre']       ?? '23:59');
        $e_tipo   = $_POST['e_tipo_tarea'] ?? 'tarea';
        $e_color  = $_POST['e_color']      ?? '#4af0c8';
        $e_link   = trim($_POST['e_link_tarea']        ?? '');

        if (!in_array($e_tipo, $tipos_validos, true)) $e_tipo = 'tarea';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $e_color)) $e_color = '#4af0c8';
        $e_link_val = ($e_link !== '' && filter_var($e_link, FILTER_VALIDATE_URL)) ? $e_link : null;

        if (!empty($e_nombre) && !empty($e_desc) && !empty($e_fecha)) {
            try {
                $pdo->prepare("UPDATE tareas SET
                    nombre_tarea = ?, descripcion_tarea = ?, tipo_tarea = ?,
                    fecha_cierre = ?, hora_cierre = ?, color = ?, link = ?
                    WHERE id_tarea = ?")
                    ->execute([$e_nombre, $e_desc, $e_tipo, $e_fecha, $e_hora.':00', $e_color, $e_link_val, $id_tarea_edit]);
                header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Actividad actualizada.")."&type=success");
                exit;
            } catch(PDOException $e) {
                header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Error al actualizar.")."&type=danger");
                exit;
            }
        } else {
            header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Completa todos los campos.")."&type=warning");
            exit;
        }
    }

    $tipos_validos = ['tarea','quiz','parcial','examen_final','proyecto','otro'];
    $nombre      = trim($_POST['nombre_tarea']      ?? '');
    $desc        = trim($_POST['descripcion_tarea'] ?? '');
    $fecha       = trim($_POST['fecha_cierre']      ?? '');
    $hora        = trim($_POST['hora_cierre']       ?? '23:59');
    $tipo        = $_POST['tipo_tarea'] ?? 'tarea';
    $color       = $_POST['color']      ?? '#4af0c8';
    $link = trim($_POST['link_tarea'] ?? '');

    if (!in_array($tipo, $tipos_validos, true)) $tipo = 'tarea';
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) $color = '#4af0c8';

    if (!empty($nombre) && !empty($desc) && !empty($fecha)) {
        try {
            $pdo->prepare("INSERT INTO tareas
                (id_materia,nombre_tarea,descripcion_tarea,fecha_creacion,fecha_cierre,hora_cierre,tipo_tarea,color,link,estado)
                VALUES (?,?,?,NOW(),?,?,?,?,?,'pendiente')")
                ->execute([$id_materia,$nombre,$desc,$fecha,$hora.':00',$tipo,$color,$link]);
            header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Actividad creada.")."&type=success");
            exit;
        } catch(PDOException $e) {
            header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Error al guardar.")."&type=danger");
            exit;
        }
    } else {
        header("Location: detalle_materia.php?id_materia={$id_materia}&msg=".urlencode("Completa todos los campos.")."&type=warning");
        exit;
    }
}

// ── Data ────────────────────────────────────────────────────
$filter = $_GET['filter'] ?? 'todas';
$sort   = $_GET['sort']   ?? 'fecha_asc';
$highlight_task = isset($_GET['highlight_task']) ? intval($_GET['highlight_task']) : 0;
$types_valid = ['pendiente','en_progreso','completada','cancelada','todas'];
if (!in_array($filter, $types_valid, true)) $filter = 'todas';
$sort_map = [
    'fecha_asc'  => 'fecha_cierre ASC,  hora_cierre ASC',
    'fecha_desc' => 'fecha_cierre DESC, hora_cierre DESC',
    'nombre_asc' => 'nombre_tarea ASC',
    'tipo'       => 'tipo_tarea ASC, fecha_cierre ASC',
    'estado'     => 'estado ASC, fecha_cierre ASC',
];
$order = $sort_map[$sort] ?? $sort_map['fecha_asc'];

$sql = "SELECT * FROM tareas WHERE id_materia = ?";
if ($filter !== 'todas') $sql .= " AND estado = " . $pdo->quote($filter);
$sql .= " ORDER BY {$order}";
$stmtT = $pdo->prepare($sql);
$stmtT->execute([$id_materia]);
$tareas = $stmtT->fetchAll(PDO::FETCH_ASSOC);

// Stats for this subject
$stmtSub = $pdo->prepare("
    SELECT COUNT(*) total,
           SUM(estado='completada') done,
           SUM(estado='pendiente')  pend,
           SUM(estado='en_progreso') prog
    FROM tareas WHERE id_materia = ?");
$stmtSub->execute([$id_materia]);
$sub = $stmtSub->fetch(PDO::FETCH_ASSOC);
$pct = $sub['total'] > 0 ? round($sub['done'] / $sub['total'] * 100) : 0;

function getTipoLabel($t) {
    return match($t) {
        'quiz'        => 'Quiz',
        'parcial'     => 'Parcial',
        'examen_final'=> 'Examen',
        'proyecto'    => 'Proyecto',
        'otro'        => 'Otro',
        default       => 'Tarea',
    };
}
function getDeadlineInfo($fecha, $hora) {
    $now = new DateTime();
    $dl  = new DateTime($fecha . ' ' . ($hora ?? '23:59:00'));
    $diff = $now->diff($dl);
    $h = $diff->days * 24 + $diff->h;
    if ($dl < $now)    return ['cls' => 'urgent', 'txt' => '⚠ Vencida'];
    if ($h <= 24)      return ['cls' => 'urgent', 'txt' => "🔴 {$h}h restantes"];
    if ($h <= 72)      return ['cls' => 'soon',   'txt' => "🟡 {$diff->days} día(s)"];
    return null;
}
function statusClass($s) {
    return match($s) { 'pendiente' => 'status-pending', 'en_progreso' => 'status-progress', 'completada' => 'status-done', 'cancelada' => 'status-cancelled', default => '' };
}
function statusLabel($s) {
    return match($s) { 'pendiente' => 'Pendiente', 'en_progreso' => 'En progreso', 'completada' => 'Completada', 'cancelada' => 'Cancelada', default => $s };
}

include_once 'encabezado.php';
?>

<div class="page-content">

    <!-- Breadcrumb -->
    <div class="breadcrumb-nbs">
        <a href="home.php">Dashboard</a>
        <span>›</span>
        <span><?= htmlspecialchars($materia['nombre_materia']) ?></span>
    </div>

    <!-- Page header -->
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <div class="page-header-eyebrow" style="color:<?= htmlspecialchars($materia['color']) ?>">
                    Materia
                </div>
                <h1 class="page-header-title"><?= htmlspecialchars($materia['nombre_materia']) ?></h1>
                <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
                    <span style="font-family:var(--font-mono);font-size:11px;color:var(--text-tertiary);"><?= $sub['total'] ?> actividades · <?= $pct ?>% completado</span>
                    <div style="width:100px;height:3px;background:var(--bg-overlay);border-radius:2px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:<?= htmlspecialchars($materia['color']) ?>;transition:.6s;border-radius:2px;"></div>
                    </div>
                </div>
            </div>
            <button class="btn-primary-nbs" onclick="document.getElementById('modalTarea').classList.add('open')">
                <i class="bi bi-plus"></i> Nueva actividad
            </button>
        </div>
    </div>

    <!-- Stats cards -->
    <?php if ($sub['total'] > 0): ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:20px;">
        <?php
        $canceladas = $sub['total'] - $sub['done'] - $sub['pend'] - $sub['prog'];
        $stats = [
            ['label'=>'Total',       'val'=>$sub['total'], 'color'=>'var(--text-tertiary)',   'icon'=>'bi-journal-text'],
            ['label'=>'Pendientes',  'val'=>$sub['pend'],  'color'=>'var(--status-pending)',  'icon'=>'bi-hourglass-split'],
            ['label'=>'En progreso', 'val'=>$sub['prog'],  'color'=>'var(--status-progress)', 'icon'=>'bi-arrow-repeat'],
            ['label'=>'Completadas', 'val'=>$sub['done'],  'color'=>'var(--status-done)',     'icon'=>'bi-check-circle-fill'],
        ];
        foreach ($stats as $s): ?>
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:10px;
                    padding:12px 14px;display:flex;align-items:center;gap:10px;">
            <i class="bi <?= $s['icon'] ?>" style="font-size:18px;color:<?= $s['color'] ?>;flex-shrink:0;"></i>
            <div>
                <div style="font-family:var(--font-display);font-size:20px;font-weight:600;color:<?= $s['color'] ?>;line-height:1.1;">
                    <?= $s['val'] ?>
                </div>
                <div style="font-family:var(--font-mono);font-size:10px;color:var(--text-tertiary);margin-top:1px;">
                    <?= $s['label'] ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <!-- Progress bar card -->
        <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:10px;
                    padding:12px 14px;display:flex;flex-direction:column;justify-content:center;gap:6px;min-width:140px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-family:var(--font-mono);font-size:10px;color:var(--text-tertiary);">Progreso</span>
                <span style="font-family:var(--font-display);font-size:16px;font-weight:600;
                             color:<?= htmlspecialchars($materia['color']) ?>;"><?= $pct ?>%</span>
            </div>
            <div style="height:6px;background:var(--bg-overlay);border-radius:3px;overflow:hidden;">
                <div style="width:<?= $pct ?>%;height:100%;background:<?= htmlspecialchars($materia['color']) ?>;
                            border-radius:3px;transition:.6s;"></div>
            </div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                <?php
                $bars = [
                    [$sub['pend'],  'var(--status-pending)'],
                    [$sub['prog'],  'var(--status-progress)'],
                    [$sub['done'],  'var(--status-done)'],
                ];
                foreach($bars as [$n,$c]):
                    $w = $sub['total'] > 0 ? round($n / $sub['total'] * 100) : 0;
                    if ($w > 0): ?>
                    <div style="height:3px;width:<?= $w ?>%;background:<?= $c ?>;border-radius:2px;" title="<?= $n ?>"></div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter bar -->
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;">
        <?php foreach(['todas'=>'Todas','pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','cancelada'=>'Cancelada'] as $k=>$label): ?>
        <a href="?id_materia=<?= $id_materia ?>&filter=<?= $k ?>"
           style="font-size:12px;padding:5px 12px;border-radius:20px;border:1px solid var(--border);
                  font-family:var(--font-mono);
                  background:<?= $filter===$k ? 'var(--accent-dim)' : 'transparent' ?>;
                  color:<?= $filter===$k ? 'var(--accent)' : 'var(--text-secondary)' ?>;
                  border-color:<?= $filter===$k ? 'var(--accent)' : 'var(--border)' ?>;
                  text-decoration:none;transition:.15s;">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
        <div style="margin-left:auto;display:flex;align-items:center;gap:6px;">
            <i class="bi bi-sort-down" style="font-size:12px;color:var(--text-tertiary);"></i>
            <select onchange="location.href=this.value"
                    style="font-family:var(--font-mono);font-size:11px;color:var(--text-secondary);
                           background:var(--bg-surface);border:1px solid var(--border);
                           border-radius:6px;padding:4px 8px;cursor:pointer;outline:none;">
                <?php
                $sort_opts = [
                    'fecha_asc'  => 'Fecha ↑',
                    'fecha_desc' => 'Fecha ↓',
                    'nombre_asc' => 'Nombre A–Z',
                    'tipo'       => 'Tipo',
                    'estado'     => 'Estado',
                ];
                foreach ($sort_opts as $k => $lbl):
                    $url = '?id_materia='.$id_materia.'&filter='.$filter.'&sort='.$k;
                ?>
                <option value="<?= $url ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Tasks grid -->
    <?php if (!empty($tareas)): ?>
    <div class="tasks-grid">
        <?php foreach ($tareas as $t):
            $dl       = getDeadlineInfo($t['fecha_cierre'], $t['hora_cierre'] ?? '23:59:00');
            $isDone   = in_array($t['estado'], ['completada','cancelada']);
            $typeClr  = $t['color'] ?? '#4af0c8';
        ?>
        <div class="task-card <?= $isDone ? 'is-done' : '' ?>" data-task-id="<?= $t['id_tarea'] ?>">
            <div class="task-card-header">
                <div style="flex:1;min-width:0;">
                    <span class="task-type-chip" style="background:<?= $typeClr ?>22;color:<?= $typeClr ?>;">
                        <?= getTipoLabel($t['tipo_tarea'] ?? 'tarea') ?>
                    </span>
                    <div class="task-title truncate"><?= htmlspecialchars($t['nombre_tarea']) ?></div>
                </div>
                <div class="relative" style="flex-shrink:0;">
                    <?php if (!empty($t['link'])): ?>
                    <a href="<?= htmlspecialchars($t['link']) ?>" target="_blank" class="btn-icon" title="Abrir enlace en nueva pestaña">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                    <?php endif; ?>
                    <button class="btn-icon" onclick="toggleCtx('tc-<?= $t['id_tarea'] ?>')"
                        data-id="<?= $t['id_tarea'] ?>"
                        data-nombre="<?= htmlspecialchars($t['nombre_tarea'], ENT_QUOTES) ?>"
                        data-desc="<?= htmlspecialchars($t['descripcion_tarea'], ENT_QUOTES) ?>"
                        data-tipo="<?= htmlspecialchars($t['tipo_tarea'] ?? 'tarea', ENT_QUOTES) ?>"
                        data-fecha="<?= htmlspecialchars($t['fecha_cierre'], ENT_QUOTES) ?>"
                        data-hora="<?= htmlspecialchars(substr($t['hora_cierre'] ?? '23:59:00', 0, 5), ENT_QUOTES) ?>"
                        data-color="<?= htmlspecialchars($t['color'] ?? '#4af0c8', ENT_QUOTES) ?>"
                        data-link="<?= htmlspecialchars($t['link'] ?? '', ENT_QUOTES) ?>">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <div class="ctx-menu" id="tc-<?= $t['id_tarea'] ?>">
                        <div class="ctx-item" onclick="abrirEditarTarea(this.closest('.ctx-menu').previousElementSibling)"><i class="bi bi-pencil-square"></i> Editar actividad</div>
                        <div class="ctx-sep"></div>
                        <div class="ctx-item" style="font-size:11px;color:var(--text-tertiary);padding-bottom:4px;">Cambiar estado</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'pendiente')">⏳ Pendiente</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'en_progreso')">🔄 En progreso</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'completada')">✅ Completada</div>
                        <div class="ctx-item" onclick="cambiarEstado(<?= $t['id_tarea'] ?>,'cancelada')">🚫 Cancelada</div>
                        <div class="ctx-sep"></div>
                        <div class="ctx-item danger" onclick="eliminarTarea(<?= $t['id_tarea'] ?>,'<?= htmlspecialchars(addslashes($t['nombre_tarea'])) ?>')">
                            <i class="bi bi-trash3"></i> Eliminar
                        </div>
                    </div>
                </div>
            </div>

            <div class="task-card-body">
                <?php if ($dl && !$isDone): ?>
                <div class="deadline-banner <?= $dl['cls'] ?>"><?= $dl['txt'] ?></div>
                <?php endif; ?>
                <p class="task-desc"><?= htmlspecialchars($t['descripcion_tarea']) ?></p>
            </div>

            <div class="task-card-footer">
                <div class="task-deadline <?= $dl && !$isDone ? $dl['cls'] : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <?= date('d/m/Y', strtotime($t['fecha_cierre'])) ?>
                    <span style="font-size:10px;background:var(--bg-overlay);padding:1px 5px;border-radius:3px;">
                        <?= substr($t['hora_cierre'] ?? '23:59:00', 0, 5) ?>
                    </span>
                </div>
                <span class="status-badge <?= statusClass($t['estado']) ?>"><?= statusLabel($t['estado']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-journal-x"></i></div>
        <div class="empty-title">Sin actividades<?= $filter !== 'todas' ? ' con ese filtro' : '' ?></div>
        <div class="empty-sub"><?= $filter !== 'todas' ? 'Prueba cambiando el filtro.' : 'Crea tu primera actividad para esta materia.' ?></div>
        <?php if ($filter === 'todas'): ?>
        <button class="btn-primary-nbs" onclick="document.getElementById('modalTarea').classList.add('open')">
            <i class="bi bi-plus"></i> Crear actividad
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL NUEVA ACTIVIDAD -->
<div class="modal-nbs-backdrop" id="modalTarea" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-nbs">
        <div class="modal-nbs-header">
            <span class="modal-nbs-title">Nueva actividad</span>
            <button class="btn-icon" onclick="document.getElementById('modalTarea').classList.remove('open')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="detalle_materia.php?id_materia=<?= $id_materia ?>" method="post">
            <div class="modal-nbs-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-nbs">Tipo</label>
                        <select name="tipo_tarea" class="form-control-nbs" required>
                            <option value="tarea">📄 Tarea</option>
                            <option value="quiz">📝 Quiz</option>
                            <option value="parcial">📋 Parcial</option>
                            <option value="examen_final">🎯 Examen final</option>
                            <option value="proyecto">🗂 Proyecto</option>
                            <option value="otro">📌 Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label-nbs">Color</label>
                        <div class="color-swatch-row" id="swatchRow">
                            <?php
                            $swatches = ['#4af0c8','#63b3ed','#f5a623','#fc5c7d','#a78bfa','#34d399','#f87171','#e879f9','#fbbf24','#38bdf8'];
                            foreach($swatches as $i => $c): ?>
                            <div class="color-swatch <?= $i===0?'selected':'' ?>"
                                 style="background:<?= $c ?>;" data-color="<?= $c ?>"
                                 onclick="selectSwatch(this)"></div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="color" id="colorInput" value="<?= $swatches[0] ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Nombre</label>
                    <input type="text" name="nombre_tarea" class="form-control-nbs"
                           placeholder="Ej: Quiz Unidad 2" required>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Descripción</label>
                    <textarea name="descripcion_tarea" class="form-control-nbs" rows="2"
                              placeholder="Detalles de la actividad…" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-nbs">Fecha límite</label>
                        <input type="date" name="fecha_cierre" class="form-control-nbs" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-nbs">Hora límite</label>
                        <input type="time" name="hora_cierre" class="form-control-nbs" value="23:59">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Enlace (opcional)</label>
                    <input type="url" name="link_tarea" class="form-control-nbs"
                           placeholder="Ej: https://docs.google.com/forms/..." 
                           title="Debe ser una URL válida (https://...)">
                </div>
            </div>
            <div class="modal-nbs-footer">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modalTarea').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn-primary-nbs"><i class="bi bi-save2"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR ACTIVIDAD -->
<div class="modal-nbs-backdrop" id="modalEditarTarea" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-nbs">
        <div class="modal-nbs-header">
            <span class="modal-nbs-title">Editar actividad</span>
            <button class="btn-icon" onclick="document.getElementById('modalEditarTarea').classList.remove('open')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form action="detalle_materia.php?id_materia=<?= $id_materia ?>" method="post">
            <input type="hidden" name="accion" value="editar_tarea">
            <input type="hidden" name="id_tarea_edit" id="editIdTarea">
            <div class="modal-nbs-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-nbs">Tipo</label>
                        <select name="e_tipo_tarea" id="editTipo" class="form-control-nbs" required>
                            <option value="tarea">📄 Tarea</option>
                            <option value="quiz">📝 Quiz</option>
                            <option value="parcial">📋 Parcial</option>
                            <option value="examen_final">🎯 Examen final</option>
                            <option value="proyecto">🗂 Proyecto</option>
                            <option value="otro">📌 Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label-nbs">Color</label>
                        <div class="color-swatch-row" id="editSwatchRow">
                            <?php
                            $swatches = ['#4af0c8','#63b3ed','#f5a623','#fc5c7d','#a78bfa','#34d399','#f87171','#e879f9','#fbbf24','#38bdf8'];
                            foreach($swatches as $c): ?>
                            <div class="color-swatch"
                                 style="background:<?= $c ?>;" data-color="<?= $c ?>"
                                 onclick="selectSwatchEdit(this)"></div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="e_color" id="editColorInput" value="">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Nombre</label>
                    <input type="text" name="e_nombre_tarea" id="editNombre" class="form-control-nbs"
                           placeholder="Ej: Quiz Unidad 2" required>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Descripción</label>
                    <textarea name="e_descripcion_tarea" id="editDesc" class="form-control-nbs" rows="2"
                              placeholder="Detalles de la actividad…" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-nbs">Fecha límite</label>
                        <input type="date" name="e_fecha_cierre" id="editFecha" class="form-control-nbs" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-nbs">Hora límite</label>
                        <input type="time" name="e_hora_cierre" id="editHora" class="form-control-nbs">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label-nbs">Enlace (opcional)</label>
                    <input type="url" name="e_link_tarea" id="editLink" class="form-control-nbs"
                           placeholder="Ej: https://docs.google.com/forms/..."
                           title="Debe ser una URL válida (https://...)">
                </div>
            </div>
            <div class="modal-nbs-footer">
                <button type="button" class="btn-ghost" onclick="document.getElementById('modalEditarTarea').classList.remove('open')">Cancelar</button>
                <button type="submit" class="btn-primary-nbs"><i class="bi bi-save2"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden delete form -->
<form id="frmDelTarea" action="../controller/eliminar_tarea.php" method="post" style="display:none">
    <input type="hidden" name="id_tarea"   id="delTareaId">
    <input type="hidden" name="id_materia" value="<?= $id_materia ?>">
</form>
<script>
function toggleCtx(id) {
    const m = document.getElementById(id);
    document.querySelectorAll('.ctx-menu.open').forEach(x => { if(x.id!==id) x.classList.remove('open'); });
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
function selectSwatch(el) {
    document.querySelectorAll('#swatchRow .color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('colorInput').value = el.dataset.color;
}
function cambiarEstado(id, estado) {
    fetch('../controller/cmb_estado.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id_tarea=${id}&nuevo_estado=${encodeURIComponent(estado)}`
    }).then(r=>r.json()).then(d=>{
        if(d.success) { showToast('Estado actualizado.','success'); setTimeout(()=>location.reload(),600); }
        else Swal.fire('Error', d.message||'No se pudo actualizar.','error');
    });
}
function eliminarTarea(id, nombre) {
    Swal.fire({
        title: '¿Eliminar actividad?',
        html: `<span style="color:var(--text-secondary);font-size:14px;">Se eliminará <strong style="color:#eef0f5">${nombre}</strong>.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
    }).then(r=>{
        if(r.isConfirmed) {
            document.getElementById('delTareaId').value = id;
            document.getElementById('frmDelTarea').submit();
        }
    });
}
function selectSwatchEdit(el) {
    document.querySelectorAll('#editSwatchRow .color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('editColorInput').value = el.dataset.color;
}
function abrirEditarTarea(btn) {
    document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));

    document.getElementById('editIdTarea').value    = btn.dataset.id;
    document.getElementById('editNombre').value     = btn.dataset.nombre;
    document.getElementById('editDesc').value       = btn.dataset.desc;
    document.getElementById('editFecha').value      = btn.dataset.fecha;
    document.getElementById('editHora').value       = btn.dataset.hora;
    document.getElementById('editLink').value       = btn.dataset.link;
    document.getElementById('editColorInput').value = btn.dataset.color;

    const sel = document.getElementById('editTipo');
    for (let o of sel.options) o.selected = (o.value === btn.dataset.tipo);

    document.querySelectorAll('#editSwatchRow .color-swatch').forEach(s => {
        s.classList.toggle('selected', s.dataset.color === btn.dataset.color);
    });

    document.getElementById('modalEditarTarea').classList.add('open');
}
</script>
<?php include_once 'footer.php'; ?>
