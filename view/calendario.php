<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
if (!isset($pdo)) require_once '../model/conexion.php';

$id_usuario = intval($_SESSION['user_id']);

// ── Filters (same as filtros.php logic, inline for independence) ──
$filter                  = in_array($_GET['filter'] ?? 'todos', ['todos','pendiente','en_progreso','completada','cancelada'])
                           ? ($_GET['filter'] ?? 'todos') : 'todos';
$id_materia_seleccionada = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : null;
$busqueda                = trim($_GET['busqueda'] ?? '');
$busqueda_mostrada       = htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8');

// Active month/year (default: current Colombia time = UTC-5)
$now_col   = new DateTime('now', new DateTimeZone('America/Bogota'));
$view_year  = isset($_GET['y']) ? intval($_GET['y']) : (int)$now_col->format('Y');
$view_month = isset($_GET['m']) ? intval($_GET['m']) : (int)$now_col->format('n');
// Clamp month
if ($view_month < 1)  { $view_month = 12; $view_year--; }
if ($view_month > 12) { $view_month = 1;  $view_year++; }

// Materias for filter dropdown
$stmtMat = $pdo->prepare("SELECT id_materia, nombre_materia, color FROM materias WHERE id_usuario = ? ORDER BY nombre_materia ASC");
$stmtMat->execute([$id_usuario]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

// Load ALL tasks for calendar (broader range: prev month + current + next for overflow)
$sql = "SELECT t.id_tarea, t.id_materia, t.nombre_tarea, t.descripcion_tarea,
               t.fecha_creacion, t.fecha_cierre, t.hora_cierre, t.tipo_tarea,
               t.estado, t.color, t.link, m.nombre_materia, m.color AS materia_color
        FROM tareas t
        INNER JOIN materias m ON t.id_materia = m.id_materia
        WHERE m.id_usuario = :uid";
$params = [':uid' => $id_usuario];

if ($filter !== 'todos') { $sql .= " AND t.estado = :estado"; $params[':estado'] = $filter; }
if ($id_materia_seleccionada) { $sql .= " AND m.id_materia = :mat"; $params[':mat'] = $id_materia_seleccionada; }
if (!empty($busqueda)) { $sql .= " AND (t.nombre_tarea LIKE :bus OR m.nombre_materia LIKE :bus)"; $params[':bus'] = "%{$busqueda}%"; }
$sql .= " ORDER BY t.hora_cierre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$all_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Index tasks by date string YYYY-MM-DD
$tasks_by_date = [];
foreach ($all_tasks as $t) {
    $key = $t['fecha_cierre'];
    $tasks_by_date[$key][] = $t;
}

// Calendar math
$first_day  = new DateTime("{$view_year}-{$view_month}-01");
$days_month = (int)$first_day->format('t');
$start_dow  = (int)$first_day->format('N'); // 1=Mon … 7=Sun
// We want week starting Monday; convert to 0-based offset
$start_offset = $start_dow - 1; // Mon=0 … Sun=6

// Month labels ES
$MONTHS_ES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$DAYS_ES   = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

// Prev / Next month nav
$prev_m = $view_month - 1; $prev_y = $view_year;
if ($prev_m < 1)  { $prev_m = 12; $prev_y--; }
$next_m = $view_month + 1; $next_y = $view_year;
if ($next_m > 12) { $next_m = 1;  $next_y++; }

// Helpers
function calStatusClass($s) {
    return match($s) { 'pendiente'=>'status-pending','en_progreso'=>'status-progress','completada'=>'status-done','cancelada'=>'status-cancelled',default=>'' };
}
function calTipoEmoji($t) {
    return match($t) { 'quiz'=>'📝','parcial'=>'📋','examen_final'=>'🎯','proyecto'=>'🗂','otro'=>'📌',default=>'📄' };
}

include_once 'encabezado.php';
?>

<style>
/* ── CALENDAR LAYOUT ──────────────────────────────────────────── */
.cal-wrap       { display: flex; flex-direction: column; gap: 0; }
.cal-header     { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.cal-month-nav  { display: flex; align-items: center; gap: 8px; }
.cal-month-label {
  font-family: var(--font-display);
  font-size: 22px;
  color: var(--text-primary);
  min-width: 220px;
  text-align: center;
}
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  border-top: 1px solid var(--border);
  border-left: 1px solid var(--border);
}
.cal-day-header {
  font-family: var(--font-mono);
  font-size: 10px;
  font-weight: 500;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--text-tertiary);
  text-align: center;
  padding: 8px 4px;
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  background: var(--bg-surface);
}
.cal-day-header.weekend { color: var(--status-cancelled); opacity: .7; }
.cal-cell {
  border-right: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  min-height: 110px;
  padding: 6px;
  background: var(--bg-base);
  position: relative;
  vertical-align: top;
  transition: background var(--t-fast);
}
.cal-cell:hover { background: var(--bg-hover); }
.cal-cell.other-month { opacity: .35; pointer-events: none; }
.cal-cell.today { background: var(--accent-dim); }
.cal-cell.today .cal-day-num { color: var(--accent); font-weight: 700; }
.cal-cell.has-overdue { border-top: 2px solid var(--status-cancelled); }

