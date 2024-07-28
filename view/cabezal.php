<?php
// Inicio de sesión

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
  // Redirigir al usuario a la página de inicio
  header('Location: ../index.php');
  exit;
}



// Conexión a la base de datos
require '../model/conexion.php';

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['user_id'])) {
  // Obtener el ID del usuario actual
  $user_id = $_SESSION['user_id'];

  // Consulta SQL para buscar el nombre del usuario en la tabla de usuarios
  $query = "SELECT nombre FROM usuarios WHERE id_usuario = :id_usuario";
  $stmt = $conn->prepare($query);
  $stmt->bindParam(':id_usuario', $user_id);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  // Almacenar el nombre del usuario en una variable de sesión
  $_SESSION['nombre_usuario'] = $result['nombre'];
  $nombre_usuario = $_SESSION['nombre_usuario'];
}





?>



<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home</title>
  <link rel="icon" href="../img/N.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>

<body>
  <nav class="navbar bg-dark-subtle">
    <div class="container-fluid">
      <a class="navbar-brand" href="../index.php">Inicio</a>
      <!--FECHA Y HORA-->
      <div class="p-3 btn btn-outline-dark bg-opacity-10 border border-white border-start rounded-end d-none d-lg-block overflow-auto">
        <h5 id="fecha" class=""></h5>
      </div>
      <form class="d-flex" role="search">
        <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">

        <div class="dropdown-center">

          <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">

            <!--Mostrar el nombre del usuario en la barra de navegación-->
            <a class="navbar-brand nombre_usuario" href="#"><?php echo $nombre_usuario; ?></a>

            <img src="../img/user/perfil.png" class="rounded-circle" width="32"> <a href="home.html" class="text-decoration-none"></a>

          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="#">Perfil</a></li>
            <li><a class="dropdown-item" href="#">Action two</a></li>
            <li><a class="dropdown-item" href="../model/logout.php">Cerrar sesión</a></li>
          </ul>
        </div>
      </form>
    </div>
  </nav>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
  <script>
    setInterval(() => {
      let fecha = new Date();
      let fechahora = fecha.toLocaleString();
      document.getElementById("fecha").textContent = fechahora;
    }, 1000);
  </script>