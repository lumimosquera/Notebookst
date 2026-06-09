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
    <title>Notebookst | Ownitech</title>
    <link rel="icon" href="asset/img/N.png">
    <link rel="stylesheet" href="css/stilo.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            color-scheme: dark;
            --bg: #060913;
            --surface: rgba(8, 20, 40, 0.88);
            --panel: rgba(10, 19, 35, 0.92);
            --border: rgba(57, 191, 255, 0.16);
            --blue: #39b7ff;
            --purple: #9f66ff;
            --text: #e7f1ff;
            --muted: #8da6c7;
            --accent: #40e0d0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top, rgba(57, 191, 255, 0.14), transparent 28%),
                radial-gradient(circle at 90% 20%, rgba(159, 102, 255, 0.14), transparent 16%),
                linear-gradient(180deg, #02050b 0%, #050b16 45%, #060913 100%);
            display: flex;
            flex-direction: column;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 100% 40px, 40px 100%;
            pointer-events: none;
            opacity: 0.4;
            z-index: 0;
        }

        .navbar {
            background: rgba(7, 16, 29, 0.96);
            border-bottom: 1px solid rgba(57, 191, 255, 0.12);
            z-index: 10;
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .navbar-nav .nav-link {
            color: rgba(231, 241, 255, 0.75) !important;
            transition: color 0.2s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffffff !important;
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 5.5rem 0 4rem;
            z-index: 1;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: -20% 0 20%;
            background: radial-gradient(circle at 50% 20%, rgba(57, 191, 255, 0.22), transparent 30%);
            filter: blur(60px);
            pointer-events: none;
        }

        .hero .eyebrow {
            display: inline-flex;
            padding: 0.65rem 1rem;
            border: 1px solid rgba(57, 191, 255, 0.18);
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--blue);
            background: rgba(57, 191, 255, 0.06);
            margin-bottom: 1.6rem;
            font-size: 0.8rem;
        }

        .hero h1 {
            font-size: clamp(2.6rem, 5vw, 4.8rem);
            line-height: 0.95;
            margin-bottom: 1rem;
            letter-spacing: -0.04em;
        }

        .hero h1 .highlight {
            color: var(--purple);
        }

        .hero p.lead {
            font-size: 1.1rem;
            color: var(--muted);
            max-width: 680px;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3fb7ff 0%, #7d5fff 100%);
            border: none;
            box-shadow: 0 18px 40px rgba(63, 183, 255, 0.24);
        }

        .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.18);
            color: #f8fbff;
            background: rgba(255, 255, 255, 0.04);
        }

        .hero-visual {
            position: relative;
            background: rgba(7, 13, 27, 0.88);
            border: 1px solid rgba(57, 191, 255, 0.18);
            border-radius: 2rem;
            padding: 2rem;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.38);
            backdrop-filter: blur(10px);
        }

        .visual-frame {
            border: 1px solid rgba(57, 191, 255, 0.2);
            border-radius: 1.5rem;
            padding: 1.5rem;
            background: rgba(3, 8, 18, 0.9);
        }

        .visual-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .visual-header span {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
        }

        .terminal-list {
            list-style: none;
            padding: 0;
            margin: 0;
            color: rgba(158, 214, 255, 0.9);
        }

        .terminal-list li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 1rem;
            font-size: 0.98rem;
            line-height: 1.65;
        }

        .terminal-list li::before {
            content: '›';
            position: absolute;
            left: 0;
            top: 0;
            color: var(--blue);
        }

        .feature-card {
            background: rgba(8, 16, 33, 0.86);
            border: 1px solid rgba(63, 183, 255, 0.1);
            border-radius: 1.5rem;
            padding: 1.8rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.24);
        }

        .feature-card h3 {
            color: #ffffff;
            margin-bottom: 0.8rem;
        }

        .feature-card p {
            color: var(--muted);
        }

        .feature-icon {
            display: inline-flex;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            justify-content: center;
            align-items: center;
            background: rgba(57, 191, 255, 0.14);
            color: var(--blue);
            margin-bottom: 1rem;
        }

        .floating_menu {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 11;
        }

        .menu {
            background: linear-gradient(135deg, #3fb7ff, #7d5fff);
            border-radius: 50%;
            width: 56px;
            height: 56px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 18px 28px rgba(63, 183, 255, 0.26);
        }

        .menu i {
            color: #fff;
            font-size: 1.2rem;
        }

        .sud_menu {
            position: absolute;
            right: 0;
            bottom: 72px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            opacity: 0;
            pointer-events: none;
            transform: translateY(15px);
            transition: transform 0.25s ease, opacity 0.25s ease;
        }

        .floating_menu.active .sud_menu {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .sud_menu a {
            width: 48px;
            height: 48px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(57, 191, 255, 0.14);
            color: var(--text);
            transition: background 0.2s ease;
        }

        .sud_menu a:hover {
            background: rgba(57, 191, 255, 0.18);
        }

        footer {
            background: rgba(4, 10, 20, 0.98);
            padding: 1.2rem 0;
            color: rgba(231, 241, 255, 0.74);
        }

        footer p {
            margin: 0;
            font-size: 0.95rem;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.10);
            color: var(--muted);
            font-size: 0.88rem;
        }

        .feature-pill strong {
            color: #fff;
        }

        @media (max-width: 991px) {
            .hero {
                padding-top: 4.5rem;
            }

            .hero-visual {
                margin-top: 2rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Notebookst</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="view/registro.php">Registrarse</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view/login.php">Iniciar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="hero">
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <span class="eyebrow">OWNITECH · DARKBALL EXPERIENCE</span>
                    <h1>Tu estudio digital con potencia <span class="highlight">futurista</span>.</h1>
                    <p class="lead">Notebookst reúne notas, tareas y proyectos en una interfaz oscura, elegante y lista para tus ideas más avanzadas.</p>

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="view/registro.php" class="btn btn-primary btn-lg">Comenzar</a>
                        <a href="view/login.php" class="btn btn-outline-light btn-lg">Iniciar sesión</a>
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-5">
                        <span class="feature-pill"><strong>Seguridad</strong> cifrada</span>
                        <span class="feature-pill"><strong>Notas</strong> organizadas</span>
                        <span class="feature-pill"><strong>Interfaz</strong> Darkball</span>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-visual">
                        <div class="visual-frame">
                            <div class="visual-header">
                                <span>Notebookst</span>
                                <span><i class="fas fa-circle" style="color:#39b7ff"></i> Live</span>
                            </div>
                            <ul class="terminal-list">
                                <li>Inicio de sesión seguro activado</li>
                                <li>Espacio de notas acelerado</li>
                                <li>Panel de tareas optimizado</li>
                                <li>Sincronización de ideas en tiempo real</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section class="py-5">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-lightbulb"></i></div>
                        <h3>Ideas claras</h3>
                        <p>Encuentra la motivación para escribir, anotar y llevar tu productividad a otro nivel.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-tasks"></i></div>
                        <h3>Control total</h3>
                        <p>Puedes administrar tareas con prioridad y estado, todo dentro de una vista moderna y simple.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h3>Protegido</h3>
                        <p>Tu contenido se mantiene confiable y accesible en un entorno pensado para usuarios exigentes.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="floating_menu" id="FloatMenu">
        <div class="menu" onclick="toggleMenu()">
            <i class="fas fa-plus"></i>
        </div>
        <div class="sud_menu">
            <a href="index.php" title="Inicio"><i class="fas fa-home"></i></a>
            <a href="view/login.php" title="Login"><i class="fas fa-sign-in-alt"></i></a>
            <a href="view/registro.php" title="Registro"><i class="fas fa-user-plus"></i></a>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p>&copy; <?= date('Y') ?> Notebookst by OwniTech. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleMenu() {
            document.getElementById('FloatMenu').classList.toggle('active');
        }
    </script>
</body>

</html>