.cal-day-num {
  font-family: var(--font-mono);
  font-size: 12px;
  color: var(--text-tertiary);
  margin-bottom: 4px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
}
.cal-day-num.today-circle {
  background: var(--accent);
  color: var(--text-inverse);
  font-weight: 700;
}

/* Task chip inside calendar cell */
.cal-chip {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 3px 6px;
  border-radius: 4px;
  font-size: 11px;
  line-height: 1.3;
  margin-bottom: 3px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: filter var(--t-fast), transform var(--t-fast);
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  max-width: 100%;
  position: relative;
}
.cal-chip:hover { filter: brightness(1.15); transform: translateY(-1px); }
.cal-chip.done  { opacity: .45; text-decoration: line-through; }
.cal-chip-dot   { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.cal-chip-text  { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; font-weight: 500; }
.cal-chip-hour  { font-family: var(--font-mono); font-size: 9px; opacity: .7; flex-shrink: 0; }

/* +N more badge */
.cal-more {
  font-family: var(--font-mono);
  font-size: 10px;
  color: var(--text-tertiary);
  padding: 2px 4px;
  cursor: pointer;
  border-radius: 3px;
}
.cal-more:hover { background: var(--bg-overlay); color: var(--text-secondary); }

/* ── DETAIL PANEL (slide-in from right) ──────────────────────── */
.cal-panel-backdrop {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,.4);
  z-index: 10000;
  backdrop-filter: blur(2px);
}
.cal-panel-backdrop.open { display: block; }
.cal-panel {
  position: fixed;
  top: 0; right: 0;
  width: min(420px, 100vw);
  height: 100vh;
  background: var(--bg-surface);
  border-left: 1px solid var(--border);
  box-shadow: -12px 0 48px rgba(0,0,0,.4);
  z-index: 10001;
  transform: translateX(100%);
  transition: transform .25s cubic-bezier(.4,0,.2,1);
  display: flex; flex-direction: column;
  overflow: hidden;
}
.cal-panel.open { transform: translateX(0); }
.cal-panel-header {
  padding: 18px 20px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
}
.cal-panel-date {
  font-family: var(--font-display);
  font-size: 18px;
  color: var(--text-primary);
}
.cal-panel-body { flex: 1; overflow-y: auto; padding: 16px 20px; }
.cal-panel-task {
  background: var(--bg-elevated);
  border: 1px solid var(--border);
  border-radius: var(--r-md);
  padding: 12px 14px;
  margin-bottom: 10px;
  cursor: default;
  transition: border-color var(--t-fast);
}
.cal-panel-task:hover { border-color: rgba(255,255,255,.14); }
.cal-panel-task-top {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;
  margin-bottom: 6px;
}
.cal-panel-task-name { font-size: 13px; font-weight: 500; color: var(--text-primary); line-height: 1.35; }
.cal-panel-task-meta { font-family: var(--font-mono); font-size: 10px; color: var(--text-tertiary); display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
.cal-panel-task-desc { font-size: 12px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5; }
.cal-panel-empty { text-align: center; padding: 40px 0; color: var(--text-tertiary); font-size: 13px; }

/* ── TODAY BUTTON ─────────────────────────────────────────────── */
.btn-today {
  font-family: var(--font-mono);
  font-size: 11px;
  padding: 5px 12px;
  border-radius: var(--r-md);
  border: 1px solid var(--border);
  background: var(--bg-elevated);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all var(--t-fast);
}
.btn-today:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-dim); }

