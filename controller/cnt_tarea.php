<?php
// Sesión ya iniciada por encabezado.php cuando se incluye desde la vista
if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit;
}

$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : 0;

if ($id_materia <= 0) {
    die('Materia no válida.');
}

// Verificar que la materia pertenece al usuario antes de procesar el POST
$checkMateria = $pdo->prepare("SELECT id_materia FROM materias WHERE id_materia = :id AND id_usuario = :uid");
$checkMateria->execute([':id' => $id_materia, ':uid' => $_SESSION['user_id']]);
if (!$checkMateria->fetch()) {
    header('Location: home.php');
    exit;
}

// Tipos de tarea permitidos
$tipos_validos = ['tarea', 'quiz', 'parcial', 'examen_final', 'proyecto', 'otro'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_tarea     = trim($_POST['nombre_tarea']     ?? '');
    $descripcion_tarea = trim($_POST['descripcion_tarea'] ?? '');
    $fecha_cierre     = $_POST['fecha_cierre']     ?? '';
    $hora_cierre      = $_POST['hora_cierre']      ?? '23:59';
    $tipo_tarea       = $_POST['tipo_tarea']       ?? 'tarea';
    $color            = $_POST['color']            ?? '#4a90d9';
    $link_tarea       = isset($_POST['link_tarea']) ? trim($_POST['link_tarea']) : '';

    // Validar tipo
    if (!in_array($tipo_tarea, $tipos_validos, true)) {
        $tipo_tarea = 'tarea';
    }

    // Sanitizar color (solo hex válido)
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#4a90d9';
    }

    if (!empty($nombre_tarea) && !empty($descripcion_tarea) && !empty($fecha_cierre)) {
        // Validar URL si se proporciona
        if (!empty($link_tarea) && !filter_var($link_tarea, FILTER_VALIDATE_URL)) {
            $message    = "El enlace debe ser una URL válida.";
            $alert_type = "alert-danger";
        } else {
            $datetime_cierre = $fecha_cierre . ' ' . $hora_cierre . ':00';
            $estado          = 'pendiente';
            $fecha_creacion  = date('Y-m-d H:i:s');
            $link_tarea      = !empty($link_tarea) ? $link_tarea : null;

            try {
                $sql = "INSERT INTO tareas (id_materia, nombre_tarea, descripcion_tarea, fecha_creacion, fecha_cierre, hora_cierre, tipo_tarea, color, estado, link)
                        VALUES (:id_materia, :nombre_tarea, :descripcion_tarea, :fecha_creacion, :fecha_cierre, :hora_cierre, :tipo_tarea, :color, :estado, :link)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_materia'       => $id_materia,
                    ':nombre_tarea'     => $nombre_tarea,
                    ':descripcion_tarea'=> $descripcion_tarea,
                    ':fecha_creacion'   => $fecha_creacion,
                    ':fecha_cierre'     => $fecha_cierre,
                    ':hora_cierre'      => $hora_cierre . ':00',
                    ':tipo_tarea'       => $tipo_tarea,
                    ':color'            => $color,
                    ':estado'           => $estado,
                    ':link'             => $link_tarea,
                ]);
                $message    = "Tarea creada exitosamente.";
                $alert_type = "alert-success";
            } catch (PDOException $e) {
                $message    = "Error al guardar la tarea.";
                $alert_type = "alert-danger";
            }
        }

    // Redirigir para evitar reenvío del formulario (PRG pattern)
    // Apuntar a la vista para mantener rutas relativas correctas
    $redirect_url = "../view/detalle_materia.php?id_materia=" . $id_materia;
    if (isset($message)) {
        $redirect_url .= "&msg=" . urlencode($message) . "&type=" . urlencode($alert_type);
    }
    header("Location: " . $redirect_url);
    exit;
}
