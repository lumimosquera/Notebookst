<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['id'])) {
        require_once "../model/conexion.php"; // Asegúrate de que la ruta al archivo de conexión sea correcta
        $id = $_POST['id'];
        
        // Preparar y ejecutar la consulta para eliminar la materia
        $query = $pdo->prepare("DELETE FROM materias WHERE id_materia = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            header('Location: ../view/home.php'); // Redirige a la página de materias después de eliminar
            exit;
        } else {
            echo "Error al eliminar la materia.";
        }
    } else {
        echo "ID de materia no proporcionado.";
    }
} else {
    echo "Solicitud no válida.";
}

?>
