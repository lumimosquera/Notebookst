<?php
ob_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/error.php?message=" . urlencode('No estás autorizado.'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $id_usuario     = $_SESSION['user_id'];
    $nombre_materia = trim($_POST['nombre']);
    $link_materia   = isset($_POST['link']) ? trim($_POST['link']) : '';

    // Generate a random solid color from a curated palette
    $palette = ['#4af0c8','#63b3ed','#f5a623','#fc5c7d','#a78bfa','#34d399','#f59e0b','#60a5fa','#e879f9','#f87171'];
    $color   = $palette[array_rand($palette)];

    if (empty($nombre_materia)) {
        $message    = "El nombre de la materia es requerido.";
        $alert_type = "alert-error";
    } elseif (!empty($link_materia) && !filter_var($link_materia, FILTER_VALIDATE_URL)) {
        $message    = "El enlace debe ser una URL válida.";
        $alert_type = "alert-error";
    } else {
        try {
            $link_value = !empty($link_materia) ? $link_materia : null;

            // Check whether the 'link' column exists to avoid SQL errors on old schemas
            $hasLink = false;
            try {
                $colCheck = $pdo->query("SHOW COLUMNS FROM materias LIKE 'link'");
                $hasLink  = ($colCheck->rowCount() > 0);
            } catch (Exception $e) {}

            if ($hasLink) {
                $sql  = "INSERT INTO materias (nombre_materia, id_usuario, color, link) VALUES (?, ?, ?, ?)";
                $args = [$nombre_materia, $id_usuario, $color, $link_value];
            } else {
                $sql  = "INSERT INTO materias (nombre_materia, id_usuario, color) VALUES (?, ?, ?)";
                $args = [$nombre_materia, $id_usuario, $color];
            }

            $pdo->prepare($sql)->execute($args);
            $message    = "Materia creada exitosamente";
            $alert_type = "alert-success";

        } catch (PDOException $e) {
            $message    = "Error: " . $e->getMessage();
            $alert_type = "alert-error";
        }
    }

    ob_end_clean();
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../view/home.php'));
    exit();
}

ob_end_clean();

// Load materias with task counts for display
$id_usuario = $_SESSION['user_id'];

try {
    $query = $pdo->prepare("
        SELECT m.*, COUNT(t.id_tarea) AS tareas_count
        FROM materias m
        LEFT JOIN tareas t ON m.id_materia = t.id_materia
        WHERE m.id_usuario = :id_usuario
        GROUP BY m.id_materia
        ORDER BY m.id_materia DESC
    ");
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query->execute();
    $materias = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $materias = [];
    $message  = "Error al cargar materias: " . $e->getMessage();
}
