<!DOCTYPE html>
<html>
<head>
    <title>Notebook - Página de Inicio</title>
    <!-- Enlace al archivo CSS de Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
        /* Estilos del fondo animado */
        body {
            background-color: #f1f1f1;
            overflow: hidden;
            position: relative;
        }
</style>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark  bg-secondary">
        <div class="container">
            <a class="navbar-brand" href="#">Notebook</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item txt-whhile">
                        <a class="nav-link" href="./view/registro.php">¿Deseas Registrarte?</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <header class="py-5 text-center">
        <h1 class="display-4">Notebook</h1>
        <p class="lead">Tu lugar para escribir y organizar tus notas</p>
    </header>

    <!-- Sección Acerca de -->
    <section class="py-5" id="about">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2>¿Deseas Registrarte?</h2>
                    <p class="lead">Regístrate para empezar a usar Notebook y guardar tus notas de manera segura.</p>
                    <a href="view/registro.php" class="btn btn-primary">Registrarse</a>
                </div>
                <div class="col-lg-6">
                    <h2>¿Ya tienes una cuenta?</h2>
                    <p class="lead">Inicia sesión para acceder a tus notas y continuar escribiendo.</p>
                    <a href="view/login.php" class="btn btn-secondary">Iniciar Sesión</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Video de fondo -->
    <video autoplay loop muted playsinline id="video-bg">
        <source src="" type="video/mp4">
        <!-- Si el navegador no admite video, puedes mostrar una imagen de respaldo -->
        <img src="ruta/de/imagen-de-respaldo.jpg" alt="Video no soportado">
    </video>

    <!-- Enlace al archivo JS de Bootstrap y jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
