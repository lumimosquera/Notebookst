<?php

$id_usuario = $_SESSION['user_id']; // Obtener el ID del usuario logueado

// Obtener los filtros de la URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'todos';
$id_materia_seleccionada = isset($_GET['id_materia']) ? $_GET['id_materia'] : null;
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Limpiar el texto de búsqueda para mostrar en la barra de búsqueda
$busqueda_mostrada = htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8');

// Construir la consulta SQL base
$query = "SELECT tareas.id_tarea, tareas.nombre_tarea, tareas.descripcion_tarea, tareas.fecha_creacion, 
                 tareas.fecha_cierre, tareas.estado, tareas.color, materias.nombre_materia
          FROM tareas
          INNER JOIN materias ON tareas.id_materia = materias.id_materia
          WHERE materias.id_usuario = :id_usuario";

// Agregar condiciones dinámicamente
$params = ['id_usuario' => $id_usuario];

// Filtrar por estado si se aplica
if ($filter !== 'todos') {
    $query .= " AND tareas.estado = :estado";
    $params['estado'] = $filter;
}

// Filtrar por materia si se ha seleccionado una
if ($id_materia_seleccionada) {
    $query .= " AND materias.id_materia = :id_materia";
    $params['id_materia'] = $id_materia_seleccionada;
}

// Filtrar por texto de búsqueda si se proporciona
if (!empty($busqueda)) {
    $query .= " AND (tareas.nombre_tarea LIKE :busqueda OR materias.nombre_materia LIKE :busqueda)";
    $params['busqueda'] = "%{$busqueda}%";
}

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}

$stmt->execute();
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$estado_actual = ucfirst($filter); // Solo se usa si es necesario para la vista

?>
