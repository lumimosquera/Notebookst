<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Mega</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/stilo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://kit.fontawesome.com/b02da9335c.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@300;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>


    <style>
        body {
            background: #fff;
          
        }
    </style>
</head>


<body class="bg-while">

    <section class="w-60">
        <div class="row g-0 ">
            <div class="col-lg-7  bg-primary  w-50 d-none d-lg-block overflow-auto"> <!--CARRUCEL-->

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
                            <img src="../img/s1.jpeg" class=" w-75 carru" alt="...">
                        </div>
                        <div class="carousel-item">
                            <img src="../img/s2.jpeg" class=" w-75 carru" alt="...">
                        </div>
                        <div class="carousel-item">
                            <img src="../img/s3.png" class="w-75 carru" alt="...">
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

            <div class="col-lg-5  flex-column align-items-end min-vh-100  p-lg-4 bg-white"> <!--align-items-end min-vh-100-->
                <!--LOGO-->
                <div class="px-lg-5 pt-lg-4 pt-lg-3 p-4 w-100 ">
                    <img src="../img/Please.png" class="img-fluid" class="dell" width="300px">
                </div>
                <div class="px-lg-5 py-lg-4 p-4 w-100 align-self-center">
                    <h1 class=" font-weight-bold mb-4">Formulario de registro </h1> 

                         <!--imput 1-->

                    <div class="form-floating mb-4">
                        <input type="text" name="nombre" class="form-control font-weight-bold" id="floatingInput" required>
                        <label for="floatingInput">Nombre completo</label>
                    </div>
                        <!--input 2-->
                        <div class="form-floating mb-4">
                            <input type="text" name="usuario" class="form-control font-weight-bold" id="floatingInput"
                                placeholder="name@example.com "  required>
                            <label for="floatingInput">Usuario </label>
                        </div>
                         <!--input 3-->
                         <div class="form-floating mb-4">
                            <input type="email" name="usuario" class="form-control font-weight-bold" id="floatingInput"
                                placeholder="name@example.com">
                            <label for="floatingInput">correo</label>
                        </div>
                          <!--input 4-->
                        <div class="form-floating mb-4">
                            <input type="password" name="contraseña" class="form-control font-weight-bold mb-2"
                                id="floatingPassword" placeholder="Password">
                            <label for="floatingPassword ">Contraseña</label> 
                            
                        </div>
                             <!--input 4-->
                             <div class="form-floating mb-4">
                                <input type="password" name="contraseña" class="form-control font-weight-bold mb-2"
                                    id="floatingPassword" placeholder="Password">
                                <label for="floatingPassword ">Contraseña</label> 
                                <a href="https://worldvectorlogo.com/downloaded/dell-1" id="emailHelp"
                                    class="form-text  text-decoration-none " style="color:#216fdb">¿Olvidaste tu contraseña?
                                </a>
                            </div>
                            <div class="mb-4 form-check"> <!-- para mantener la seccion activa-->
                                <input type="checkbox" name="connected" class="form-check-input" id="" />
                                <label for="connected" class="form-check-label">Acepto términos y condiciones</label>
                            </div>
                       
                        <button type="" class="btn btn-primary botin w-100">Registrar me</button>
                    </form>
                    
                </div>
                <div class="text-center px-lg-5 pt-lg-3 pt-lg-4 p-4 w-100 ">

                    <span>Ya tengo  Cuenta? <a href="index.html">Iniciar sesión</a></span> <br>

                </div>
            </div>


    </section>



    <!---->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>

</html>