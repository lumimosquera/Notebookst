<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        require_once "../model/conexion.php"; // Asegúrate de que la ruta al archivo de conexión sea correcta
        $id = $_POST['id'];
        
        try {
            // Iniciar una transacción
            $pdo->beginTransaction();
            
            // Preparar y ejecutar la consulta para eliminar las tareas asociadas a la materia
            $queryTareas = $pdo->prepare("DELETE FROM tareas WHERE id_materia = :id");
            $queryTareas->bindParam(':id', $id, PDO::PARAM_INT);
            $queryTareas->execute();
            
            // Preparar y ejecutar la consulta para eliminar la materia
            $queryMateria = $pdo->prepare("DELETE FROM materias WHERE id_materia = :id");
            $queryMateria->bindParam(':id', $id, PDO::PARAM_INT);
            $queryMateria->execute();
            
            // Confirmar la transacción
            $pdo->commit();
            
            // Redirigir a la página de materias después de eliminar
            header('Location: ../view/home.php');
            exit;
        } catch (Exception $e) {
            // En caso de error, revertir la transacción
            $pdo->rollBack();
            echo "Error al eliminar la materia y sus tareas: " . $e->getMessage();
        }
    } else {
        echo "ID de materia no proporcionado.";
    }
} else {
    echo "Solicitud no válida.";
}

?>
