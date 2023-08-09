<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
  </head>
  <body>
    <nav class="navbar bg-dark-subtle">
        <div class="container-fluid">
          <a class="navbar-brand">Inicio</a>    
          <!--FECHA Y HORA--> 
          <div class="p-3 btn btn-outline-dark bg-opacity-10 border border-white border-start rounded-end">  <h5 id="fecha" class=""></h5></div>
          <form class="d-flex" role="search">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">

            <div class="dropdown-center">
          
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <a class="navbar-brand" href="#">Luis Miguel</a>
                
                        <img src="img/user/perfil.png" class="rounded-circle" width="32"> <a href="home.html" class="text-decoration-none"></a>
                 
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="#">Perfil</a></li>
                  <li><a class="dropdown-item" href="#">Action two</a></li>
                  <li><a class="dropdown-item" href="in">Cerrar sesión</a></li>
                </ul>
              </div>
          </form>
        </div>
      </nav>

      <html>

<head>
<meta charset="UTF-8">
<title>GRANERO EL PAISA</title>
<link rel="stylesheet" href="MODULOS/css/lumi.css">
<link rel="icon" href="img/logo.ico">
</head>


  <div class="login-logo">

  </div>


<body>
<form action="inicio/login_registrar.php" method="POST">

<h2>Iniciar sesión</h2>
<input type="text" placeholder="&#128273; Usuario" name="usuario" required>
<input type="password" placeholder="&#128274; Contraseña" name="pass" required>
<input type="submit" value="Ingresar" name="btningresar">

<br>


</form>
</body>
</html>

    
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
  <script>
    setInterval(() => {
      let fecha = new Date();
      let fechahora = fecha.toLocaleString();
      document.getElementById("fecha").textContent = fechahora;
    }, 1000);
  </script>
    </body>
</html>