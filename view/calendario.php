<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
if (!isset($pdo)) require_once '../model/conexion.php';

$id_usuario = intval($_SESSION['user_id']);

$filter                  = in_array($_GET['filter'] ?? 'todos', ['todos','pendiente','en_progreso','completada','cancelada'])
                           ? ($_GET['filter'] ?? 'todos') : 'todos';
$id_materia_seleccionada = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : null;
$view_mode               = in_array($_GET['view'] ?? 'mes', ['mes','semana','dia']) ? ($_GET['view'] ?? 'mes') : 'mes';

$now_col    = new DateTime('now', new DateTimeZone('America/Bogota'));
$view_year  = isset($_GET['y']) ? intval($_GET['y']) : (int)$now_col->format('Y');
$view_month = isset($_GET['m']) ? intval($_GET['m']) : (int)$now_col->format('n');
$view_day   = isset($_GET['d']) ? intval($_GET['d']) : (int)$now_col->format('j');

if ($view_month < 1)  { $view_month = 12; $view_year--; }
if ($view_month > 12) { $view_month = 1;  $view_year++; }

// Materias
$stmtMat = $pdo->prepare("SELECT id_materia, nombre_materia, color FROM materias WHERE id_usuario = ? ORDER BY nombre_materia ASC");
$stmtMat->execute([$id_usuario]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

// Tasks
$sql = "SELECT t.id_tarea, t.id_materia, t.nombre_tarea, t.descripcion_tarea,
               t.fecha_creacion, t.fecha_cierre, t.hora_cierre, t.tipo_tarea,
               t.estado, t.color, t.link, m.nombre_materia, m.color AS materia_color
        FROM tareas t
        INNER JOIN materias m ON t.id_materia = m.id_materia
        WHERE m.id_usuario = :uid";
$params = [':uid' => $id_usuario];
if ($filter !== 'todos')        { $sql .= " AND t.estado = :estado"; $params[':estado'] = $filter; }
if ($id_materia_seleccionada)   { $sql .= " AND m.id_materia = :mat"; $params[':mat'] = $id_materia_seleccionada; }
$sql .= " ORDER BY t.fecha_cierre ASC, t.hora_cierre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tasks_by_date = [];
foreach ($all_tasks as $t) {
    $tasks_by_date[$t['fecha_cierre']][] = $t;
}

// Calendar math
$first_day    = new DateTime("{$view_year}-{$view_month}-01");
$days_month   = (int)$first_day->format('t');
$start_dow    = (int)$first_day->format('N');
$start_offset = $start_dow - 1;

// Week math
$current_date_str = sprintf('%04d-%02d-%02d', $view_year, $view_month, $view_day);
$current_dt       = new DateTime($current_date_str);
$week_dow         = (int)$current_dt->format('N'); // 1=Mon
$week_start       = clone $current_dt;
$week_start->modify('-' . ($week_dow - 1) . ' days');
$week_days = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $week_start;
    $d->modify("+{$i} days");
    $week_days[] = $d;
}

// Nav URLs helper
function navUrl($vm, $vy, $vmo, $vd, $filter, $mat, $mode) {
    return "?view={$mode}&m={$vmo}&y={$vy}&d={$vd}&filter={$filter}&id_materia={$mat}";
}

$MONTHS_ES   = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$DAYS_ES     = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
$DAYS_FULL   = ['','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

$today_str = $now_col->format('Y-m-d');
$mat_param = $id_materia_seleccionada ?? '';

// Prev/Next for month view
$prev_m = $view_month - 1; $prev_y = $view_year;
if ($prev_m < 1)  { $prev_m = 12; $prev_y--; }
$next_m = $view_month + 1; $next_y = $view_year;
if ($next_m > 12) { $next_m = 1;  $next_y++; }

// Prev/Next for week view
$prev_week_start = clone $week_start; $prev_week_start->modify('-7 days');
$next_week_start = clone $week_start; $next_week_start->modify('+7 days');

// Prev/Next for day view
$prev_day_dt = clone $current_dt; $prev_day_dt->modify('-1 day');
$next_day_dt = clone $current_dt; $next_day_dt->modify('+1 day');

function calTipoEmoji($t) {
    return match($t) { 'quiz'=>'📝','parcial'=>'📋','examen_final'=>'🎯','proyecto'=>'🗂','otro'=>'📌',default=>'📄' };
}

include_once 'encabezado.php';
?>
<style>
/* ════════════════════════════════════════════════════════════
   CALENDAR — Design refresh
════════════════════════════════════════════════════════════ */

/* ── View switcher ── */
.cal-view-switcher {
    display: flex;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
}
.cal-view-btn {
    font-family: var(--font-mono);
    font-size: 11px;
    padding: 5px 14px;
    border-radius: 7px;
    border: none;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    text-decoration: none;
    display: flex; align-items: center; gap: 5px;
    transition: all .15s;
    white-space: nowrap;
}
.cal-view-btn:hover { color: var(--text-primary); background: var(--bg-overlay); }
.cal-view-btn.active {
    background: var(--accent);
    color: var(--text-inverse);
    font-weight: 500;
}

/* ── Nav row ── */
.cal-nav-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; margin-bottom: 18px;
}
.cal-nav-center { display: flex; align-items: center; gap: 6px; }
.cal-nav-label {
    font-family: var(--font-display);
    font-size: 20px;
    color: var(--text-primary);
    min-width: 200px;
    text-align: center;
    line-height: 1.2;
}
.cal-nav-sub {
    font-family: var(--font-mono);
    font-size: 10px;
    color: var(--text-tertiary);
    text-align: center;
}
.btn-nav {
    width: 32px; height: 32px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg-elevated);
    color: var(--text-secondary);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    transition: all .15s;
    text-decoration: none;
}
.btn-nav:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); }
.btn-today-pill {
    font-family: var(--font-mono); font-size: 11px;
    padding: 4px 12px; border-radius: 20px;
    border: 1px solid var(--accent); background: var(--accent-dim);
    color: var(--accent); cursor: pointer; text-decoration: none;
    transition: all .15s;
}
.btn-today-pill:hover { background: var(--accent); color: var(--text-inverse); }

/* ── Filter pills ── */
.cal-filters {
    display: flex; flex-wrap: wrap; gap: 6px;
    align-items: center; margin-bottom: 14px;
}
.cal-filter-pill {
    font-size: 11px; padding: 4px 12px;
    border-radius: 20px; border: 1px solid var(--border);
    font-family: var(--font-mono); text-decoration: none;
    transition: all .15s; white-space: nowrap;
    color: var(--text-secondary); background: transparent;
}
.cal-filter-pill:hover { border-color: var(--accent); color: var(--accent); }
.cal-filter-pill.active { background: var(--accent-dim); color: var(--accent); border-color: var(--accent); }

/* ── Legend ── */
.cal-legend { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
.cal-legend-item { display: flex; align-items: center; gap: 4px; font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); }
.cal-legend-dot  { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ══ MONTH VIEW ══════════════════════════════════════════════ */
.cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr);
    border-radius: 12px; overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: 0 2px 16px rgba(0,0,0,.18);
}
.cal-day-header {
    font-family: var(--font-mono); font-size: 10px; font-weight: 500;
    letter-spacing: .08em; text-transform: uppercase;
    color: var(--text-tertiary); text-align: center;
    padding: 10px 4px; background: var(--bg-elevated);
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
}
.cal-day-header:last-child { border-right: none; }
.cal-day-header.weekend { color: var(--status-cancelled); opacity: .6; }
.cal-cell {
    border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    min-height: 115px; padding: 8px 6px;
    background: var(--bg-base); position: relative;
    transition: background .12s; cursor: pointer;
}
.cal-cell:last-child { border-right: none; }
.cal-cell:hover { background: var(--bg-hover); }
.cal-cell.other-month { opacity: .3; pointer-events: none; background: var(--bg-surface); }
.cal-cell.today { background: color-mix(in srgb, var(--accent) 6%, var(--bg-base)); }
.cal-cell.has-overdue { border-top: 2px solid var(--status-cancelled); }
.cal-cell.today .cal-day-num { background: var(--accent); color: var(--text-inverse); }
.cal-day-num {
    font-family: var(--font-mono); font-size: 11px; color: var(--text-tertiary);
    margin-bottom: 5px; display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 50%; font-weight: 500;
}
.cal-chip {
    display: flex; align-items: center; gap: 4px;
    padding: 3px 6px; border-radius: 5px; font-size: 10.5px;
    margin-bottom: 3px; cursor: pointer;
    transition: filter .12s, transform .12s;
    overflow: hidden; white-space: nowrap; max-width: 100%;
}
.cal-chip:hover { filter: brightness(1.2); transform: translateY(-1px); }
.cal-chip.done  { opacity: .4; text-decoration: line-through; }
.cal-chip-dot   { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.cal-chip-text  { overflow: hidden; text-overflow: ellipsis; flex: 1; font-weight: 500; }
.cal-chip-hour  { font-family: var(--font-mono); font-size: 9px; opacity: .65; flex-shrink: 0; }
.cal-more {
    font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary);
    padding: 2px 5px; cursor: pointer; border-radius: 4px; margin-top: 1px;
    display: inline-block;
}
.cal-more:hover { background: var(--bg-overlay); color: var(--accent); }

/* ══ WEEK VIEW ═══════════════════════════════════════════════ */
.week-grid {
    border: 1px solid var(--border); border-radius: 12px; overflow: hidden;
    box-shadow: 0 2px 16px rgba(0,0,0,.18);
}
.week-header-row {
    display: grid; grid-template-columns: 52px repeat(7, 1fr);
    background: var(--bg-elevated); border-bottom: 1px solid var(--border);
}
.week-header-cell {
    padding: 10px 6px; text-align: center; border-right: 1px solid var(--border);
    font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary);
    letter-spacing: .06em; text-transform: uppercase;
}
.week-header-cell:last-child { border-right: none; }
.week-header-cell.today-col { color: var(--accent); }
.week-header-cell .wh-day { font-size: 18px; font-family: var(--font-display); color: var(--text-primary); margin-top: 2px; }
.week-header-cell.today-col .wh-day {
    background: var(--accent); color: var(--text-inverse);
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 3px auto 0;
}
.week-body { display: grid; grid-template-columns: 52px repeat(7, 1fr); }
.week-time-col { border-right: 1px solid var(--border); }
.week-time-slot {
    height: 56px; padding: 4px 6px;
    border-bottom: 1px solid var(--border);
    font-family: var(--font-mono); font-size: 9px; color: var(--text-tertiary);
    text-align: right; line-height: 1;
}
.week-day-col {
    border-right: 1px solid var(--border); position: relative;
    min-height: 56px;
}
.week-day-col:last-child { border-right: none; }
.week-day-col.today-col { background: color-mix(in srgb, var(--accent) 4%, var(--bg-base)); }
.week-task-row {
    border-bottom: 1px solid var(--border);
    padding: 4px 5px; height: 56px;
    display: flex; flex-direction: column; gap: 2px;
    overflow: hidden; position: relative;
}
.week-task-chip {
    display: flex; align-items: center; gap: 4px;
    padding: 2px 6px; border-radius: 4px; font-size: 10px;
    cursor: pointer; transition: filter .12s; overflow: hidden;
    white-space: nowrap; flex-shrink: 0;
}
.week-task-chip:hover { filter: brightness(1.2); }
.week-task-chip.done { opacity: .4; text-decoration: line-through; }

/* ══ DAY VIEW ════════════════════════════════════════════════ */
.day-view-wrap {
    border: 1px solid var(--border); border-radius: 12px; overflow: hidden;
    box-shadow: 0 2px 16px rgba(0,0,0,.18);
}
.day-view-header {
    padding: 16px 20px;
    background: var(--bg-elevated); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.day-view-title {
    font-family: var(--font-display); font-size: 22px; color: var(--text-primary);
}
.day-view-sub { font-family: var(--font-mono); font-size: 11px; color: var(--text-tertiary); margin-top: 3px; }
.day-task-card {
    margin: 10px 16px;
    background: var(--bg-elevated); border: 1px solid var(--border);
    border-radius: 10px; padding: 14px 16px;
    display: flex; align-items: flex-start; gap: 14px;
    transition: border-color .15s, box-shadow .15s;
    cursor: default;
}
.day-task-card:hover { border-color: rgba(255,255,255,.14); box-shadow: 0 4px 20px rgba(0,0,0,.2); }
.day-task-time {
    font-family: var(--font-mono); font-size: 11px; color: var(--text-tertiary);
    min-width: 38px; text-align: center; padding-top: 2px; flex-shrink: 0;
}
.day-task-line { width: 3px; border-radius: 2px; align-self: stretch; flex-shrink: 0; }
.day-task-body { flex: 1; min-width: 0; }
.day-task-name { font-size: 14px; font-weight: 500; color: var(--text-primary); line-height: 1.35; }
.day-task-meta { font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); margin-top: 4px; display: flex; gap: 10px; flex-wrap: wrap; }
.day-task-desc { font-size: 12px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5; }
.day-task-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
.day-empty {
    padding: 60px 20px; text-align: center;
    color: var(--text-tertiary); font-size: 13px;
}

/* ── Detail panel (month/week click) ── */
.cal-panel-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 10000;
    backdrop-filter: blur(3px);
}
.cal-panel-backdrop.open { display: block; }
.cal-panel {
    position: fixed; top: 0; right: 0;
    width: min(440px, 100vw); height: 100vh;
    background: var(--bg-surface); border-left: 1px solid var(--border);
    box-shadow: -16px 0 60px rgba(0,0,0,.45);
    z-index: 10001; transform: translateX(100%);
    transition: transform .28s cubic-bezier(.4,0,.2,1);
    display: flex; flex-direction: column; overflow: hidden;
}
.cal-panel.open { transform: translateX(0); }
.cal-panel-header {
    padding: 18px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    background: var(--bg-elevated);
}
.cal-panel-date { font-family: var(--font-display); font-size: 17px; color: var(--text-primary); }
.cal-panel-body { flex: 1; overflow-y: auto; padding: 14px 18px; }
.cal-panel-task {
    background: var(--bg-base); border: 1px solid var(--border);
    border-radius: 10px; padding: 12px 14px; margin-bottom: 10px;
    transition: border-color .15s;
}
.cal-panel-task:hover { border-color: rgba(255,255,255,.13); }
.cal-panel-task-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 5px; }
.cal-panel-task-name { font-size: 13px; font-weight: 500; color: var(--text-primary); line-height: 1.35; }
.cal-panel-task-meta { font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); display: flex; gap: 8px; flex-wrap: wrap; margin-top: 3px; }
.cal-panel-task-desc { font-size: 12px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5; }
.cal-panel-empty { text-align: center; padding: 48px 0; color: var(--text-tertiary); font-size: 13px; }

@media (max-width: 768px) {
    .cal-cell { min-height: 62px; padding: 4px; }
    .cal-chip-hour { display: none; }
    .cal-nav-label { font-size: 15px; min-width: 140px; }
    .week-time-col { display: none; }
    .week-header-row, .week-body { grid-template-columns: repeat(7, 1fr); }
}
</style>

<?php
$STATUS_PHP = [
    'pendiente'  => ['bg'=>'rgba(245,166,35,.18)', 'dot'=>'#f5a623','text'=>'#f5a623','label'=>'Pendiente'],
    'en_progreso'=> ['bg'=>'rgba(99,179,237,.18)',  'dot'=>'#63b3ed','text'=>'#63b3ed','label'=>'En progreso'],
    'completada' => ['bg'=>'rgba(74,240,200,.15)',  'dot'=>'#4af0c8','text'=>'#4af0c8','label'=>'Completada'],
    'cancelada'  => ['bg'=>'rgba(252,92,125,.15)',  'dot'=>'#fc5c7d','text'=>'#fc5c7d','label'=>'Cancelada'],
];
?>

<div class="page-content">

  <!-- Page header -->
  <div class="page-header" style="margin-bottom:16px;">
    <div class="page-header-row">
      <div>
        <div class="page-header-eyebrow">Semestre activo</div>
        <h1 class="page-header-title">Calendario</h1>
        <p class="page-header-sub">Visualiza tus entregas por fecha.</p>
      </div>
      <!-- View switcher -->
      <div class="cal-view-switcher">
        <a href="?view=mes&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>"
           class="cal-view-btn <?= $view_mode==='mes'?'active':'' ?>">
            <i class="bi bi-grid-3x3"></i> Mes
        </a>
        <a href="?view=semana&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>"
           class="cal-view-btn <?= $view_mode==='semana'?'active':'' ?>">
            <i class="bi bi-columns-gap"></i> Semana
        </a>
        <a href="?view=dia&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>"
           class="cal-view-btn <?= $view_mode==='dia'?'active':'' ?>">
            <i class="bi bi-calendar-day"></i> Día
        </a>
      </div>
    </div>
  </div>

  <!-- Filters + materia -->
  <div class="cal-filters">
    <?php foreach(['todos'=>'Todas','pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','cancelada'=>'Cancelada'] as $k=>$lbl): ?>
    <a href="?view=<?= $view_mode ?>&filter=<?= $k ?>&id_materia=<?= $mat_param ?>&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>"
       class="cal-filter-pill <?= $filter===$k?'active':'' ?>">
        <?= $lbl ?>
    </a>
    <?php endforeach; ?>

    <div class="relative" style="margin-left:auto;">
      <button class="btn-ghost" style="font-size:12px;" onclick="document.getElementById('calMtrDrop').classList.toggle('open')">
        <i class="bi bi-journal-bookmark"></i>
        <?php
          if ($id_materia_seleccionada) {
            $nm = array_column($materias,'nombre_materia','id_materia')[$id_materia_seleccionada] ?? 'Materia';
            echo htmlspecialchars(strlen($nm)>22?substr($nm,0,20).'…':$nm);
          } else echo 'Todas las materias';
        ?>
        <i class="bi bi-chevron-down" style="font-size:10px;"></i>
      </button>
      <div class="ctx-menu" id="calMtrDrop" style="min-width:220px;">
        <a class="ctx-item" href="?view=<?= $view_mode ?>&filter=<?= $filter ?>&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>">Todas las materias</a>
        <div class="ctx-sep"></div>
        <?php foreach ($materias as $m): ?>
        <a class="ctx-item" href="?view=<?= $view_mode ?>&filter=<?= $filter ?>&id_materia=<?= $m['id_materia'] ?>&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>">
          <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($m['color']??'#4af0c8') ?>;margin-right:6px;"></span>
          <?= htmlspecialchars($m['nombre_materia']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Legend -->
    <div class="cal-legend" style="margin-left:8px;">
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-pending)"></div>Pendiente</div>
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-progress)"></div>Progreso</div>
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-done)"></div>Lista</div>
    </div>
  </div>

