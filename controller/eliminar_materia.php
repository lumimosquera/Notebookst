<?php
// Guard: session may already be started by the page that included encabezado.php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: ../view/home.php');
    exit;
}

require_once "../model/conexion.php";

$id_materia = intval($_POST['id']);
$id_usuario = intval($_SESSION['user_id']);

try {
    // Verify ownership before deleting
    $check = $pdo->prepare("SELECT id_materia FROM materias WHERE id_materia = :id AND id_usuario = :uid");
    $check->execute([':id' => $id_materia, ':uid' => $id_usuario]);

    if (!$check->fetch()) {
        header('Location: ../view/home.php');
        exit;
    }

    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM tareas   WHERE id_materia = :id")->execute([':id' => $id_materia]);
    $pdo->prepare("DELETE FROM materias WHERE id_materia = :id AND id_usuario = :uid")
        ->execute([':id' => $id_materia, ':uid' => $id_usuario]);
    $pdo->commit();

    header('Location: ../view/home.php?msg=' . urlencode('Materia eliminada.') . '&type=success');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header('Location: ../view/home.php?msg=' . urlencode('Error al eliminar la materia.') . '&type=danger');
    exit;
}
