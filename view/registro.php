<?php
include "../controller/cnt_registro.php"
?>






<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="icon" href="../asset/img/N.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/stilo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://kit.fontawesome.com/b02da9335c.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>


    <style>
    body {
        background: #fff;

    }

    .menu i {
        color: #fff;
    }

    .floating_menu {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 999;
    }

    .menu i {
        color: #fff;
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
    </style>
</head>


<body class="bg-while">

    <section class="w-60">
        <div class="row g-0 ">
            <div class="col-lg-7  bg-primary  w-50 d-none d-lg-block overflow-auto">
                <!--CARRUCEL-->

                <div id="carouselExampleIndicators" class="carousel slide w-100 px-lg-5 pt-lg-4 p-4  "
                    data-bs-ride="true">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                            class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                            aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                            aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner cartop m-lg-5 w-100 p-lg-5 pt-lg-3 ">
                        <div class="carousel-item active">
                            <img src="../asset/img/s1.jpeg" class=" w-75 carru" alt="...">
                        </div>
                        <div class="carousel-item">
                            <img src="../asset/img/s2.jpeg" class=" w-75 carru" alt="...">
                        </div>
                        <div class="carousel-item">
                            <img src="../asset/img/s3.png" class="w-75 carru" alt="...">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden p">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>

            <div class="col-lg-5  flex-column align-items-end min-vh-100  p-lg-4 bg-white">
                <!--align-items-end min-vh-100-->
                <!--LOGO-->
                <div class="px-lg-5 pt-lg-4 pt-lg-3 p-4 w-100 ">
                    <img src="../asset/img/Please.png" class="img-fluid" class="dell" width="250px">
                </div>
                <div class="px-lg-5 py-lg-4 p-4 w-100 align-self-center">

                    <form action="" method="post" class="login-form" enctype="multipart/form-data">
                        <h1 class="font-weight-bold mb-4">Formulario de registro</h1>

                        <!-- Imprimir mensajes de error si existen -->
                        <?php
    if (!empty($mensaje)) {
        echo '<div class="alert alert-primary" role="alert">';
        foreach ($mensaje as $msg) {
            echo '<p><i class="fa-solid fa-triangle-exclamation"></i> ' . $msg . '</p>';
        }
        echo '</div>';
    }
    if (!empty($mensaje2)) {
        echo '<div class="alert alert-success" role="alert">';
        foreach ($mensaje2 as $msg) {
            echo '<p><i class="fa-solid fa-circle-check"></i> ' . $msg . '</p>';
        }
        echo '</div>';
    }
    ?>

                        <!-- Campos del formulario -->
                        <div class="form-floating mb-4">
                            <input type="text" name="nombre" class="form-control font-weight-bold" id="floatingInput"
                                required>
                            <label for="floatingInput">Nombre completo</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="text" name="usuario" class="form-control font-weight-bold" id="floatingInput"
                                required>
                            <label for="floatingInput">Usuario</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="email" name="correo" class="form-control font-weight-bold" id="floatingInput"
                                required>
                            <label for="floatingInput">Correo</label>
                        </div>
                        <div class="form mb-4">
                            <label class="form-label">Foto de perfil</label>
                            <input type="file" class="form-control" name="imagen">
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" name="contraseña" class="form-control font-weight-bold mb-2"
                                id="floatingPassword" required>
                            <label for="floatingPassword">Contraseña</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" name="confirma_contraseña" class="form-control font-weight-bold mb-2"
                                id="floatingPassword" required>
                            <label for="floatingPassword">Confirmar contraseña</label>
                        </div>
                        <div class="mb-4 form-check">
                            <input type="checkbox" name="connected" class="form-check-input" id="connected" required>
                            <label for="connected" class="form-check-label">Acepto términos y condiciones</label>
                        </div>
                        <button type="submit" class="btn btn-primary botin w-100">Registrarme</button>
                    </form>


                </div>
                <div class="text-center px-lg-5 pt-lg-3 pt-lg-4 p-4 w-100 ">

                    <span>Ya tengo Cuenta? <a href="login.php">Iniciar sesión</a></span> <br>

                </div>
            </div>


    </section>
    <div class="floating_menu" id="FloatMenu">
        <div class="menu" onclick="toggleMenu()">
            <i class="fas fa-plus"></i>
        </div>

        <div class="sud_menu">
            <a href="../index.php"><i class="fas fa-home" style="--i:1"></i></a>
            <a href="login.php"><i class="fas fa-caret-right" style="--i:2"></i></a>
            <a href="registro.php"><i class="fas fa-address-card" style="--i:3"></i></a>
        </div>
    </div>

    <script>
    var menu = document.querySelector("#FloatMenu");
    menu.onclick = function() {
        menu.classList.toggle("active");

    }
    </script>

    <!---->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>
</body>

</html>