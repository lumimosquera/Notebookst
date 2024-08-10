<?php
// Obtener el id_materia desde la URL de la materia donde estamos
$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['nombre_tarea']) && !empty($_POST['descripcion_tarea']) && !empty($_POST['fecha_cierre'])) {
        $nombre_tarea = trim($_POST['nombre_tarea']);
        $descripcion_tarea = trim($_POST['descripcion_tarea']);
        $fecha_cierre = $_POST['fecha_cierre'];
        $color = isset($_POST['color']) ? $_POST['color'] : '#000000'; // Valor por defecto negro si no se elige un color
        $estado = 'pendiente';
        $fecha_creacion = date('Y-m-d H:i:s');

        try {
            $sql = "INSERT INTO tareas (id_materia, nombre_tarea, descripcion_tarea, fecha_creacion, fecha_cierre, color, estado) 
                    VALUES (:id_materia, :nombre_tarea, :descripcion_tarea, :fecha_creacion, :fecha_cierre, :color, :estado)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_materia', $id_materia, PDO::PARAM_INT);
            $stmt->bindParam(':nombre_tarea', $nombre_tarea, PDO::PARAM_STR);
            $stmt->bindParam(':descripcion_tarea', $descripcion_tarea, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_creacion', $fecha_creacion, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_cierre', $fecha_cierre, PDO::PARAM_STR);
            $stmt->bindParam(':color', $color, PDO::PARAM_STR);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $message = "Tarea creada exitosamente.";
                $alert_type = "alert-success";
            } else {
                $message = "Error al guardar la tarea.";
                $alert_type = "alert-error";
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $alert_type = "alert-error";
        }
    } else {
        $message = "Todos los campos son obligatorios.";
        $alert_type = "alert-error";
    }

    echo "<script type='text/javascript'>
    window.location.href = window.location.href;
  </script>";
exit();
}





?>




