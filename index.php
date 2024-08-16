<?php
  session_start();

  require 'model/conexion.php';

  if (isset($_SESSION['user_id'])) {
    $records = $pdo->prepare('SELECT id_usuario, usuario, contraseña FROM usuarios WHERE id_usuario = :id_usuario');
    $records->bindParam(':id_usuario', $_SESSION['user_id']);
    $records->execute();
    $results = $records->fetch(PDO::FETCH_ASSOC);

    $user = null;

    if ($results) {
      $user = $results;
    }
  }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Notebookst</title>
    <!-- Icono de la página -->
    <link rel="icon" href="asset/img/N.png">
    <!-- Enlace a Bootstrap y otros CSS -->
    <link rel="stylesheet" href="css/stilo.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* Estilos del fondo y otros elementos */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f1f1f1;
            margin: 0;
        }

        header {
            flex: 1;
        }

        .navbar {
            background-color: #05324c;
        }

        .floating_menu {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999;
        }

        .menu {
            background-color: #007bff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .menu:hover {
            background-color: #0056b3;
        }

        .sud_menu {
            position: absolute;
            right: 60px;
            bottom: 0;
            transform: translateX(100%);
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            pointer-events: none;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .floating_menu.active .sud_menu {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        .sud_menu i {
            font-size: 24px;
            color: #fff;
            background-color: #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .sud_menu i:hover {
            background-color: #0056b3;
        }

        .menu i {
            color: #fff;
        }

        /* FOOTER */
        footer {
            background-color: #0a141d;
            color: #eff9ff;
            padding: 1rem 0;
            text-align: center;
            margin-top: auto;
        }

        /* Imagen y video responsivos */
        header img {
            max-width: 100%;
            height: auto;
        }

        #video-bg {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Notebookst</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="./view/registro.php">¿Deseas Registrarte?</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view/login.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <header class="py-5 text-center">
        <img src="asset/img/Please.png" alt="Imagen de bienvenida">
        <p class="lead">Tu lugar para escribir y organizar tus notas</p>
    </header>

    <!-- Sección Acerca de -->
    <section class="py-5" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2>¿Deseas Registrarte?</h2>
                    <p class="lead">Regístrate para empezar a usar Notebook y guardar tus notas de manera segura.</p>
                    <a href="view/registro.php" class="btn btn-primary">Registrarse</a>
                </div>
                <div class="col-lg-6 mb-4">
                    <h2>¿Ya tienes una cuenta?</h2>
                    <p class="lead">Inicia sesión para acceder a tus notas y continuar escribiendo.</p>
                    <a href="view/login.php" class="btn btn-secondary">Iniciar Sesión</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Video de fondo -->
    <video autoplay loop muted playsinline id="video-bg">
        <source src="ruta/de/video.mp4" type="video/mp4">
        <!-- Si el navegador no admite video, se muestra la imagen de respaldo -->
        <img src="ruta/de/imagen-de-respaldo.jpg" alt="Video no soportado">
    </video>

    <!-- Menú flotante -->
    <div class="floating_menu" id="FloatMenu">
        <div class="menu" onclick="toggleMenu()">
            <i class="fas fa-plus"></i>
        </div>
        <div class="sud_menu">
            <a href="index.php"><i class="fas fa-home"></i></a>
            <a href="view/login.php"><i class="fas fa-caret-right"></i></a>
            <a href="view/registro.php"><i class="fas fa-address-card"></i></a>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="py-3 mt-auto">
        <div class="container text-center">
            <p>Notebookst &copy; 2024. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Enlaces a JavaScript de Bootstrap y jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper
