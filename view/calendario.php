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

$stmtMat = $pdo->prepare("SELECT id_materia, nombre_materia, color FROM materias WHERE id_usuario = ? ORDER BY nombre_materia ASC");
$stmtMat->execute([$id_usuario]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT t.id_tarea, t.id_materia, t.nombre_tarea, t.descripcion_tarea,
               t.fecha_creacion, t.fecha_cierre, t.hora_cierre, t.tipo_tarea,
               t.estado, t.color, t.link, m.nombre_materia, m.color AS materia_color
        FROM tareas t INNER JOIN materias m ON t.id_materia = m.id_materia
        WHERE m.id_usuario = :uid";
$params = [':uid' => $id_usuario];
if ($filter !== 'todos')      { $sql .= " AND t.estado = :estado"; $params[':estado'] = $filter; }
if ($id_materia_seleccionada) { $sql .= " AND m.id_materia = :mat"; $params[':mat'] = $id_materia_seleccionada; }
$sql .= " ORDER BY t.fecha_cierre ASC, t.hora_cierre ASC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$all_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tasks_by_date = [];
foreach ($all_tasks as $t) $tasks_by_date[$t['fecha_cierre']][] = $t;

$first_day    = new DateTime("{$view_year}-{$view_month}-01");
$days_month   = (int)$first_day->format('t');
$start_dow    = (int)$first_day->format('N');
$start_offset = $start_dow - 1;

$current_date_str = sprintf('%04d-%02d-%02d', $view_year, $view_month, $view_day);
$current_dt       = new DateTime($current_date_str);
$week_dow         = (int)$current_dt->format('N');
$week_start       = clone $current_dt;
$week_start->modify('-' . ($week_dow - 1) . ' days');
$week_days = [];
for ($i = 0; $i < 7; $i++) { $d = clone $week_start; $d->modify("+{$i} days"); $week_days[] = $d; }

$MONTHS_ES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$DAYS_ES   = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
$DAYS_FULL = ['','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

$today_str = $now_col->format('Y-m-d');
$mat_param = $id_materia_seleccionada ?? '';

$prev_m = $view_month - 1; $prev_y = $view_year;
if ($prev_m < 1)  { $prev_m = 12; $prev_y--; }
$next_m = $view_month + 1; $next_y = $view_year;
if ($next_m > 12) { $next_m = 1;  $next_y++; }

$prev_week_start = clone $week_start; $prev_week_start->modify('-7 days');
$next_week_start = clone $week_start; $next_week_start->modify('+7 days');
$prev_day_dt = clone $current_dt; $prev_day_dt->modify('-1 day');
$next_day_dt = clone $current_dt; $next_day_dt->modify('+1 day');

function calTipoEmoji($t) {
    return match($t) { 'quiz'=>'📝','parcial'=>'📋','examen_final'=>'🎯','proyecto'=>'🗂','otro'=>'📌',default=>'📄' };
}

$STATUS_PHP = [
    'pendiente'  => ['bg'=>'rgba(245,166,35,.15)','dot'=>'#f5a623','text'=>'#f5a623','label'=>'Pendiente',  'border'=>'rgba(245,166,35,.35)'],
    'en_progreso'=> ['bg'=>'rgba(99,179,237,.15)', 'dot'=>'#63b3ed','text'=>'#63b3ed','label'=>'En progreso','border'=>'rgba(99,179,237,.35)'],
    'completada' => ['bg'=>'rgba(74,240,200,.12)', 'dot'=>'#4af0c8','text'=>'#4af0c8','label'=>'Completada', 'border'=>'rgba(74,240,200,.3)'],
    'cancelada'  => ['bg'=>'rgba(252,92,125,.12)', 'dot'=>'#fc5c7d','text'=>'#fc5c7d','label'=>'Cancelada',  'border'=>'rgba(252,92,125,.3)'],
];

include_once 'encabezado.php';
?>
<style>
/* ═══════════════════════════════════════════════════════════════
   CALENDAR — Professional redesign inspired by Fluid Calendar
═══════════════════════════════════════════════════════════════ */

/* ── Toolbar ── */
.cal-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
    padding: 12px 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 14px;
}
.cal-toolbar-left  { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cal-toolbar-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* View switcher */
.cal-view-switcher {
    display: flex; background: var(--bg-elevated);
    border: 1px solid var(--border); border-radius: 9px; padding: 3px; gap: 2px;
}
.cal-view-btn {
    font-family: var(--font-mono); font-size: 11px; padding: 5px 13px;
    border-radius: 6px; border: none; background: transparent;
    color: var(--text-secondary); cursor: pointer; text-decoration: none;
    display: flex; align-items: center; gap: 5px; transition: all .15s; white-space: nowrap;
}
.cal-view-btn:hover { color: var(--text-primary); background: var(--bg-overlay); }
.cal-view-btn.active { background: var(--accent); color: #080c14; font-weight: 600; }

/* Nav group */
.cal-nav-group { display: flex; align-items: center; gap: 4px; }
.cal-nav-btn {
    width: 30px; height: 30px; border-radius: 7px;
    border: 1px solid var(--border); background: var(--bg-elevated);
    color: var(--text-secondary); cursor: pointer; display: flex;
    align-items: center; justify-content: center; font-size: 12px;
    transition: all .15s; text-decoration: none;
}
.cal-nav-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); }
.cal-nav-label {
    font-family: var(--font-display); font-size: 16px; font-weight: 500;
    color: var(--text-primary); padding: 0 10px; white-space: nowrap; min-width: 190px; text-align: center;
}
.cal-nav-sub { font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); display: block; text-align: center; margin-top: 1px; }

.btn-today {
    font-family: var(--font-mono); font-size: 11px; padding: 5px 12px;
    border-radius: 7px; border: 1px solid var(--border);
    background: var(--bg-elevated); color: var(--text-secondary);
    cursor: pointer; text-decoration: none; transition: all .15s; white-space: nowrap;
}
.btn-today:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); }

/* Filter pills */
.cal-filter-pill {
    font-size: 11px; padding: 4px 11px; border-radius: 20px;
    border: 1px solid var(--border); font-family: var(--font-mono);
    text-decoration: none; transition: all .15s; white-space: nowrap;
    color: var(--text-secondary); background: transparent; cursor: pointer;
}
.cal-filter-pill:hover  { border-color: var(--accent); color: var(--accent); }
.cal-filter-pill.active { background: var(--accent-dim); color: var(--accent); border-color: var(--accent); font-weight: 500; }

/* Materia dropdown trigger */
.cal-mat-btn {
    font-size: 11px; font-family: var(--font-mono); padding: 5px 11px;
    border-radius: 7px; border: 1px solid var(--border);
    background: var(--bg-elevated); color: var(--text-secondary);
    cursor: pointer; display: flex; align-items: center; gap: 5px;
    transition: all .15s; white-space: nowrap;
}
.cal-mat-btn:hover { border-color: var(--accent); color: var(--accent); }

/* Legend */
.cal-legend { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.cal-legend-item { display: flex; align-items: center; gap: 4px; font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); }
.cal-legend-dot  { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

/* ══ MONTH VIEW ══════════════════════════════════════════════ */
.cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr);
    border-radius: 14px; overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: 0 4px 24px rgba(0,0,0,.2);
}
.cal-day-header {
    font-family: var(--font-mono); font-size: 10px; font-weight: 600;
    letter-spacing: .1em; text-transform: uppercase;
    color: var(--text-tertiary); text-align: center;
    padding: 11px 4px; background: var(--bg-elevated);
    border-bottom: 1px solid var(--border); border-right: 1px solid var(--border);
}
.cal-day-header:last-child { border-right: none; }
.cal-day-header.weekend { color: var(--status-cancelled); opacity: .55; }

.cal-cell {
    border-right: 1px solid var(--border); border-bottom: 1px solid var(--border);
    min-height: 120px; padding: 8px 7px;
    background: var(--bg-base); position: relative;
    transition: background .12s; cursor: pointer; vertical-align: top;
}
.cal-cell:nth-child(7n) { border-right: none; }
.cal-cell:hover { background: var(--bg-hover); }
.cal-cell.other-month { opacity: .28; pointer-events: none; background: var(--bg-surface); }
.cal-cell.today { background: color-mix(in srgb, var(--accent) 5%, var(--bg-base)); }
.cal-cell.has-overdue { box-shadow: inset 2px 0 0 var(--status-cancelled); }

.cal-day-num {
    font-family: var(--font-mono); font-size: 11px; font-weight: 600;
    color: var(--text-tertiary); margin-bottom: 6px;
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 50%;
}
.cal-cell.today .cal-day-num {
    background: var(--accent); color: #080c14; font-weight: 700;
}

/* Month chip */
.cal-chip {
    display: flex; align-items: center; gap: 5px;
    padding: 3px 7px; border-radius: 5px; font-size: 10.5px;
    margin-bottom: 3px; cursor: pointer; border: 1px solid transparent;
    transition: filter .12s, transform .12s, border-color .12s;
    overflow: hidden; white-space: nowrap; max-width: 100%;
}
.cal-chip:hover { filter: brightness(1.18); transform: translateY(-1px); border-color: rgba(255,255,255,.1); }
.cal-chip.done  { opacity: .38; text-decoration: line-through; }
.cal-chip-dot   { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.cal-chip-text  { overflow: hidden; text-overflow: ellipsis; flex: 1; font-weight: 500; }
.cal-chip-hour  { font-family: var(--font-mono); font-size: 9px; opacity: .6; flex-shrink: 0; }
.cal-more {
    font-family: var(--font-mono); font-size: 10px; color: var(--accent);
    padding: 2px 6px; cursor: pointer; border-radius: 4px; margin-top: 1px;
    background: var(--accent-dim); display: inline-block; transition: filter .12s;
}
.cal-more:hover { filter: brightness(1.2); }

/* ══ WEEK VIEW ═══════════════════════════════════════════════ */
.week-wrap {
    border: 1px solid var(--border); border-radius: 14px; overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.2); background: var(--bg-base);
}
.week-header-row {
    display: grid; grid-template-columns: 56px repeat(7, 1fr);
    background: var(--bg-elevated); border-bottom: 1px solid var(--border);
    position: sticky; top: 0; z-index: 2;
}
.week-time-gutter-head {
    border-right: 1px solid var(--border); padding: 10px 0;
    font-family: var(--font-mono); font-size: 9px; color: var(--text-tertiary);
    text-align: center; letter-spacing: .06em;
}
.week-header-cell {
    padding: 10px 6px; text-align: center;
    border-right: 1px solid var(--border);
    font-family: var(--font-mono); font-size: 10px;
    color: var(--text-tertiary); letter-spacing: .06em;
    text-transform: uppercase; transition: background .12s;
}
.week-header-cell:last-child { border-right: none; }
.week-header-cell.today-col { color: var(--accent); background: color-mix(in srgb,var(--accent) 6%,var(--bg-elevated)); }
.week-header-cell.weekend   { opacity: .6; }
.week-hday {
    font-size: 20px; font-family: var(--font-display); font-weight: 500;
    color: var(--text-primary); margin-top: 3px; line-height: 1;
    display: block; text-decoration: none; transition: color .15s;
}
.week-hday:hover { color: var(--accent); }
.week-hday.today-num {
    width: 32px; height: 32px; background: var(--accent); color: #080c14;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin: 3px auto 0; font-weight: 700; font-size: 16px;
}