/* ── MINI LEGEND ─────────────────────────────────────────────── */
.cal-legend { display: flex; gap: 14px; flex-wrap: wrap; align-items: center; }
.cal-legend-item { display: flex; align-items: center; gap: 5px; font-family: var(--font-mono); font-size: 10px; color: var(--text-secondary); }
.cal-legend-dot  { width: 8px; height: 8px; border-radius: 50%; }

/* Responsive */
@media (max-width: 768px) {
  .cal-cell { min-height: 60px; padding: 4px; }
  .cal-chip-text { max-width: 50px; }
  .cal-chip-hour { display: none; }
  .cal-month-label { font-size: 17px; min-width: 150px; }
}
@media (max-width: 480px) {
  .cal-cell { min-height: 44px; padding: 2px; }
  .cal-day-header { font-size: 9px; padding: 5px 2px; }
}
</style>

<div class="page-content">

  <!-- Page header -->
  <div class="page-header" style="margin-bottom:20px;">
    <div class="page-header-row">
      <div>
        <div class="page-header-eyebrow">Semestre activo</div>
        <h1 class="page-header-title">Calendario</h1>
        <p class="page-header-sub">Visualiza tus entregas por fecha.</p>
      </div>
      <a href="tareas.php" class="btn-ghost" style="text-decoration:none;">
        <i class="bi bi-list-check"></i> Vista lista
      </a>
    </div>
  </div>

  <!-- Filter bar -->
  <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:16px;">
    <?php foreach(['todos'=>'Todas','pendiente'=>'Pendiente','en_progreso'=>'En progreso','completada'=>'Completada'] as $k=>$lbl): ?>
    <a href="?filter=<?= $k ?>&id_materia=<?= $id_materia_seleccionada ?>&m=<?= $view_month ?>&y=<?= $view_year ?>"
       style="font-size:12px;padding:4px 12px;border-radius:20px;border:1px solid var(--border);
              font-family:var(--font-mono);text-decoration:none;transition:.15s;
              background:<?= $filter===$k?'var(--accent-dim)':'transparent' ?>;
              color:<?= $filter===$k?'var(--accent)':'var(--text-secondary)' ?>;
              border-color:<?= $filter===$k?'var(--accent)':'var(--border)' ?>;">
      <?= $lbl ?>
    </a>
    <?php endforeach; ?>

    <!-- Materia filter -->
    <div class="relative" style="margin-left:auto;">
      <button class="btn-ghost" onclick="document.getElementById('calMtrDrop').classList.toggle('open')">
        <i class="bi bi-journal-bookmark"></i>
        <?php
          if ($id_materia_seleccionada) {
            $nm = array_column($materias,'nombre_materia','id_materia')[$id_materia_seleccionada] ?? 'Materia';
            echo htmlspecialchars(strlen($nm)>20 ? substr($nm,0,18).'…' : $nm);
          } else { echo 'Todas las materias'; }
        ?>
        <i class="bi bi-chevron-down" style="font-size:10px;"></i>
      </button>
      <div class="ctx-menu" id="calMtrDrop" style="min-width:210px;">
        <a class="ctx-item" href="?filter=<?= $filter ?>&m=<?= $view_month ?>&y=<?= $view_year ?>">Todas las materias</a>
        <div class="ctx-sep"></div>
        <?php foreach ($materias as $m): ?>
        <a class="ctx-item" href="?filter=<?= $filter ?>&id_materia=<?= $m['id_materia'] ?>&m=<?= $view_month ?>&y=<?= $view_year ?>">
          <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($m['color']??'#4af0c8') ?>;margin-right:5px;"></span>
          <?= htmlspecialchars($m['nombre_materia']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Calendar nav + legend -->
  <div class="cal-header">
    <div class="cal-month-nav">
      <a href="?filter=<?= $filter ?>&id_materia=<?= $id_materia_seleccionada ?>&m=<?= $prev_m ?>&y=<?= $prev_y ?>" class="btn-icon" title="Mes anterior">
        <i class="bi bi-chevron-left"></i>
      </a>
      <div class="cal-month-label"><?= $MONTHS_ES[$view_month] ?> <?= $view_year ?></div>
      <a href="?filter=<?= $filter ?>&id_materia=<?= $id_materia_seleccionada ?>&m=<?= $next_m ?>&y=<?= $next_y ?>" class="btn-icon" title="Mes siguiente">
        <i class="bi bi-chevron-right"></i>
      </a>
      <?php
        $today_m = (int)$now_col->format('n');
        $today_y = (int)$now_col->format('Y');
        if ($view_month !== $today_m || $view_year !== $today_y):
      ?>
      <a href="?filter=<?= $filter ?>&id_materia=<?= $id_materia_seleccionada ?>&m=<?= $today_m ?>&y=<?= $today_y ?>" class="btn-today">Hoy</a>
      <?php endif; ?>
    </div>
    <div class="cal-legend">
      <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-pending)"></div>Pendiente</div>
      <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-progress)"></div>En progreso</div>
      <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-done)"></div>Completada</div>
      <div class="cal-legend-item"><div class="cal-legend-dot" style="background:var(--status-cancelled)"></div>Cancelada</div>
    </div>
  </div>

  <!-- Calendar grid -->
  <div class="cal-grid">

    <!-- Day headers -->
    <?php foreach ($DAYS_ES as $i => $d): ?>
    <div class="cal-day-header <?= $i >= 5 ? 'weekend' : '' ?>"><?= $d ?></div>
    <?php endforeach; ?>

    <?php
    $today_str = $now_col->format('Y-m-d');
    $today_day = (int)$now_col->format('j');

    // Cells before the 1st of the month (previous month overflow)
    $prev_month_last = new DateTime("{$prev_y}-{$prev_m}-01");
    $prev_days_total = (int)(new DateTime("{$view_year}-{$view_month}-01"))->modify('-1 day')->format('j');

    for ($i = 0; $i < $start_offset; $i++):
        $d = $prev_days_total - $start_offset + $i + 1;
        $date_str = sprintf('%04d-%02d-%02d', $prev_y, $prev_m, $d);
        $day_tasks = $tasks_by_date[$date_str] ?? [];
    ?>
    <div class="cal-cell other-month">
      <span class="cal-day-num"><?= $d ?></span>
    </div>
    <?php endfor; ?>

    <?php
    // Current month cells
    for ($day = 1; $day <= $days_month; $day++):
      $date_str  = sprintf('%04d-%02d-%02d', $view_year, $view_month, $day);
      $day_tasks = $tasks_by_date[$date_str] ?? [];
      $is_today  = ($date_str === $today_str);
      $is_past   = ($date_str < $today_str);
      $has_overdue = $is_past && !empty(array_filter($day_tasks, fn($t) => !in_array($t['estado'], ['completada','cancelada'])));

      // Day of week (for weekend styling)
      $dow = (int)(new DateTime($date_str))->format('N'); // 6=Sat,7=Sun
      $is_weekend = $dow >= 6;

      $DAYS_FULL_ES = ['','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
      $date_label   = $DAYS_FULL_ES[$dow] . ', ' . $day . ' de ' . $MONTHS_ES[$view_month] . ' ' . $view_year;

      // Task color by status
      $status_colors = [
        'pendiente'   => ['bg' => 'rgba(245,166,35,.18)',  'dot' => '#f5a623', 'text' => '#f5a623'],
        'en_progreso' => ['bg' => 'rgba(99,179,237,.18)',  'dot' => '#63b3ed', 'text' => '#63b3ed'],
        'completada'  => ['bg' => 'rgba(74,240,200,.15)',  'dot' => '#4af0c8', 'text' => '#4af0c8'],
        'cancelada'   => ['bg' => 'rgba(252,92,125,.15)', 'dot' => '#fc5c7d', 'text' => '#fc5c7d'],
      ];

      $MAX_CHIPS = 3; // max visible chips before "+N more"
    ?>
    <div class="cal-cell <?= $is_today ? 'today' : '' ?> <?= $has_overdue ? 'has-overdue' : '' ?>"
         style="<?= $is_weekend ? 'background:var(--bg-surface);' : '' ?>"
         onclick="openPanel('<?= $date_str ?>', '<?= htmlspecialchars($date_label, ENT_QUOTES) ?>')"
         title="<?= $date_label ?>">

      <span class="cal-day-num <?= $is_today ? 'today-circle' : '' ?>"><?= $day ?></span>

      <?php
        $shown = 0;
        foreach ($day_tasks as $t):
          if ($shown >= $MAX_CHIPS) break;
          $sc   = $status_colors[$t['estado']] ?? $status_colors['pendiente'];
          $done = in_array($t['estado'], ['completada','cancelada']);
          $hora = substr($t['hora_cierre'] ?? '23:59:00', 0, 5);
          $shown++;
      ?>
      <div class="cal-chip <?= $done ? 'done' : '' ?>"
           style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;"
           onclick="event.stopPropagation(); openPanel('<?= $date_str ?>', '<?= htmlspecialchars($date_label, ENT_QUOTES) ?>')"
           title="<?= htmlspecialchars($t['nombre_tarea']) ?> — <?= htmlspecialchars($t['nombre_materia']) ?>">
        <div class="cal-chip-dot" style="background:<?= $sc['dot'] ?>;"></div>
        <span class="cal-chip-text"><?= htmlspecialchars($t['nombre_tarea']) ?></span>
        <span class="cal-chip-hour"><?= $hora ?></span>
      </div>
      <?php endforeach; ?>

      <?php if (count($day_tasks) > $MAX_CHIPS): ?>
      <div class="cal-more" onclick="event.stopPropagation(); openPanel('<?= $date_str ?>', '<?= htmlspecialchars($date_label, ENT_QUOTES) ?>')">
        +<?= count($day_tasks) - $MAX_CHIPS ?> más
      </div>
      <?php endif; ?>

    </div>
    <?php endfor; ?>

    <?php
    // Trailing cells to complete the last row
    $total_cells = $start_offset + $days_month;
    $trailing    = (7 - ($total_cells % 7)) % 7;
    for ($i = 1; $i <= $trailing; $i++):
      $date_str = sprintf('%04d-%02d-%02d', $next_y, $next_m, $i);
    ?>
    <div class="cal-cell other-month">
      <span class="cal-day-num"><?= $i ?></span>
    </div>
    <?php endfor; ?>

  </div><!-- .cal-grid -->

</div><!-- .page-content -->

<!-- ── DETAIL PANEL ──────────────────────────────────────────── -->
<div class="cal-panel-backdrop" id="panelBackdrop" onclick="closePanel()"></div>
<div class="cal-panel" id="calPanel">
  <div class="cal-panel-header">
    <span class="cal-panel-date" id="panelDateLabel">—</span>
    <button class="btn-icon" onclick="closePanel()"><i class="bi bi-x-lg"></i></button>
  </div>
  <div class="cal-panel-body" id="panelBody">
    <div class="cal-panel-empty"><i class="bi bi-calendar3" style="font-size:28px;display:block;margin-bottom:8px;"></i>Sin actividades este día</div>
  </div>
</div>

<?php
// Serialize tasks to JSON for the JS panel renderer
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
// ── Tasks data (from PHP) ──────────────────────────────────────
const ALL_TASKS = <?= $tasks_json ?>;

// Index by date
const tasksByDate = {};
ALL_TASKS.forEach(t => {
    if (!tasksByDate[t.fecha]) tasksByDate[t.fecha] = [];
    tasksByDate[t.fecha].push(t);
});

// ── Panel ──────────────────────────────────────────────────────
const STATUS_COLORS = {
    pendiente:   { bg: 'rgba(245,166,35,.18)',  dot: '#f5a623', text: '#f5a623', label: 'Pendiente'   },
    en_progreso: { bg: 'rgba(99,179,237,.18)',  dot: '#63b3ed', text: '#63b3ed', label: 'En progreso' },
    completada:  { bg: 'rgba(74,240,200,.15)',  dot: '#4af0c8', text: '#4af0c8', label: 'Completada'  },
    cancelada:   { bg: 'rgba(252,92,125,.15)', dot: '#fc5c7d', text: '#fc5c7d', label: 'Cancelada'   },
};
const TIPO_EMOJI = { tarea:'📄', quiz:'📝', parcial:'📋', examen_final:'🎯', proyecto:'🗂', otro:'📌' };

function openPanel(dateStr, dateLabel) {
    const tasks = tasksByDate[dateStr] || [];
    const body  = document.getElementById('panelBody');
    document.getElementById('panelDateLabel').textContent = dateLabel;

    if (!tasks.length) {
        body.innerHTML = `<div class="cal-panel-empty">
            <i class="bi bi-calendar-check" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4;"></i>
            Sin actividades este día
        </div>`;
    } else {
        body.innerHTML = tasks.map(t => {
            const sc    = STATUS_COLORS[t.estado] || STATUS_COLORS.pendiente;
            const emoji = TIPO_EMOJI[t.tipo] || '📄';
            const done  = t.estado === 'completada' || t.estado === 'cancelada';
            return `
            <div class="cal-panel-task" style="border-left: 3px solid ${sc.dot};">
                <div class="cal-panel-task-top">
                    <div>
                        <div class="cal-panel-task-name" style="${done ? 'text-decoration:line-through;opacity:.6;' : ''}">
                            ${emoji} ${escHtml(t.nombre)}
                        </div>
                        <div class="cal-panel-task-meta">
                            <span style="color:${t.mat_color};">● ${escHtml(t.materia)}</span>
                            <span>⏰ ${t.hora}</span>
                        </div>
                    </div>
                    <span class="status-badge ${t.estado === 'pendiente' ? 'status-pending' :
                                                 t.estado === 'en_progreso' ? 'status-progress' :
                                                 t.estado === 'completada' ? 'status-done' : 'status-cancelled'}">
                        ${sc.label}
                    </span>
                </div>
                ${t.desc ? `<div class="cal-panel-task-desc">${escHtml(t.desc)}</div>` : ''}
                <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                    <button class="btn-ghost" style="font-size:11px;padding:4px 10px;"
                            onclick="cambiarEstadoPanel(${t.id}, 'completada', this)"
                            ${done ? 'disabled style="opacity:.4;cursor:not-allowed;"' : ''}>
                        ✅ Completada
                    </button>
                    <a class="btn-ghost" href="detalle_materia.php?id_materia=${t.id_materia}"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;">
                        <i class="bi bi-arrow-right-circle"></i> Ver materia
                    </a>
                    ${t.link ? `<a class="btn-ghost" href="${escHtml(t.link)}" target="_blank" rel="noopener noreferrer"
                       style="font-size:11px;padding:4px 10px;text-decoration:none;color:var(--accent);border-color:var(--accent);">
                        <i class="bi bi-link-45deg"></i> Abrir enlace
                    </a>` : ''}
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
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_tarea=${id}&nuevo_estado=${encodeURIComponent(estado)}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showToast('Estado actualizado.', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            Swal.fire('Error', d.message || 'No se pudo actualizar.', 'error');
        }
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// Close panel on Escape
document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });

// Close subject filter dropdown
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('.ctx-menu.open').forEach(x => x.classList.remove('open'));
    }
});
</script>