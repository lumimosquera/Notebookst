<?php

require_once 'encabezado.php';

include "../controller/cnt_profile.php";

?>

<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header text-center bg-dark text-white">
                    <img class="rounded-circle img-thumbnail my-3" alt="Profile Image"
                        src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>" width="150" height="150">
                    <h4 class="card-title mb-0"><?php echo $nombre_usuario; ?></h4>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-weight-bold"><i class="fa-solid fa-user fa-xl" style="color: #35a4e9;"></i> Usuario:</td>
                                <td><?php echo $_SESSION['usuario_usuario']; ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold"><i class="fa-solid fa-envelope fa-xl" style="color: #35a4e9;"></i> Correo:</td>
                                <td><?php echo $_SESSION['correo_usuario']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center">
                        <button type="button" class="btn btn-primary mt-3" data-toggle="modal" data-target="#update-user-modal">
                        <i class="bi bi-pencil-square"></i> Actualizar perfil
                        </button>
                        <button type="button" class="btn btn-secondary mt-3" data-toggle="modal" data-target="#change-password-modal">
                        <i class="bi bi-pencil-square"></i> Cambiar contraseña
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para actualizar perfil -->
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
                <form action="../controller/cnt_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" class="form-control" id="name" name="nombre" placeholder="Nombre" value="<?php echo $nombre_usuario; ?>">
                    </div>
                    <div class="form-group">
                        <label for="image">Imagen de perfil</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" onchange="readURL(this);">
                    </div>
                    <div class="form-group text-center">
                        <img id="preview" class="img-thumbnail mt-3" src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>" alt="Profile Image" width="150" height="150">
                    </div>
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>
                        <input type="text" class="form-control" id="username" name="usuario" placeholder="Nombre de usuario" value="<?php echo $_SESSION['usuario_usuario']; ?>">
                    </div>
                    <div class="form-group">
                        <label for="correo"><i class="bi bi-envelope-at"></i> Correo Electrónico:</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo Electrónico" value="<?php echo $_SESSION['correo_usuario']; ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>







<!-- JavaScript to handle modal -->
<script>
  var modal = document.getElementById("updateUserModal");
  var span = document.getElementsByClassName("close")[0];

  // Open the modal
  function openModal() {
    modal.style.display = "block";
  }

  // Close the modal
  span.onclick = function() {
    modal.style.display = "none";
  }

  // Close the modal if the user clicks outside of it
  window.onclick = function(event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  }
</script>

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
