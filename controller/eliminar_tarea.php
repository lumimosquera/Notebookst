<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/home.php');
    exit;
}

require_once "../model/conexion.php";

$id_tarea   = intval($_POST['id_tarea']   ?? 0);
$id_materia = intval($_POST['id_materia'] ?? 0);
$id_usuario = intval($_SESSION['user_id']);

// Verificar ownership: la tarea debe pertenecer a una materia del usuario
$check = $pdo->prepare("
    SELECT t.id_tarea
    FROM tareas t
    INNER JOIN materias m ON t.id_materia = m.id_materia
    WHERE t.id_tarea = :id_tarea AND m.id_usuario = :uid
");
$check->execute([':id_tarea' => $id_tarea, ':uid' => $id_usuario]);

if (!$check->fetch()) {
    header('Location: ../view/home.php');
    exit;
}

$pdo->prepare("DELETE FROM tareas WHERE id_tarea = :id")->execute([':id' => $id_tarea]);

$redirect = "../view/detalle_materia.php?id_materia={$id_materia}&msg=" . urlencode("Actividad eliminada.");
header("Location: $redirect");
exit;
