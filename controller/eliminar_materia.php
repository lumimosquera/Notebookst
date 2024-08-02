<?php
session_start(); // Asegúrate de iniciar la sesión si aún no lo has hecho

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_materia'])) {
    $id_materia = $_POST['id_materia'];

    if (!empty($id_materia)) {
        require_once "../model/conexion.php"; // Asegúrate de que este archivo configure la conexión PDO en $pdo

        try {
            // Preparar la declaración SQL para eliminar de manera segura
            $stmt = $pdo->prepare("DELETE FROM materias WHERE id_materia = :id_materia");
            
            // Vincular parámetros
            $stmt->bindParam(':id_materia', $id_materia, PDO::PARAM_INT);

            // Ejecutar la declaración
            if ($stmt->execute()) {
                // Redirigir a la página principal después de eliminar
                header('Location: ../view/home.php');
                exit(); // Asegúrate de que no se ejecute más código después de la redirección
            } else {
                // Manejar error en la ejecución
                echo "Error al ejecutar la consulta: " . $stmt->errorInfo()[2];
            }

        } catch (PDOException $e) {
            // Manejar error en la preparación de la declaración
            echo "Error: " . $e->getMessage();
        }

        // Cerrar la conexión
        $pdo = null;
    } else {
        // Manejar casos donde el parámetro está vacío
        echo "El ID de la materia no puede estar vacío.";
    }
} else {
    // Manejar casos donde el método no es POST o el parámetro no está establecido
    echo "Solicitud no válida.";
}
?>
