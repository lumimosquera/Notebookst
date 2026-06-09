<?php
session_start();
include '../model/conexion.php';

// 1. Solo usuarios autenticados
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$id_tarea    = isset($_POST['id_tarea'])    ? intval($_POST['id_tarea'])    : 0;
$nuevo_estado = isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '';

// 2. Validar el estado contra la lista permitida (evita inyección en el ENUM)
$estados_validos = ['pendiente', 'en_progreso', 'completada', 'cancelada'];
if (!in_array($nuevo_estado, $estados_validos, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Estado inválido.']);
    exit;
}

if ($id_tarea <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de tarea inválido.']);
    exit;
}

// 3. Verificar que la tarea pertenezca al usuario (a través de la materia)
$check = $pdo->prepare("
    SELECT t.id_tarea
    FROM tareas t
    INNER JOIN materias m ON t.id_materia = m.id_materia
    WHERE t.id_tarea = :id_tarea AND m.id_usuario = :id_usuario
");
$check->execute([':id_tarea' => $id_tarea, ':id_usuario' => $_SESSION['user_id']]);

if (!$check->fetch()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para modificar esta tarea.']);
    exit;
}

// 4. Actualizar
$query = $pdo->prepare("UPDATE tareas SET estado = :estado WHERE id_tarea = :id_tarea");
$success = $query->execute([':estado' => $nuevo_estado, ':id_tarea' => $id_tarea]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado.']);
}
