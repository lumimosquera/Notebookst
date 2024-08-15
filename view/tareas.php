<?php
include_once 'encabezado.php';
include "../controller/cnt_materia.php";
include "../controller/filtros.php";

// Funcion de color para los estados
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

<!--  barra de busqueda -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="dropdown">

                    <button class="btn bnt-dsing dropdown-toggle" type="button" id="filterDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Todas
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                        <li><a class="dropdown-item" href="?filter=todos">Todas</a></li>
                        <li><a class="dropdown-item" href="?filter=pendiente">Pendiente</a></li>
                        <li><a class="dropdown-item" href="?filter=en_progreso">En progreso</a></li>
                        <li><a class="dropdown-item" href="?filter=completada">Completada</a></li>
                        <li><a class="dropdown-item" href="?filter=cancelada">Cancelada</a></li>
                    </ul>
                </div>

                <form class="flex-grow-1 mx-4" action="" method="get">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <input type="hidden" name="id_materia"
                        value="<?php echo htmlspecialchars($id_materia_seleccionada); ?>">
                    <input type="text" name="busqueda" class="form-control" placeholder="Buscar"
                        value="<?php echo $busqueda_mostrada; ?>">

                </form>



                <div class="dropdown mx-2">

                    <button class="btn bnt-dsing dropdown-toggle" type="button" id="sortDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-filter-circle-fill"> Ordenar por Materia</i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                        <?php foreach ($materias as $materia): ?>
                        <li>
                            <a class="dropdown-item"
                                href="?id_materia=<?php echo $materia['id_materia']; ?>"><?php echo htmlspecialchars($materia['nombre_materia']); ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
                <div class="dropdown">
                    <button class="btn bnt-dsing dropdown-toggle" type="button" id="viewDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Fecha
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="viewDropdown">
                        
                    <li><a class="dropdown-item" href="#">Fecha de finalización</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- barra de busqueda -->


<!-- -->
<!-- VISTA CARD -->
<div class="container mt-4">
    <div class="row mt-4">
        <?php if (!empty($tareas)): ?>
        <?php foreach ($tareas as $tarea): ?>
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <!-- Card Container -->
            <div class="card h-100 shadow-sm border-0 rounded-lg">
                <div class="card-header text-white d-flex justify-content-between align-items-center"
                    style="background: <?php echo isset($tarea['color']) ? 'linear-gradient(45deg, ' . htmlspecialchars($tarea['color']) . ', #333)' : '#333'; ?>;">
                    <!-- Mostrar el nombre de la materia -->
                    <h6 class="mb-0">
                        <?php echo htmlspecialchars($tarea['nombre_tarea']); ?>
                    </h6>

                    <!-- Dropdown button in the task card -->
                    <div class="dropdown ms-auto">
                        <button class="btn btn-sm dropdown-toggle accion-btn text-white" type="button"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
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
                                    Progreso</button>
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
                    <!-- Mostrar el nombre de la tarea -->
                    <p class="mb-0">

                        <?php echo htmlspecialchars($tarea['nombre_materia']); ?>
                    </p>
                    <!-- Descripción de la tarea -->
                    <p class="card-text">
                        <?php echo htmlspecialchars($tarea['descripcion_tarea']); ?>
                    </p>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <div>
                        <p class="card-text mb-1"><small class="text-muted"><i class="bi bi-calendar-fill"></i> Creado
                                en:
                                <?php echo htmlspecialchars($tarea['fecha_creacion']); ?></small></p>
                        <p class="card-text mb-1"><small class="text-muted"><i class="bi bi-calendar-fill"></i> Fecha de
                                Cierre:
                                <?php echo htmlspecialchars($tarea['fecha_cierre']); ?></small></p>
                    </div>
                    <span class="badge bg-<?php echo getBadgeClass($tarea['estado']); ?>">
                        <?php echo htmlspecialchars($tarea['estado']); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="col-12 no-tareas">
            <p>No hay tareas para mostrar.</p>
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

    xhr.onreadystatechange = function() {
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





<?php
include_once 'footer.php';
?>