<?php



if (isset($_POST['id_tarea']) && isset($_POST['nuevo_estado'])) {
    $id_tarea = $_POST['id_tarea'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Conexión a la base de datos (PDO)
    require '.../model/conexion.php'; // Asegúrate de tener el archivo de conexión

    // Actualizar el estado de la tarea
    $stmt = $pdo->prepare("UPDATE tareas SET estado = :nuevo_estado WHERE id_tarea = :id_tarea");
    $stmt->bindParam(':nuevo_estado', $nuevo_estado);
    $stmt->bindParam(':id_tarea', $id_tarea);

    if ($stmt->execute()) {
        $response = array('success' => true, 'message' => 'Estado de la tarea actualizado exitosamente.');
    } else {
        $response = array('success' => false, 'message' => 'Error al actualizar el estado de la tarea.');
    }

    echo json_encode($response);
} else {
    $response = array('success' => false, 'message' => 'Parámetros incompletos.');
    echo json_encode($response);
}


?>
<h1>eeeee</h1>