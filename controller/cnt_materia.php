<?php

ob_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Redirige a una página de error o muestra un mensaje en la misma página
    $message = 'No estás autorizado para crear una materia.';
    $alert_type = 'alert-error';
    // Puedes redirigir a una página de error o volver al formulario
    header("Location: ../view/error.php?message=" . urlencode($message) . "&alert_type=" . urlencode($alert_type));
    exit();
}

// Verifica si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifica si 'nombre' está en $_POST
    if (isset($_POST['nombre'])) {
        $id_usuario = $_SESSION['user_id'];
        $nombre_materia = trim($_POST['nombre']); // Usa trim() para eliminar espacios en blanco

        function getRandomHexColor() {
            return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }
        
            // Función para generar un gradiente lineal aleatorio
            function getRandomGradient() {
                $color1 = getRandomHexColor();
                $color2 = getRandomHexColor();
                return "linear-gradient(45deg, $color1, $color2)";
            }
            // Generar color de gradiente
            $color = getRandomGradient();
                
        // Validar datos de entrada
        if (empty($nombre_materia)) {
            $message = "El nombre de la materia es requerido.";
            $alert_type = "alert-error";
        } else {
            try {
                $sql = "INSERT INTO materias (nombre_materia, id_usuario, color) VALUES (:nombre_materia, :id_usuario, :color)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':nombre_materia', $nombre_materia);
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':color', $color);
                $stmt->execute();

                $message = "Materia creada exitosamente";
                $alert_type = "alert-success";
            
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $alert_type = "alert-error";
            }
        }
    } else {
        $message = "El nombre de la materia no está definido.";
        $alert_type = "alert-error";
    }
    echo "<script type='text/javascript'>
    window.location.href = window.location.href;
  </script>";
exit();
}

ob_end_flush();
// Mostrar materias
$id_usuario = $_SESSION['user_id'];

try {
    // Prepara y ejecuta la consulta
    $sql = "SELECT id_materia, nombre_materia, color FROM materias WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    
    // Obtiene las materias
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error: " . $e->getMessage();
    echo "<div class='alert alert-danger'>$message</div>";
    exit();
}


// CONTEO DE TAREAS
$id_usuario = $_SESSION['user_id']; // Obtén el ID del usuario actual desde la sesión

// Consulta para obtener materias con el conteo de tareas asociadas, filtrando por id_usuario
$query = $pdo->prepare("
    SELECT m.*, COUNT(t.id_tarea) as tareas_count
    FROM materias m
    LEFT JOIN tareas t ON m.id_materia = t.id_materia
    WHERE m.id_usuario = :id_usuario
    GROUP BY m.id_materia
");
$query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$query->execute();
$materias = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Aquí puedes usar el color almacenado para cada materia -->