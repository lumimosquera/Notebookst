<?php
include_once '../encabezado.php';
// Conexión a la base de datos


// Conexión a la base de datos
require '../../model/conexion.php';

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
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Materia</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .card-container {
            margin-bottom: 20px;
        }

        .card-container .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card-container .card:hover {
            transform: scale(1.03);
        }

        .card-container .card-header {
            background-color: #007bff;
            color: #fff;
            border-bottom: none;
            border-radius: 10px 10px 0 0;
        }

        .card-container .card-body {
            background-color: #f8f9fa;
            border-radius: 0 0 10px 10px;
            padding: 20px;
        }

        .card-container .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .card-container .card-text {
            font-size: 0.875rem;
        }

        .card-container .btn {
            background-color: #007bff;
            color: #fff;
        }

        .card-container .btn:hover {
            background-color: #0056b3;
        }

        .no-tareas {
            text-align: center;
            margin-top: 20px;
            font-size: 1.125rem;
            color: #6c757d;
        }
    </style>
</head>

<body>

    <div class="container mt-4">
        <h1 class="mb-4">Detalle de Materia: <?php echo htmlspecialchars($materia['nombre_materia']); ?></h1>
        <div class="row">
            <?php if (!empty($tareas)): ?>
                <?php foreach ($tareas as $tarea): ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <!-- Card Container -->
                    <div class="card-container">
                        <div class="card">
                            <div class="card-header">
                                <?php echo htmlspecialchars($tarea['nombre_tarea']); ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Descripción</h5>
                                <p class="card-text"><?php echo htmlspecialchars($tarea['descripcion_tarea']); ?></p>
                                <p class="card-text"><small class="text-muted">Creado en: <?php echo htmlspecialchars($tarea['fecha_creacion']); ?></small></p>
                                <p class="card-text"><small class="text-muted">Fecha de Cierre: <?php echo htmlspecialchars($tarea['fecha_cierre']); ?></small></p>
                            </div>
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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

    <?php

include_once '../footer.php';
?>