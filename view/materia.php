<?php
include "../controller/cnt_materia.php";
// En tu página principal
if (isset($_POST['id_materia'])) {
    include '../controller/eliminar_materia.php';
}

?>


<body>


    <div class="container mt-3">
        <div class="card border-0">
            <div class="card-body">
                <?php if(!empty($message)): ?>
                <div class="alert alert-primary" role="alert">
                    <p><i class="fa-solid fa-triangle-exclamation"></i> <?= $message ?></p>
                </div>
                <?php endif; ?>

                <h1 class="card-title">Crear Materia</h1>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-notes-medical fa-2xl"></i></span>
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre de la materia"
                            required>
                    </div>
                    <input type="hidden" name="id_usuario" value="<?= $_SESSION['user_id']; ?>">
                    <button type="submit" class="btn btn-primary">Crear</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row">
            <?php foreach ($materias as $materia): ?>
            <div class="col-lg-3 col-6">
                <!-- Card Container -->
                <div class="card-container">
                    <!-- Begin::Small Box Widget -->
                    <div class="small-box"
                        style="background-color: <?php echo htmlspecialchars($materia['color']); ?>;">
                        <form method="post" action="../controller/eliminar_materia.php" class="d-inline eliminar">
 
    <button class="delete-btn" type="submit"><i class="fas fa-trash-alt"></i></button>
</form>



                        <div class="inner link-light">
                            <h3><?php echo htmlspecialchars($materia['nombre_materia']); ?></h3>
                        </div>
                        <a href="#"
                            class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                            Más Info <i class="bi bi-link-45deg"></i>
                        </a>
                    </div>
                    <!-- End::Small Box Widget -->
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- Asegúrate de incluir Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>



</body>

</html>