/* Week scroll body */
.week-scroll { overflow-y: auto; max-height: 600px; }
.week-body {
    display: grid; grid-template-columns: 56px repeat(7, 1fr);
    min-height: 100%;
}
.week-time-col { border-right: 1px solid var(--border); }
.week-time-slot {
    height: 60px; padding: 4px 8px 0;
    border-bottom: 1px solid rgba(255,255,255,.04);
    font-family: var(--font-mono); font-size: 9px; color: var(--text-tertiary);
    text-align: right; line-height: 1; user-select: none;
}
.week-day-col {
    border-right: 1px solid var(--border); position: relative;
}
.week-day-col:last-child { border-right: none; }
.week-day-col.today-col { background: color-mix(in srgb,var(--accent) 3%,var(--bg-base)); }
.week-day-col.weekend-col { background: var(--bg-surface); opacity: .85; }

/* Hour row inside column */
.week-hour-row {
    height: 60px; border-bottom: 1px solid rgba(255,255,255,.04);
    position: relative; overflow: visible;
}
.week-hour-row:last-child { border-bottom: none; }

/* Task pill — NEW: flex row inside hour, wrap gracefully */
.week-tasks-bucket {
    position: absolute; inset: 2px 3px;
    display: flex; flex-direction: column; gap: 2px;
    overflow: hidden;
}
.week-chip {
    display: flex; align-items: center; gap: 4px;
    padding: 2px 6px; border-radius: 4px; font-size: 10px;
    cursor: pointer; overflow: hidden; white-space: nowrap;
    transition: filter .12s, transform .12s; flex-shrink: 0;
    border-left: 2px solid transparent;
}
.week-chip:hover { filter: brightness(1.2); transform: translateX(1px); }
.week-chip.done { opacity: .38; text-decoration: line-through; }
.week-chip-text { overflow: hidden; text-overflow: ellipsis; flex: 1; font-weight: 500; }

/* "+N más" inside week cell */
.week-more {
    font-family: var(--font-mono); font-size: 9px; color: var(--accent);
    padding: 1px 5px; background: var(--accent-dim);
    border-radius: 3px; cursor: pointer; display: inline-block;
    align-self: flex-start; flex-shrink: 0;
}

/* ══ DAY VIEW ════════════════════════════════════════════════ */
.day-wrap {
    border: 1px solid var(--border); border-radius: 14px; overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.2);
}
.day-header {
    padding: 18px 22px; background: var(--bg-elevated);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
}
.day-header-title { font-family: var(--font-display); font-size: 21px; color: var(--text-primary); }
.day-header-sub   { font-family: var(--font-mono); font-size: 11px; color: var(--text-tertiary); margin-top: 3px; }
.day-today-badge  {
    font-family: var(--font-mono); font-size: 10px; color: var(--accent);
    background: var(--accent-dim); padding: 4px 12px;
    border-radius: 20px; border: 1px solid var(--accent);
}
.day-body { padding: 0; }
.day-hour-row {
    display: flex; gap: 0; border-bottom: 1px solid rgba(255,255,255,.04);
    min-height: 60px; position: relative;
}
.day-hour-row:last-child { border-bottom: none; }
.day-hour-row.has-tasks { background: color-mix(in srgb, var(--bg-elevated) 50%, var(--bg-base)); }
.day-time-label {
    width: 56px; flex-shrink: 0; padding: 8px 10px 0;
    font-family: var(--font-mono); font-size: 9px; color: var(--text-tertiary);
    text-align: right; border-right: 1px solid var(--border); line-height: 1;
}
.day-tasks-col { flex: 1; padding: 4px 12px; display: flex; flex-direction: column; gap: 5px; min-width: 0; }
.day-empty-slot { flex: 1; }

