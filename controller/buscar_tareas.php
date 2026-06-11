<?php
// Capturar cualquier output accidental (warnings, notices) para no romper el JSON
ob_start();

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(403);
    echo json_encode([]);
    exit;
}

if (!isset($pdo)) require_once '../model/conexion.php';

$id_usuario = intval($_SESSION['user_id']);
$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    ob_end_clean();
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.id_tarea, t.id_materia, t.nombre_tarea, t.tipo_tarea,
               t.estado, t.fecha_cierre,
               m.nombre_materia, m.color AS mat_color
        FROM tareas t
        INNER JOIN materias m ON t.id_materia = m.id_materia
        WHERE m.id_usuario = ?
          AND (t.nombre_tarea LIKE ? OR t.descripcion_tarea LIKE ? OR m.nombre_materia LIKE ?)
        ORDER BY t.fecha_cierre ASC
        LIMIT 12
    ");
    $like = '%' . $q . '%';
    $stmt->execute([$id_usuario, $like, $like, $like]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = array_map(fn($r) => [
        'id_tarea'   => $r['id_tarea'],
        'id_materia' => $r['id_materia'],
        'nombre'     => $r['nombre_tarea'],
        'tipo'       => $r['tipo_tarea']  ?? 'tarea',
        'estado'     => $r['estado'],
        'fecha'      => date('d/m/Y', strtotime($r['fecha_cierre'])),
        'materia'    => $r['nombre_materia'],
        'mat_color'  => $r['mat_color']   ?? '#4af0c8',
    ], $rows);

    ob_end_clean();
    echo json_encode($out, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
