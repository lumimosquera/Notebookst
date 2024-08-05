<?php
include "../controller/cnt_materia.php";


?>


<body>


    <div class="container mt-3">
        <div class="card border-0">
            <div class="card-body">
                <?php if(!empty($message)): ?>
                <div id="tempAlert" class="alert alert-success" role="alert">
                    <p><i class="fa-solid fa-check-circle"></i> <?= $message ?></p>
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


    <script>
    // Espera a que el DOM se cargue completamente
    document.addEventListener("DOMContentLoaded", function() {
        // Selecciona el elemento de la alerta temporal
        var tempAlert = document.getElementById('tempAlert');

        // Verifica si el elemento existe
        if (tempAlert) {
            // Configura un temporizador para eliminar la alerta después de 2 segundos (2000 ms)
            setTimeout(function() {
                tempAlert.style.display = 'none'; // Oculta la alerta
            }, 800); // 1000 ms = 1 segundos
        }
    });
    </script>


<div class="container mt-3">
    <div class="row">
        <?php foreach ($materias as $materia): ?>
        <div class="col-lg-3 col-6">
            <!-- Card Container -->
            <div class="card-container">
                <!-- Begin::Small Box Widget -->
                <div class="small-box"
                    style="background-color: <?php echo htmlspecialchars($materia['color']); ?>;">

                    <form method="post" action="../controller/eliminar_materia.php" class="d-inline eliminar"
                        onsubmit="return confirmDelete(event, this)">
                        <input type="hidden" name="id"
                            value="<?php echo htmlspecialchars($materia['id_materia']); ?>">
                        <button class="delete-btn" type="submit"><i class="fas fa-trash-alt"></i></button>
                    </form>

                    <div class="inner link-light">
                        <h3><?php echo htmlspecialchars($materia['nombre_materia']); ?></h3>
                    </div>
                    <a href="./materia/detalle_materia.php?id_materia=<?php echo htmlspecialchars($materia['id_materia']); ?>"
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(event, form) {
        event.preventDefault(); // Detener el envío del formulario

        Swal.fire({
            title: '¿Estás seguro de eliminar esta materia?',
            text: "No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Enviar el formulario si el usuario confirma
            }
        });

        return false; // Evitar el envío del formulario por defecto
    }
    </script>



    <!-- Incluir SweetAlert2 desde un CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</body>

</html>