/* Day task card */
.day-card {
    background: var(--bg-elevated); border: 1px solid var(--border);
    border-radius: 10px; padding: 12px 14px;
    display: flex; align-items: flex-start; gap: 12px;
    transition: border-color .15s, box-shadow .15s, transform .15s;
}
.day-card:hover { border-color: rgba(255,255,255,.14); box-shadow: 0 4px 20px rgba(0,0,0,.22); transform: translateX(2px); }
.day-card-bar { width: 3px; border-radius: 2px; align-self: stretch; flex-shrink: 0; min-height: 36px; }
.day-card-body { flex: 1; min-width: 0; }
.day-card-name { font-size: 13px; font-weight: 500; color: var(--text-primary); line-height: 1.35; margin-bottom: 4px; }
.day-card-name.done { text-decoration: line-through; opacity: .5; }
.day-card-meta { font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); display: flex; gap: 10px; flex-wrap: wrap; }
.day-card-desc { font-size: 12px; color: var(--text-secondary); margin-top: 6px; line-height: 1.55; }
.day-card-actions { display: flex; gap: 6px; margin-top: 9px; flex-wrap: wrap; }
.day-empty-msg { padding: 64px 20px; text-align: center; color: var(--text-tertiary); font-size: 13px; }

/* ── Detail panel ── */
.cal-panel-backdrop {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.5); z-index: 10000; backdrop-filter: blur(4px);
}
.cal-panel-backdrop.open { display: block; }
.cal-panel {
    position: fixed; top: 0; right: 0;
    width: min(440px, 100vw); height: 100vh;
    background: var(--bg-surface); border-left: 1px solid var(--border);
    box-shadow: -20px 0 60px rgba(0,0,0,.5);
    z-index: 10001; transform: translateX(100%);
    transition: transform .28s cubic-bezier(.4,0,.2,1);
    display: flex; flex-direction: column; overflow: hidden;
}
.cal-panel.open { transform: translateX(0); }
.cal-panel-header {
    padding: 18px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0; background: var(--bg-elevated);
}
.cal-panel-date { font-family: var(--font-display); font-size: 17px; color: var(--text-primary); }
.cal-panel-body { flex: 1; overflow-y: auto; padding: 14px 18px; }
.cal-panel-task {
    background: var(--bg-base); border: 1px solid var(--border);
    border-radius: 10px; padding: 13px 15px; margin-bottom: 10px;
    border-left: 3px solid transparent; transition: border-color .15s, box-shadow .15s;
}
.cal-panel-task:hover { box-shadow: 0 4px 16px rgba(0,0,0,.2); }
.cal-panel-task-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 5px; }
.cal-panel-task-name { font-size: 13px; font-weight: 500; color: var(--text-primary); line-height: 1.35; }
.cal-panel-task-meta { font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); display: flex; gap: 8px; flex-wrap: wrap; margin-top: 3px; }
.cal-panel-task-desc { font-size: 12px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5; }
.cal-panel-empty { text-align: center; padding: 48px 0; color: var(--text-tertiary); font-size: 13px; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .week-wrap { overflow-x: auto; }
    .week-header-row, .week-body { min-width: 640px; }
    .cal-toolbar { padding: 10px 12px; gap: 8px; }
    .cal-legend { display: none; }
}
@media (max-width: 640px) {
    .cal-cell { min-height: 70px; padding: 5px 4px; }
    .cal-chip-hour { display: none; }
    .cal-nav-label { font-size: 13px; min-width: 140px; }
    .week-scroll { max-height: 480px; }
    .day-card { padding: 10px 12px; }
    .cal-toolbar-right { display: none; }
}
</style>

