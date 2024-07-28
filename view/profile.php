<?php

require_once 'encabezado.php';

include "../controller/cnt_profile.php";

?>

<body>

<div class="container">
    <div class="row px-lg-3 pt-lg-4 p-2 align-items-center">
        <div class="col-md-12 col-lg-6 mx-auto">
            <div class="card card-deck">
                <div class="fons fons-image d-flex justify-content-center bg-dark">
                    <img class="m-4 custom-image-profile" alt="..." src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>">
                </div>
                <div class="card-body">
                    <h5 class="card-text">
                        <i class="bi bi-person-badge"></i>
                        <span class="ml-2"><?php echo $nombre_usuario; ?></span>
                    </h5>
                    <table class="table table-no-margin table-no-border table-no-lines">
                        <thead>
                            <tr>
                                <th class="col-md-6">
                                    <i class="fa-solid fa-user fa-xl" style="color: #35a4e9;"></i>
                                    <span class="ml-2">Usuario:</span>
                                </th>
                                <th class="col-md-6"><?php echo $_SESSION['usuario_usuario']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="col-md-6">
                                    <i class="fa-solid fa-envelope fa-xl" style="color: #35a4e9;"></i>
                                    <span class="ml-2">Correo:</span>
                                </th>
                                <th class="col-md-6"><?php echo $_SESSION['correo_usuario']; ?></th>
                            </tr>
                        </tbody>
                    </table>
                    <!-- boton modal-->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#update-user-modal">
                        Actualizar perfil
                    </button>

                    <div class="modal fade" id="update-user-modal" tabindex="-1" role="dialog" aria-labelledby="update-user-modal-label" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="update-user-modal-label">Actualizar perfil</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="name">Nombre</label>
                                            <input type="text" class="form-control" id="name" name="nombre" placeholder="Nombre">
                                        </div>
                                        <div class="form-group">
                                            <label for="image">Imagen de perfil</label>
                                            <input type="file" class="form-control" id="imagen" name="imagen" onchange="readURL(this);">
                                        </div>
                                        <div class="form-group text-center">
                                            <img id="preview" class="preview-image" src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>" alt="" />
                                        </div>
                                        <div class="form-group">
                                            <label for="username">Nombre de usuario</label>
                                            <input type="text" class="form-control" id="username" name="usuario" placeholder="Nombre de usuario">
                                        </div>
                                        <div class="form-group">
                                            <label for="correo"><i class="bi bi-envelope-at"></i> Correo Electrónico:</label>
                                            <input type="email" class="form-control" id="correo" name="correo" placeholder="<?php echo $_SESSION['correo_usuario']; ?>">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <input type="submit" value="Guardar" class="btn btn-primary"></input>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--fin del boton-->
                </div>
            </div>
        </div>
    </div>
</div>



</body>

</html>

<?php include_once 'footer.php'; ?>
<script>
  function readURL(input) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        $('#preview').attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>