<?php /* ══════════════════════ MES ══════════════════════ */ if ($view_mode === 'mes'): ?>

  <!-- Month nav -->
  <div class="cal-nav-row">
    <div class="cal-nav-center">
      <a href="?view=mes&m=<?= $prev_m ?>&y=<?= $prev_y ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-nav"><i class="bi bi-chevron-left"></i></a>
      <div>
        <div class="cal-nav-label"><?= $MONTHS_ES[$view_month] ?> <?= $view_year ?></div>
      </div>
      <a href="?view=mes&m=<?= $next_m ?>&y=<?= $next_y ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-nav"><i class="bi bi-chevron-right"></i></a>
    </div>
    <?php if ($view_month !== (int)$now_col->format('n') || $view_year !== (int)$now_col->format('Y')): ?>
    <a href="?view=mes&m=<?= $now_col->format('n') ?>&y=<?= $now_col->format('Y') ?>&d=<?= $now_col->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-today-pill">Hoy</a>
    <?php endif; ?>
  </div>

  <!-- Month grid -->
  <div class="cal-grid">
    <?php foreach ($DAYS_ES as $i => $d): ?>
    <div class="cal-day-header <?= $i>=5?'weekend':'' ?>"><?= $d ?></div>
    <?php endforeach; ?>

    <?php
    $prev_days_total = (int)(new DateTime("{$view_year}-{$view_month}-01"))->modify('-1 day')->format('j');
    for ($i = 0; $i < $start_offset; $i++):
        $pd = $prev_days_total - $start_offset + $i + 1;
    ?>
    <div class="cal-cell other-month"><span class="cal-day-num"><?= $pd ?></span></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= $days_month; $day++):
      $ds        = sprintf('%04d-%02d-%02d', $view_year, $view_month, $day);
      $day_tasks = $tasks_by_date[$ds] ?? [];
      $is_today  = ($ds === $today_str);
      $is_past   = ($ds < $today_str);
      $has_ov    = $is_past && !empty(array_filter($day_tasks, fn($t)=>!in_array($t['estado'],['completada','cancelada'])));
      $dow       = (int)(new DateTime($ds))->format('N');
      $is_wknd   = $dow >= 6;
      $dl        = $DAYS_FULL[$dow].', '.$day.' de '.$MONTHS_ES[$view_month].' '.$view_year;
      $MAX       = 3;
    ?>
    <div class="cal-cell <?= $is_today?'today':'' ?> <?= $has_ov?'has-overdue':'' ?>"
         style="<?= $is_wknd?'background:var(--bg-surface);':'' ?>"
         onclick="openPanel('<?= $ds ?>','<?= htmlspecialchars($dl,ENT_QUOTES) ?>')"
         title="<?= $dl ?>">
      <span class="cal-day-num"><?= $day ?></span>
      <?php $shown=0; foreach ($day_tasks as $t):
        if ($shown>=$MAX) break;
        $sc = $STATUS_PHP[$t['estado']] ?? $STATUS_PHP['pendiente'];
        $done = in_array($t['estado'],['completada','cancelada']);
        $shown++;
      ?>
      <div class="cal-chip <?= $done?'done':'' ?>"
           style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;"
           onclick="event.stopPropagation();openPanel('<?= $ds ?>','<?= htmlspecialchars($dl,ENT_QUOTES) ?>')">
        <div class="cal-chip-dot" style="background:<?= $sc['dot'] ?>;"></div>
        <span class="cal-chip-text"><?= htmlspecialchars($t['nombre_tarea']) ?></span>
        <span class="cal-chip-hour"><?= substr($t['hora_cierre']??'23:59:00',0,5) ?></span>
      </div>
      <?php endforeach; ?>
      <?php if (count($day_tasks) > $MAX): ?>
      <div class="cal-more" onclick="event.stopPropagation();openPanel('<?= $ds ?>','<?= htmlspecialchars($dl,ENT_QUOTES) ?>')">+<?= count($day_tasks)-$MAX ?> más</div>
      <?php endif; ?>
    </div>
    <?php endfor; ?>

    <?php
    $trailing = (7-(($start_offset+$days_month)%7))%7;
    for ($i=1;$i<=$trailing;$i++): ?>
    <div class="cal-cell other-month"><span class="cal-day-num"><?= $i ?></span></div>
    <?php endfor; ?>
  </div>

