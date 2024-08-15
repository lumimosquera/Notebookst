<?php

require_once 'encabezado.php';

include "../controller/cnt_profile.php";
// CONTEO DE TAREAS
$id_usuario = $_SESSION['user_id']; // Obtén el ID del usuario actual desde la sesión


/// Consulta para obtener el conteo total de materias asociadas al usuario
$countQuery = $pdo->prepare("
SELECT COUNT(*) as total_materias
FROM materias
WHERE id_usuario = :id_usuario
");
$countQuery->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$countQuery->execute();
$countResult = $countQuery->fetch(PDO::FETCH_ASSOC);
$totalMaterias = $countResult['total_materias'];


// Consulta para obtener el conteo total de tareas asociadas al usuario
$totalQuery = $pdo->prepare("
    SELECT COUNT(*) as total_tareas
    FROM tareas
    INNER JOIN materias ON tareas.id_materia = materias.id_materia
    WHERE materias.id_usuario = :id_usuario
");
$totalQuery->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$totalQuery->execute();
$totalResult = $totalQuery->fetch(PDO::FETCH_ASSOC);
$totalTareas = $totalResult['total_tareas'];
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body box-profile">
                        <div class="text-center mb-3">
                            <img class="profile-user-img img-fluid img-circle"
                                src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>"
                                alt="User profile picture">
                        </div>
                        <h3 class="profile-username text-center text-primary"><?php echo $nombre_usuario; ?></h3>
                        <p class="text-muted text-center">Ingenieria en Sistemas</p>
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <b>Materias</b> <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($totalMaterias); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <b>Tareas</b> <span class="badge badge-primary badge-pill"><?php echo htmlspecialchars($totalTareas); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <b>Friends</b> <span class="badge badge-primary badge-pill">13,287</span>
                            </li>
                        </ul>
                        <a href="#" class="btn btn-primary btn-block"><b>....</b></a>
                    </div>
                </div>
            </div>
            <!-- Ajustar para que ambas tarjetas estén en la misma fila -->
            <div class="col-md-9">
                <div class="card card-primary shadow-sm">
                    <div class="card-header card-all">
                        <h3 class="card-title">Mas Info</h3>
                    </div>
                    <div class="card-body">
                        <strong><i class="fas fa-book mr-1"></i> Education</strong>
                        <p class="text-muted">
                            B.S. in Computer Science from the University of Tennessee at Knoxville
                        </p>
                        <hr>
                        <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>
                        <p class="text-muted">Medellin</p>
                        <hr>
                        <strong><i class="fas fa-pencil-alt mr-1"></i> Skills</strong>
                        <p class="text-muted">
                            <span class="badge badge-danger">UI Design</span>
                            <span class="badge badge-success">Coding</span>
                            <span class="badge badge-info">Javascript</span>
                            <span class="badge badge-warning">PHP</span>
                            <span class="badge badge-primary">Node.js</span>
                        </p>
                        <hr>
                        <strong><i class="far fa-file-alt mr-1"></i> Notes</strong>
                        <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam fermentum enim neque.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>





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
