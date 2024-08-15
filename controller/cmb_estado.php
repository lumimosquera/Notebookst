<?php
// Incluir el archivo de conexión
include '../model/conexion.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tarea = isset($_POST['id_tarea']) ? intval($_POST['id_tarea']) : 0;
    $nuevo_estado = isset($_POST['nuevo_estado']) ? $_POST['nuevo_estado'] : '';

    if ($id_tarea > 0 && !empty($nuevo_estado)) {
        $query = $pdo->prepare("UPDATE tareas SET estado = ? WHERE id_tarea = ?");
        $success = $query->execute([$nuevo_estado, $id_tarea]);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    }
}

?>