<div class="page-content">

  <!-- Page header -->
  <div class="page-header" style="margin-bottom:16px;">
    <div class="page-header-row">
      <div>
        <div class="page-header-eyebrow">Semestre activo</div>
        <h1 class="page-header-title">Calendario</h1>
        <p class="page-header-sub">Visualiza tus entregas por fecha.</p>
      </div>
      <div class="cal-view-switcher">
        <a href="?view=mes&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="cal-view-btn <?= $view_mode==='mes'?'active':'' ?>"><i class="bi bi-grid-3x3-gap"></i> Mes</a>
        <a href="?view=semana&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="cal-view-btn <?= $view_mode==='semana'?'active':'' ?>"><i class="bi bi-columns-gap"></i> Semana</a>
        <a href="?view=dia&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>" class="cal-view-btn <?= $view_mode==='dia'?'active':'' ?>"><i class="bi bi-calendar-day"></i> Día</a>
      </div>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="cal-toolbar">
    <div class="cal-toolbar-left">

      <!-- Nav -->
      <?php
        if ($view_mode === 'mes') {
            $prev_href = "?view=mes&m={$prev_m}&y={$prev_y}&d={$view_day}&filter={$filter}&id_materia={$mat_param}";
            $next_href = "?view=mes&m={$next_m}&y={$next_y}&d={$view_day}&filter={$filter}&id_materia={$mat_param}";
            $nav_label = $MONTHS_ES[$view_month] . ' ' . $view_year;
            $nav_sub   = '';
            $today_href= "?view=mes&m={$now_col->format('n')}&y={$now_col->format('Y')}&d={$now_col->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $show_today= ($view_month !== (int)$now_col->format('n') || $view_year !== (int)$now_col->format('Y'));
        } elseif ($view_mode === 'semana') {
            $prev_href = "?view=semana&m={$prev_week_start->format('n')}&y={$prev_week_start->format('Y')}&d={$prev_week_start->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $next_href = "?view=semana&m={$next_week_start->format('n')}&y={$next_week_start->format('Y')}&d={$next_week_start->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $nav_label = $week_days[0]->format('d') . ' – ' . $week_days[6]->format('d') . ' ' . $MONTHS_ES[(int)$week_days[6]->format('n')] . ' ' . $week_days[6]->format('Y');
            $nav_sub   = 'Semana ' . $week_days[0]->format('W');
            $today_href= "?view=semana&m={$now_col->format('n')}&y={$now_col->format('Y')}&d={$now_col->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $in_week   = false; foreach($week_days as $wd) { if($wd->format('Y-m-d')===$today_str) $in_week=true; }
            $show_today= !$in_week;
        } else {
            $prev_href = "?view=dia&m={$prev_day_dt->format('n')}&y={$prev_day_dt->format('Y')}&d={$prev_day_dt->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $next_href = "?view=dia&m={$next_day_dt->format('n')}&y={$next_day_dt->format('Y')}&d={$next_day_dt->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $nav_label = $DAYS_FULL[$week_dow] . ', ' . $view_day . ' de ' . $MONTHS_ES[$view_month];
            $nav_sub   = (string)$view_year;
            $today_href= "?view=dia&m={$now_col->format('n')}&y={$now_col->format('Y')}&d={$now_col->format('j')}&filter={$filter}&id_materia={$mat_param}";
            $show_today= ($current_date_str !== $today_str);
        }
      ?>
      <div class="cal-nav-group">
        <a href="<?= $prev_href ?>" class="cal-nav-btn"><i class="bi bi-chevron-left"></i></a>
        <div>
          <div class="cal-nav-label"><?= $nav_label ?></div>
          <?php if ($nav_sub): ?><span class="cal-nav-sub"><?= $nav_sub ?></span><?php endif; ?>
        </div>
        <a href="<?= $next_href ?>" class="cal-nav-btn"><i class="bi bi-chevron-right"></i></a>
      </div>
      <?php if ($show_today): ?><a href="<?= $today_href ?>" class="btn-today">Hoy</a><?php endif; ?>

      <!-- Status filters -->
      <?php foreach(['todos'=>'Todas','pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada','cancelada'=>'Cancelada'] as $k=>$lbl): ?>
      <a href="?view=<?= $view_mode ?>&filter=<?= $k ?>&id_materia=<?= $mat_param ?>&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>"
         class="cal-filter-pill <?= $filter===$k?'active':'' ?>"><?= $lbl ?></a>
      <?php endforeach; ?>
    </div>

    <div class="cal-toolbar-right">
      <!-- Materia filter -->
      <div class="relative">
        <button class="cal-mat-btn" onclick="document.getElementById('calMtrDrop').classList.toggle('open')">
          <i class="bi bi-journal-bookmark"></i>
          <?php
            if ($id_materia_seleccionada) {
              $nm = array_column($materias,'nombre_materia','id_materia')[$id_materia_seleccionada] ?? 'Materia';
              echo htmlspecialchars(strlen($nm)>20?substr($nm,0,18).'…':$nm);
            } else echo 'Todas las materias';
          ?>
          <i class="bi bi-chevron-down" style="font-size:9px;"></i>
        </button>
        <div class="ctx-menu" id="calMtrDrop" style="min-width:220px;right:0;left:auto;">
          <a class="ctx-item" href="?view=<?= $view_mode ?>&filter=<?= $filter ?>&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>">Todas las materias</a>
          <div class="ctx-sep"></div>
          <?php foreach ($materias as $m): ?>
          <a class="ctx-item" href="?view=<?= $view_mode ?>&filter=<?= $filter ?>&id_materia=<?= $m['id_materia'] ?>&m=<?= $view_month ?>&y=<?= $view_year ?>&d=<?= $view_day ?>">
            <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:<?= htmlspecialchars($m['color']??'#4af0c8') ?>;margin-right:6px;"></span>
            <?= htmlspecialchars($m['nombre_materia']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Legend -->
      <div class="cal-legend">
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#f5a623"></div>Pendiente</div>
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#63b3ed"></div>Progreso</div>
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#4af0c8"></div>Lista</div>
        <div class="cal-legend-item"><div class="cal-legend-dot" style="background:#fc5c7d"></div>Cancelada</div>
      </div>
    </div>
  </div>

<?php /* ══════════════ MES ══════════════ */ if ($view_mode === 'mes'): ?>

  <div class="cal-grid">
    <?php foreach ($DAYS_ES as $i => $d): ?>
    <div class="cal-day-header <?= $i>=5?'weekend':'' ?>"><?= $d ?></div>
    <?php endforeach; ?>

    <?php
    $prev_days_total = (int)(new DateTime("{$view_year}-{$view_month}-01"))->modify('-1 day')->format('j');
    for ($i=0; $i<$start_offset; $i++):
        $pd = $prev_days_total - $start_offset + $i + 1;
    ?>
    <div class="cal-cell other-month"><span class="cal-day-num"><?= $pd ?></span></div>
    <?php endfor; ?>

    <?php for ($day=1; $day<=$days_month; $day++):
      $ds        = sprintf('%04d-%02d-%02d', $view_year, $view_month, $day);
      $day_tasks = $tasks_by_date[$ds] ?? [];
      $is_today  = ($ds === $today_str);
      $is_past   = ($ds < $today_str);
      $has_ov    = $is_past && !empty(array_filter($day_tasks, fn($t)=>!in_array($t['estado'],['completada','cancelada'])));
      $dow       = (int)(new DateTime($ds))->format('N');
      $dl        = $DAYS_FULL[$dow].', '.$day.' de '.$MONTHS_ES[$view_month].' '.$view_year;
      $MAX       = 3;
    ?>
    <div class="cal-cell <?= $is_today?'today':'' ?> <?= $has_ov?'has-overdue':'' ?>"
         style="<?= $dow>=6?'background:var(--bg-surface);':'' ?>"
         onclick="openPanel('<?= $ds ?>','<?= htmlspecialchars($dl,ENT_QUOTES) ?>')"
         title="<?= $dl ?>">
      <span class="cal-day-num"><?= $day ?></span>
      <?php $shown=0; foreach ($day_tasks as $t):
        if ($shown>=$MAX) break;
        $sc   = $STATUS_PHP[$t['estado']] ?? $STATUS_PHP['pendiente'];
        $done = in_array($t['estado'],['completada','cancelada']);
        $shown++;
      ?>
      <div class="cal-chip <?= $done?'done':'' ?>"
           style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;border-color:<?= $sc['border'] ?>;"
           onclick="event.stopPropagation();openPanel('<?= $ds ?>','<?= htmlspecialchars($dl,ENT_QUOTES) ?>')"
           title="<?= htmlspecialchars($t['nombre_tarea']) ?>">
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

<?php /* ══════════════ SEMANA ══════════════ */ elseif ($view_mode === 'semana'): ?>

  <div class="week-wrap">
    <!-- Header -->
    <div class="week-header-row">
      <div class="week-time-gutter-head">UTC−5</div>
      <?php foreach ($week_days as $wd):
        $wds  = $wd->format('Y-m-d');
        $wdow = (int)$wd->format('N');
        $isT  = ($wds === $today_str);
        $cnt  = count($tasks_by_date[$wds] ?? []);
      ?>
      <div class="week-header-cell <?= $isT?'today-col':'' ?> <?= $wdow>=6?'weekend':'' ?>">
        <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
          <?= $DAYS_ES[$wdow-1] ?>
          <?php if ($cnt): ?>
          <span style="font-family:var(--font-mono);font-size:9px;background:var(--accent-dim);color:var(--accent);padding:1px 5px;border-radius:3px;"><?= $cnt ?></span>
          <?php endif; ?>
        </div>
        <?php if ($isT): ?>
        <span class="week-hday today-num"><?= $wd->format('j') ?></span>
        <?php else: ?>
        <a class="week-hday" href="?view=dia&m=<?= $wd->format('n') ?>&y=<?= $wd->format('Y') ?>&d=<?= $wd->format('j') ?>&filter=<?= $filter ?>&id_materia=<?= $mat_param ?>"><?= $wd->format('j') ?></a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Scrollable body -->
    <div class="week-scroll">
      <div class="week-body">
        <!-- Time gutter -->
        <div class="week-time-col">
          <?php for ($h=0;$h<24;$h++): ?>
          <div class="week-time-slot"><?= $h>0?sprintf('%02d:00',$h):'' ?></div>
          <?php endfor; ?>
        </div>
        <!-- Day columns -->
        <?php foreach ($week_days as $wd):
          $wds       = $wd->format('Y-m-d');
          $isT       = ($wds === $today_str);
          $wdow      = (int)$wd->format('N');
          $col_tasks = $tasks_by_date[$wds] ?? [];
          $dl_week   = $DAYS_FULL[$wdow].', '.$wd->format('j').' de '.$MONTHS_ES[(int)$wd->format('n')].' '.$wd->format('Y');
        ?>
        <div class="week-day-col <?= $isT?'today-col':'' ?> <?= $wdow>=6?'weekend-col':'' ?>"
             onclick="openPanel('<?= $wds ?>','<?= htmlspecialchars($dl_week,ENT_QUOTES) ?>')">
          <?php for ($h=0;$h<24;$h++):
            $slot = array_values(array_filter($col_tasks, fn($t)=>(int)substr($t['hora_cierre']??'23:00:00',0,2)===$h));
            $MAX_WEEK = 2;
          ?>
          <div class="week-hour-row">
            <?php if (!empty($slot)): ?>
            <div class="week-tasks-bucket" onclick="event.stopPropagation();openPanel('<?= $wds ?>','<?= htmlspecialchars($dl_week,ENT_QUOTES) ?>')">
              <?php $sw=0; foreach($slot as $t):
                if ($sw>=$MAX_WEEK) break;
                $sc   = $STATUS_PHP[$t['estado']] ?? $STATUS_PHP['pendiente'];
                $done = in_array($t['estado'],['completada','cancelada']);
                $sw++;
              ?>
              <div class="week-chip <?= $done?'done':'' ?>"
                   style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;border-left-color:<?= $sc['dot'] ?>;"
                   title="<?= htmlspecialchars($t['nombre_tarea']) ?> — <?= htmlspecialchars($t['nombre_materia']) ?>">
                <span class="week-chip-text"><?= htmlspecialchars($t['nombre_tarea']) ?></span>
              </div>
              <?php endforeach; ?>
              <?php if (count($slot) > $MAX_WEEK): ?>
              <span class="week-more">+<?= count($slot)-$MAX_WEEK ?> más</span>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endfor; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

<?php /* ══════════════ DÍA ══════════════ */ else: ?>

  <div class="day-wrap">
    <div class="day-header">
      <div>
        <div class="day-header-title"><?= $DAYS_FULL[$week_dow] ?>, <?= $view_day ?> de <?= $MONTHS_ES[$view_month] ?> <?= $view_year ?></div>
        <?php $day_count = count($tasks_by_date[$current_date_str] ?? []); ?>
        <div class="day-header-sub"><?= $day_count ?> actividad<?= $day_count!==1?'es':'' ?> programada<?= $day_count!==1?'s':'' ?></div>
      </div>
      <?php if ($current_date_str === $today_str): ?>
      <span class="day-today-badge"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Hoy</span>
      <?php endif; ?>
    </div>

    <div class="day-body">
      <?php
      $day_tasks_all = $tasks_by_date[$current_date_str] ?? [];
      // Index by hour
      $by_hour = [];
      foreach ($day_tasks_all as $t) {
          $h = (int)substr($t['hora_cierre']??'23:00:00',0,2);
          $by_hour[$h][] = $t;
      }
      // Only render hours that have tasks + 1h before/after, or all 24 if no tasks
      $hours_to_show = empty($day_tasks_all) ? range(8,22) : range(0,23);
      ?>

      <?php if (empty($day_tasks_all)): ?>
      <div class="day-empty-msg">
        <i class="bi bi-sun" style="font-size:36px;display:block;margin-bottom:12px;opacity:.25;"></i>
        Sin actividades para este día
      </div>
      <?php else: foreach ($hours_to_show as $h):
        $slot = $by_hour[$h] ?? [];
      ?>
      <div class="day-hour-row <?= !empty($slot)?'has-tasks':'' ?>">
        <div class="day-time-label"><?= $h>0?sprintf('%02d:00',$h):'' ?></div>
        <?php if (!empty($slot)): ?>
        <div class="day-tasks-col">
          <?php foreach ($slot as $t):
            $sc   = $STATUS_PHP[$t['estado']] ?? $STATUS_PHP['pendiente'];
            $done = in_array($t['estado'],['completada','cancelada']);
            $hora = substr($t['hora_cierre']??'23:59:00',0,5);
          ?>
          <div class="day-card">
            <div class="day-card-bar" style="background:<?= $sc['dot'] ?>;"></div>
            <div class="day-card-body">
              <div class="day-card-name <?= $done?'done':'' ?>"><?= calTipoEmoji($t['tipo_tarea']??'tarea') ?> <?= htmlspecialchars($t['nombre_tarea']) ?></div>
              <div class="day-card-meta">
                <span style="color:<?= htmlspecialchars($t['materia_color']??'#4af0c8') ?>;">● <?= htmlspecialchars($t['nombre_materia']) ?></span>
                <span>⏰ <?= $hora ?></span>
                <span class="status-badge <?= match($t['estado']){'pendiente'=>'status-pending','en_progreso'=>'status-progress','completada'=>'status-done','cancelada'=>'status-cancelled',default=>''} ?>"><?= $sc['label'] ?></span>
              </div>
              <?php if (!empty($t['descripcion_tarea'])): ?>
              <div class="day-card-desc"><?= htmlspecialchars($t['descripcion_tarea']) ?></div>
              <?php endif; ?>
              <div class="day-card-actions">
                <?php if (!$done): ?>
                <button class="btn-ghost" style="font-size:11px;padding:4px 10px;"
                        onclick="cambiarEstadoPanel(<?= $t['id_tarea'] ?>,'completada',this)">✅ Completada</button>
                <?php endif; ?>
                <a class="btn-ghost" href="detalle_materia.php?id_materia=<?= $t['id_materia'] ?>"
                   style="font-size:11px;padding:4px 10px;text-decoration:none;">
                   <i class="bi bi-arrow-right-circle"></i> Ver materia</a>
                <?php if (!empty($t['link'])): ?>
                <a class="btn-ghost" href="<?= htmlspecialchars($t['link']) ?>" target="_blank" rel="noopener noreferrer"
                   style="font-size:11px;padding:4px 10px;text-decoration:none;color:var(--accent);border-color:var(--accent);">
                   <i class="bi bi-link-45deg"></i> Abrir enlace</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="day-empty-slot"></div>
        <?php endif; ?>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

<?php endif; ?>
</div>

<!-- Detail panel -->
<div class="cal-panel-backdrop" id="panelBackdrop" onclick="closePanel()"></div>
<div class="cal-panel" id="calPanel">
  <div class="cal-panel-header">
    <span class="cal-panel-date" id="panelDateLabel">—</span>
    <button class="btn-icon" onclick="closePanel()"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="cal-panel-body" id="panelBody">
    <div class="cal-panel-empty"><i class="bi bi-calendar3" style="font-size:28px;display:block;margin-bottom:8px;opacity:.25;"></i>Sin actividades este día</div>
  </div>
</div>

<?php
$tasks_json = json_encode(array_map(fn($t) => [
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
], $all_tasks), JSON_UNESCAPED_UNICODE);
?>
<?php include_once 'footer.php'; ?>
<script>
const ALL_TASKS = <?= $tasks_json ?>;
const tasksByDate = {};
ALL_TASKS.forEach(t => {
    if (!tasksByDate[t.fecha]) tasksByDate[t.fecha] = [];
    tasksByDate[t.fecha].push(t);
});

const SC = {
    pendiente:   { bg:'rgba(245,166,35,.15)',  dot:'#f5a623', text:'#f5a623', border:'rgba(245,166,35,.35)',  label:'Pendiente',   badge:'status-pending'   },
    en_progreso: { bg:'rgba(99,179,237,.15)',   dot:'#63b3ed', text:'#63b3ed', border:'rgba(99,179,237,.35)',  label:'En progreso', badge:'status-progress'  },
    completada:  { bg:'rgba(74,240,200,.12)',   dot:'#4af0c8', text:'#4af0c8', border:'rgba(74,240,200,.3)',   label:'Completada',  badge:'status-done'      },
    cancelada:   { bg:'rgba(252,92,125,.12)',  dot:'#fc5c7d', text:'#fc5c7d', border:'rgba(252,92,125,.3)',  label:'Cancelada',   badge:'status-cancelled' },
};
const EMOJI = {tarea:'📄',quiz:'📝',parcial:'📋',examen_final:'🎯',proyecto:'🗂',otro:'📌'};

function openPanel(dateStr, dateLabel) {
    const tasks = tasksByDate[dateStr] || [];
    const body  = document.getElementById('panelBody');
    document.getElementById('panelDateLabel').textContent = dateLabel;

    if (!tasks.length) {
        body.innerHTML = `<div class="cal-panel-empty">
            <i class="bi bi-sun" style="font-size:28px;display:block;margin-bottom:8px;opacity:.25;"></i>
            Sin actividades este día</div>`;
    } else {
        body.innerHTML = tasks.map(t => {
            const sc   = SC[t.estado] || SC.pendiente;
            const done = t.estado === 'completada' || t.estado === 'cancelada';
            return `
            <div class="cal-panel-task" style="border-left-color:${sc.dot};">
                <div class="cal-panel-task-top">
                    <div style="flex:1;min-width:0;">
                        <div class="cal-panel-task-name" style="${done?'text-decoration:line-through;opacity:.5;':''}">
                            ${EMOJI[t.tipo]||'📄'} ${escHtml(t.nombre)}
                        </div>
                        <div class="cal-panel-task-meta">
                            <span style="color:${t.mat_color};">● ${escHtml(t.materia)}</span>
                            <span>⏰ ${t.hora}</span>
                        </div>
                    </div>
                    <span class="status-badge ${sc.badge}" style="flex-shrink:0;">${sc.label}</span>
                </div>
                ${t.desc ? `<div class="cal-panel-task-desc">${escHtml(t.desc)}</div>` : ''}
                <div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="btn-ghost" style="font-size:11px;padding:4px 10px;"
                            onclick="cambiarEstadoPanel(${t.id},'completada',this)"
                            ${done ? 'disabled style="opacity:.35;cursor:not-allowed;"' : ''}>
                        ✅ Completada
                    </button>
                    <a class="btn-ghost" href="detalle_materia.php?id_materia=${t.id_materia}"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;">
                       <i class="bi bi-arrow-right-circle"></i> Ver materia
                    </a>
                    ${t.link ? `<a class="btn-ghost" href="${escHtml(t.link)}" target="_blank" rel="noopener noreferrer"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;color:var(--accent);border-color:var(--accent);">
                       <i class="bi bi-link-45deg"></i> Abrir enlace</a>` : ''}
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
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id_tarea=${id}&nuevo_estado=${encodeURIComponent(estado)}`
    }).then(r => r.json()).then(d => {
        if (d.success) { showToast('Estado actualizado.', 'success'); setTimeout(() => location.reload(), 500); }
        else Swal.fire('Error', d.message || 'No se pudo actualizar.', 'error');
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(String(str)));
    return d.innerHTML;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));
    }
});

// Auto-scroll week view to 7:00am on load
document.addEventListener('DOMContentLoaded', () => {
    const ws = document.querySelector('.week-scroll');
    if (ws) ws.scrollTop = 60 * 7; // 60px per hour × 7h
});
</script>
