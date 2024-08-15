<?php
include_once 'encabezado.php';
include "../controller/cnt_tarea.php";
// Conexión a la base de datos

// Obtener el id_materia desde la URL
$id_materia = isset($_GET['id_materia']) ? intval($_GET['id_materia']) : 0;

if ($id_materia > 0) {
    // Consultar las tareas de la materia seleccionada
    $query = $pdo->prepare('SELECT * FROM tareas WHERE id_materia = ?');
    $query->execute([$id_materia]);
    $tareas = $query->fetchAll(PDO::FETCH_ASSOC);

    // Consultar la información de la materia
    $queryMateria = $pdo->prepare('SELECT * FROM materias WHERE id_materia = ?');
    $queryMateria->execute([$id_materia]);
    $materia = $queryMateria->fetch(PDO::FETCH_ASSOC);
} else {
    die('Materia no válida.');
}

function getBadgeClass($estado) {
    switch ($estado) {
        case 'pendiente':
            return 'warning';
        case 'en_progreso':
            return 'info';
        case 'completada':
            return 'success';
        case 'cancelada':
            return 'danger';
        default:
            return 'secondary';
    }
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Materia</title>



    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->


</head>
<style>

</style>

<body>



    <div class="container mt-4">
        <h1 class="mb-4">Materia: <i class="bi bi-card-checklist"></i>
            <?php echo htmlspecialchars($materia['nombre_materia']); ?></h1>
        <button type="button" class="btn bnt-dsing" data-bs-toggle="modal" data-bs-target="#exampleModal">
            <i class="bi bi-plus-circle"> Crear Nueva Tarea</i>  
        </button>

        <!-- MODAL -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel"> Crear Nueva Tarea</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- CONTENIDO -->
                        <div class="container mt-3">
                            <form action="" method="post">
                                <div class="mb-3">
                                    <label for="nombre_tarea" class="form-label">Nombre de la Tarea</label>
                                    <input type="text" id="nombre_tarea" name="nombre_tarea" class="form-control"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion_tarea" class="form-label">Descripción de la Tarea</label>
                                    <textarea id="descripcion_tarea" name="descripcion_tarea" class="form-control"
                                        required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha_cierre" class="form-label">Fecha de Cierre</label>
                                    <input type="date" id="fecha_cierre" name="fecha_cierre" class="form-control"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="color" id="color" name="color" class="form-control">
                                </div>
                                <input type="hidden" id="estado" name="estado" value="pendiente">
                                <button type="submit" class="btn bnt-dsing"><i class="bi bi-save2-fill"> Guardar Tarea</i> </button>
                            </form>
                        </div>
                        <!-- CONTENIDO -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-square-fill"> Cerrar</i></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODAL -->


        <!-- VISTA CARD -->
        <div class="row mt-4">
            <?php if (!empty($tareas)): ?>
            <?php foreach ($tareas as $tarea): ?>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                <!-- Card Container -->
                <div class="card h-100 shadow-lg border-0 rounded-lg">
                    <div class="card-header text-white d-flex justify-content-between align-items-center"
                        style="background: linear-gradient(45deg, <?php echo htmlspecialchars($tarea['color']); ?>, #333);">
                        <h5 class="mb-0"><i class="bi bi-newspaper"></i> <?php echo htmlspecialchars($tarea['nombre_tarea']); ?></h5>


                        <!-- Botón de dropdown dentro de la tarjeta de la tarea -->
                        <div class="dropdown">
                            <button class="btn  dropdown-toggle accion-btn" type="button" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear-fill"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li>
                                    <button class="dropdown-item"
                                        onclick="cambiarEstado(<?php echo $tarea['id_tarea']; ?>, 'pendiente')">Pendiente</button>
                                </li>
                                <li>
                                    <button class="dropdown-item"
                                        onclick="cambiarEstado(<?php echo $tarea['id_tarea']; ?>, 'en_progreso')">En
                                        Proceso</button>
                                </li>
                                <li>
                                    <button class="dropdown-item"
                                        onclick="cambiarEstado(<?php echo $tarea['id_tarea']; ?>, 'completada')">Completada</button>
                                </li>
                                <li>
                                    <button class="dropdown-item"
                                        onclick="cambiarEstado(<?php echo $tarea['id_tarea']; ?>, 'cancelada')">Cancelada</button>
                                </li>
                            </ul>
                        </div>



                    </div>
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Descripción</h6>
                        <p class="card-text"><?php echo htmlspecialchars($tarea['descripcion_tarea']); ?></p>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center ">
                        <div>
                            <p class="card-text mb-1"><small class="text-muted"><i class="bi bi-calendar-fill"></i> Creado en:
                                    <?php echo htmlspecialchars($tarea['fecha_creacion']); ?></small></p>
                            <p class="card-text mb-1"><small class="text-muted"><i class="bi bi-calendar-fill"></i> Fecha de Cierre: 
                                    <?php echo htmlspecialchars($tarea['fecha_cierre']); ?></small></p>
                        </div>
                        <span
                            class="badge badge-pill badge-<?php echo getBadgeClass($tarea['estado']); ?>"><?php echo htmlspecialchars($tarea['estado']); ?></span>
                            
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div class="col-12 no-tareas">
                <p>No hay tareas para esta materia.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <script>
   function cambiarEstado(tareaId, nuevoEstado) {
    // Realizar la solicitud AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/cmb_estado.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                location.reload(); // Recargar la página para reflejar los cambios
            } else {
                alert("Error: " + response.message);
            }
        }
    };

    xhr.send("id_tarea=" + encodeURIComponent(tareaId) + "&nuevo_estado=" + encodeURIComponent(nuevoEstado));
}

    </script>

    <!-- JavaScript para cambiar el estado -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>




    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.min.js"></script>

<?php
include_once 'footer.php';
?>