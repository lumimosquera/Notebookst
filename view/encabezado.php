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
  $stmt = $pdo ->prepare($query);
  $stmt->bindParam(':id_usuario', $user_id);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  // Almacenar el nombre del usuario en una variable de sesión
  $_SESSION['nombre_usuario'] = $result['nombre'];
  $nombre_usuario = $_SESSION['nombre_usuario'];
}



// Consulta SQL para buscar el nombre, correo y usuario del usuario en la tabla de usuarios
$query = "SELECT nombre, correo, usuario, imagen FROM usuarios WHERE id_usuario = :id_usuario";
$stmt = $pdo ->prepare($query);
$stmt->bindParam(':id_usuario', $user_id);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Almacenar el nombre, correo y usuario del usuario en variables de sesión
$_SESSION['nombre_usuario'] = $result['nombre'];
$_SESSION['correo_usuario'] = $result['correo'];
$_SESSION['usuario_usuario'] = $result['usuario'];
$_SESSION['imagen_usuario'] = $result['imagen'];  // Nueva línea para la imagen

// Mostrar el nombre del usuario en la barra de navegación

// Variables para usar en el HTML
$nombre_usuario = $_SESSION['nombre_usuario'];
$imagen_usuario = $_SESSION['imagen_usuario'];



?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link rel="icon" href="../asset/img/N.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/b02da9335c.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Incluye Bootstrap CSS -->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="author" content="ColorlibHQ">
    <meta name="description"
        content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS.">
    <meta name="keywords"
        content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard">
    <!--end::Primary Meta Tags-->
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous">
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css"
        integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous">
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css"
        integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous">
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="../../dist/css/adminlte.css">
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous"><!-- jsvectormap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
        integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous">
    <link href="../asset/css/adminlte.css" rel="stylesheet">
    <link href="../asset/css/midesing.css" rel="stylesheet">

    <link href="../asset/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>
<style>


</style>

<body>

    <nav class="navbar navbar-expand-lg bg-dark-subtle">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">
                Inicio <i class="bi bi-house-fill"></i>
            </a>

            <!-- Botón de colapso para móviles -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <!-- Mostrar nombre y foto del usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>"
                                class="rounded-circle img-menu" alt="User Image">
                            <span class="d-none d-md-inline"><?php echo $nombre_usuario; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu" aria-labelledby="userDropdown">
                            <li class="dropdown-header text-bg-dark">
                                <img src="<?php echo $imagen_usuario ? $imagen_usuario : '../assets/img/default-user.png'; ?>"
                                    class="rounded-circle shadow-sm mb-2 custom-image" alt="User Image">
                                <p class="mb-0"> <?php echo $nombre_usuario; ?></p>
                                <small>Miembro desde Nov. 2023</small>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Perfil</a></li>
                            <li><a class="dropdown-item" href="../model/logout.php"><i class="fas fa-sign-out-alt"></i>
                                    Cerrar sesión</a></li>

                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"
        integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script src="../asset/js/adminlte.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
    <script>
    setInterval(() => {
        let fecha = new Date();
        let fechahora = fecha.toLocaleString();
        document.getElementById("fecha").textContent = fechahora;
    }, 1000);
    </script>