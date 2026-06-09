<?php
$id_usuario = $_SESSION['user_id'];

$filter                  = in_array($_GET['filter'] ?? 'todos', ['todos','pendiente','en_progreso','completada','cancelada']) ? ($_GET['filter'] ?? 'todos') : 'todos';
$id_materia_seleccionada = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : null;
$busqueda                = trim($_GET['busqueda'] ?? '');
$busqueda_mostrada       = htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8');

// Get all materias for the dropdown
$stmtMat = $pdo->prepare("SELECT id_materia, nombre_materia, color FROM materias WHERE id_usuario = ? ORDER BY nombre_materia ASC");
$stmtMat->execute([$id_usuario]);
$materias = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

// Build main query
$sql = "SELECT t.id_tarea, t.id_materia, t.nombre_tarea, t.descripcion_tarea,
               t.fecha_creacion, t.fecha_cierre, t.hora_cierre, t.tipo_tarea,
               t.estado, t.color, m.nombre_materia
        FROM tareas t
        INNER JOIN materias m ON t.id_materia = m.id_materia
        WHERE m.id_usuario = :uid";
$params = [':uid' => $id_usuario];

if ($filter !== 'todos') {
    $sql .= " AND t.estado = :estado";
    $params[':estado'] = $filter;
}
if ($id_materia_seleccionada) {
    $sql .= " AND m.id_materia = :mat";
    $params[':mat'] = $id_materia_seleccionada;
}
if (!empty($busqueda)) {
    $sql .= " AND (t.nombre_tarea LIKE :bus OR m.nombre_materia LIKE :bus)";
    $params[':bus'] = "%{$busqueda}%";
}
$sql .= " ORDER BY t.fecha_cierre ASC, t.hora_cierre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tareas_filtradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