<?php /* ══════════════════════ SEMANA ══════════════════════ */ elseif ($view_mode === 'semana'): ?>

  <!-- Week nav -->
  <div class="cal-nav-row">
    <div class="cal-nav-center">
      <a href="?view=semana&m=<?= $prev_week_start->format('n') ?>&y=<?= $prev_week_start->format('Y') ?>&d=<?= $prev_week_start->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-nav"><i class="bi bi-chevron-left"></i></a>
      <div style="text-align:center;">
        <div class="cal-nav-label"><?= $week_days[0]->format('d') ?> – <?= $week_days[6]->format('d') ?> <?= $MONTHS_ES[(int)$week_days[6]->format('n')] ?> <?= $week_days[6]->format('Y') ?></div>
        <div class="cal-nav-sub">Semana <?= $week_days[0]->format('W') ?></div>
      </div>
      <a href="?view=semana&m=<?= $next_week_start->format('n') ?>&y=<?= $next_week_start->format('Y') ?>&d=<?= $next_week_start->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-nav"><i class="bi bi-chevron-right"></i></a>
    </div>
    <?php
    $in_this_week = false;
    foreach($week_days as $wd) { if($wd->format('Y-m-d')===$today_str) $in_this_week=true; }
    if (!$in_this_week): ?>
    <a href="?view=semana&m=<?= $now_col->format('n') ?>&y=<?= $now_col->format('Y') ?>&d=<?= $now_col->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-today-pill">Hoy</a>
    <?php endif; ?>
  </div>

  <!-- Week grid -->
  <div class="week-grid">
    <div class="week-header-row">
      <div class="week-header-cell" style="border-right:1px solid var(--border);"></div>
      <?php foreach ($week_days as $i => $wd):
        $wds  = $wd->format('Y-m-d');
        $wdow = (int)$wd->format('N');
        $isT  = $wds === $today_str;
      ?>
      <div class="week-header-cell <?= $isT?'today-col':'' ?> <?= $wdow>=6?'weekend':'' ?>">
        <div><?= $DAYS_ES[$wdow-1] ?></div>
        <?php if ($isT): ?>
        <div class="wh-day"><?= $wd->format('j') ?></div>
        <?php else: ?>
        <a href="?view=dia&m=<?= $wd->format('n') ?>&y=<?= $wd->format('Y') ?>&d=<?= $wd->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>"
           style="display:block;font-size:18px;font-family:var(--font-display);color:var(--text-primary);text-decoration:none;margin-top:2px;transition:.15s;"
           onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text-primary)'">
           <?= $wd->format('j') ?>
        </a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="week-body">
      <!-- Time gutter -->
      <div class="week-time-col">
        <?php for ($h=0;$h<24;$h++): ?>
        <div class="week-time-slot"><?= $h>0 ? sprintf('%02d:00',$h) : '' ?></div>
        <?php endfor; ?>
      </div>
      <!-- Day columns -->
      <?php foreach ($week_days as $wd):
        $wds      = $wd->format('Y-m-d');
        $isT      = $wds === $today_str;
        $wdow     = (int)$wd->format('N');
        $col_tasks= $tasks_by_date[$wds] ?? [];
      ?>
      <div class="week-day-col <?= $isT?'today-col':'' ?> <?= $wdow>=6?'':''; ?>"
           onclick="openPanel('<?= $wds ?>','<?= htmlspecialchars($DAYS_FULL[$wdow].', '.$wd->format('j').' de '.$MONTHS_ES[(int)$wd->format('n')].' '.$wd->format('Y'),ENT_QUOTES) ?>')">
        <?php for ($h=0;$h<24;$h++):
          $slot_tasks = array_filter($col_tasks, fn($t) => (int)substr($t['hora_cierre']??'23:00:00',0,2)===$h);
        ?>
        <div class="week-task-row">
          <?php foreach ($slot_tasks as $t):
            $sc   = $STATUS_PHP[$t['estado']] ?? $STATUS_PHP['pendiente'];
            $done = in_array($t['estado'],['completada','cancelada']);
          ?>
          <div class="week-task-chip <?= $done?'done':'' ?>"
               style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;"
               onclick="event.stopPropagation();openPanel('<?= $wds ?>','<?= htmlspecialchars($DAYS_FULL[$wdow].', '.$wd->format('j').' de '.$MONTHS_ES[(int)$wd->format('n')].' '.$wd->format('Y'),ENT_QUOTES) ?>')"
               title="<?= htmlspecialchars($t['nombre_tarea']) ?>">
            <div style="width:5px;height:5px;border-radius:50%;background:<?= $sc['dot'] ?>;flex-shrink:0;"></div>
            <span style="overflow:hidden;text-overflow:ellipsis;flex:1;font-weight:500;"><?= htmlspecialchars($t['nombre_tarea']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endfor; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

<?php /* ══════════════════════ DÍA ══════════════════════ */ else: ?>

  <!-- Day nav -->
  <div class="cal-nav-row">
    <div class="cal-nav-center">
      <a href="?view=dia&m=<?= $prev_day_dt->format('n') ?>&y=<?= $prev_day_dt->format('Y') ?>&d=<?= $prev_day_dt->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-nav"><i class="bi bi-chevron-left"></i></a>
      <div style="text-align:center;">
        <div class="cal-nav-label"><?= $DAYS_FULL[$week_dow] ?>, <?= $view_day ?> de <?= $MONTHS_ES[$view_month] ?></div>
        <div class="cal-nav-sub"><?= $view_year ?></div>
      </div>
      <a href="?view=dia&m=<?= $next_day_dt->format('n') ?>&y=<?= $next_day_dt->format('Y') ?>&d=<?= $next_day_dt->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-nav"><i class="bi bi-chevron-right"></i></a>
    </div>
    <?php if ($current_date_str !== $today_str): ?>
    <a href="?view=dia&m=<?= $now_col->format('n') ?>&y=<?= $now_col->format('Y') ?>&d=<?= $now_col->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="btn-today-pill">Hoy</a>
    <?php endif; ?>
  </div>

  <!-- Day card list -->
  <div class="day-view-wrap">
    <div class="day-view-header">
      <div>
        <div class="day-view-title"><?= $DAYS_FULL[$week_dow] ?>, <?= $view_day ?> de <?= $MONTHS_ES[$view_month] ?> <?= $view_year ?></div>
        <?php $day_count = count($tasks_by_date[$current_date_str] ?? []); ?>
        <div class="day-view-sub"><?= $day_count ?> actividad<?= $day_count!==1?'es':'' ?> este día</div>
      </div>
      <?php if ($current_date_str === $today_str): ?>
      <span style="font-family:var(--font-mono);font-size:11px;color:var(--accent);background:var(--accent-dim);padding:4px 10px;border-radius:20px;border:1px solid var(--accent);">Hoy</span>
      <?php endif; ?>
    </div>

    <?php $day_tasks = $tasks_by_date[$current_date_str] ?? []; ?>
    <?php if (empty($day_tasks)): ?>
    <div class="day-empty">
      <i class="bi bi-sun" style="font-size:32px;display:block;margin-bottom:10px;opacity:.3;"></i>
      Sin actividades para este día
    </div>
    <?php else: ?>
    <div style="padding-bottom:12px;">
      <?php foreach ($day_tasks as $t):
        $sc   = $STATUS_PHP[$t['estado']] ?? $STATUS_PHP['pendiente'];
        $done = in_array($t['estado'],['completada','cancelada']);
        $hora = substr($t['hora_cierre']??'23:59:00',0,5);
      ?>
      <div class="day-task-card">
        <div class="day-task-time"><?= $hora ?></div>
        <div class="day-task-line" style="background:<?= $sc['dot'] ?>;min-height:40px;"></div>
        <div class="day-task-body">
          <div class="day-task-name" style="<?= $done?'text-decoration:line-through;opacity:.55;':'' ?>">
            <?= calTipoEmoji($t['tipo_tarea']??'tarea') ?> <?= htmlspecialchars($t['nombre_tarea']) ?>
          </div>
          <div class="day-task-meta">
            <span style="color:<?= htmlspecialchars($t['materia_color']??'#4af0c8') ?>;">● <?= htmlspecialchars($t['nombre_materia']) ?></span>
            <span class="status-badge <?= match($t['estado']){'pendiente'=>'status-pending','en_progreso'=>'status-progress','completada'=>'status-done','cancelada'=>'status-cancelled',default=>''} ?>"><?= $sc['label'] ?></span>
          </div>
          <?php if (!empty($t['descripcion_tarea'])): ?>
          <div class="day-task-desc"><?= htmlspecialchars($t['descripcion_tarea']) ?></div>
          <?php endif; ?>
          <div class="day-task-actions">
            <?php if (!$done): ?>
            <button class="btn-ghost" style="font-size:11px;padding:4px 10px;"
                    onclick="cambiarEstadoPanel(<?= $t['id_tarea'] ?>,'completada',this)">
                ✅ Completada
            </button>
            <?php endif; ?>
            <a class="btn-ghost" href="detalle_materia.php?id_materia=<?= $t['id_materia'] ?>"
               style="font-size:11px;padding:4px 10px;text-decoration:none;">
               <i class="bi bi-arrow-right-circle"></i> Ver materia
            </a>
            <?php if (!empty($t['link'])): ?>
            <a class="btn-ghost" href="<?= htmlspecialchars($t['link']) ?>" target="_blank" rel="noopener noreferrer"
               style="font-size:11px;padding:4px 10px;text-decoration:none;color:var(--accent);border-color:var(--accent);">
               <i class="bi bi-link-45deg"></i> Abrir enlace
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

<?php endif; ?>
</div><!-- .page-content -->

<!-- Detail panel (for month + week views) -->
<div class="cal-panel-backdrop" id="panelBackdrop" onclick="closePanel()"></div>
<div class="cal-panel" id="calPanel">
  <div class="cal-panel-header">
    <span class="cal-panel-date" id="panelDateLabel">—</span>
    <button class="btn-icon" onclick="closePanel()"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="cal-panel-body" id="panelBody">
    <div class="cal-panel-empty"><i class="bi bi-calendar3" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>Sin actividades este día</div>
  </div>
</div>

<?php
$tasks_json = json_encode(array_map(function($t) {
    return [
        'id'        => $t['id_tarea'],
        'nombre'    => $t['nombre_tarea'],
        'desc'      => $t['descripcion_tarea'],
        'fecha'     => $t['fecha_cierre'],
        'hora'      => substr($t['hora_cierre'] ?? '23:59:00', 0, 5),
        'tipo'      => $t['tipo_tarea'] ?? 'tarea',
        'estado'    => $t['estado'],
        'color'     => $t['color'] ?? '#4af0c8',
        'materia'   => $t['nombre_materia'],
        'mat_color' => $t['materia_color'] ?? '#4af0c8',
        'id_materia'=> $t['id_materia'],
        'link'      => $t['link'] ?? '',
    ];
}, $all_tasks), JSON_UNESCAPED_UNICODE);
?>
<?php include_once 'footer.php'; ?>
<script>
const ALL_TASKS = <?= $tasks_json ?>;
const tasksByDate = {};
ALL_TASKS.forEach(t => {
    if (!tasksByDate[t.fecha]) tasksByDate[t.fecha] = [];
    tasksByDate[t.fecha].push(t);
});

const STATUS_COLORS = {
    pendiente:   { bg:'rgba(245,166,35,.18)',  dot:'#f5a623',text:'#f5a623',label:'Pendiente'   },
    en_progreso: { bg:'rgba(99,179,237,.18)',   dot:'#63b3ed',text:'#63b3ed',label:'En progreso' },
    completada:  { bg:'rgba(74,240,200,.15)',   dot:'#4af0c8',text:'#4af0c8',label:'Completada'  },
    cancelada:   { bg:'rgba(252,92,125,.15)',  dot:'#fc5c7d',text:'#fc5c7d',label:'Cancelada'   },
};
const TIPO_EMOJI = {tarea:'📄',quiz:'📝',parcial:'📋',examen_final:'🎯',proyecto:'🗂',otro:'📌'};

function openPanel(dateStr, dateLabel) {
    const tasks = tasksByDate[dateStr] || [];
    const body  = document.getElementById('panelBody');
    document.getElementById('panelDateLabel').textContent = dateLabel;
    if (!tasks.length) {
        body.innerHTML = `<div class="cal-panel-empty">
            <i class="bi bi-sun" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>
            Sin actividades este día</div>`;
    } else {
        body.innerHTML = tasks.map(t => {
            const sc   = STATUS_COLORS[t.estado] || STATUS_COLORS.pendiente;
            const emoji= TIPO_EMOJI[t.tipo]||'📄';
            const done = t.estado==='completada'||t.estado==='cancelada';
            const sBadge = t.estado==='pendiente'?'status-pending':t.estado==='en_progreso'?'status-progress':t.estado==='completada'?'status-done':'status-cancelled';
            return `
            <div class="cal-panel-task" style="border-left:3px solid ${sc.dot};">
                <div class="cal-panel-task-top">
                    <div>
                        <div class="cal-panel-task-name" style="${done?'text-decoration:line-through;opacity:.55;':''}">
                            ${emoji} ${escHtml(t.nombre)}
                        </div>
                        <div class="cal-panel-task-meta">
                            <span style="color:${t.mat_color};">● ${escHtml(t.materia)}</span>
                            <span>⏰ ${t.hora}</span>
                        </div>
                    </div>
                    <span class="status-badge ${sBadge}">${sc.label}</span>
                </div>
                ${t.desc?`<div class="cal-panel-task-desc">${escHtml(t.desc)}</div>`:''}
                <div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="btn-ghost" style="font-size:11px;padding:4px 10px;"
                            onclick="cambiarEstadoPanel(${t.id},'completada',this)"
                            ${done?'disabled style="opacity:.4;cursor:not-allowed;"':''}>
                        ✅ Completada
                    </button>
                    <a class="btn-ghost" href="detalle_materia.php?id_materia=${t.id_materia}"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;">
                       <i class="bi bi-arrow-right-circle"></i> Ver materia
                    </a>
                    ${t.link?`<a class="btn-ghost" href="${escHtml(t.link)}" target="_blank" rel="noopener noreferrer"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;color:var(--accent);border-color:var(--accent);">
                       <i class="bi bi-link-45deg"></i> Abrir enlace</a>`:''}
                </div>
            </div>`;
        }).join('');
    }
    document.getElementById('panelBackdrop').classList.add('open');
    document.getElementById('calPanel').classList.add('open');
}

function closePanel() {
    document.getElementById('panelBackdrop').classList.remove('open');
    document.getElementById('calPanel').classList.remove('open');
}

function cambiarEstadoPanel(id, estado, btn) {
    fetch('../controller/cmb_estado.php', {
        method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id_tarea=${id}&nuevo_estado=${encodeURIComponent(estado)}`
    }).then(r=>r.json()).then(d=>{
        if (d.success) { showToast('Estado actualizado.','success'); setTimeout(()=>location.reload(),500); }
        else Swal.fire('Error', d.message||'No se pudo actualizar.','error');
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

document.addEventListener('keydown', e => { if(e.key==='Escape') closePanel(); });
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.ctx-menu.open').forEach(x=>x.classList.remove('open'));
    }
});
</script>