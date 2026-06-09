<?php

session_start();



// Verificar si el usuario ya tiene una sesión activa y redirigir a home
if (isset($_SESSION['user_id'])) {
  header('Location: home.php');
  exit;
}

require '../model/conexion.php';

if (!empty($_POST['usuario']) && !empty($_POST['contraseña'])) {
  // Preparar la consulta para buscar por usuario o correo
  $records = $pdo->prepare('SELECT id_usuario, usuario, correo, contraseña FROM usuarios WHERE usuario = :usuario OR correo = :correo');
  $records->bindParam(':usuario', $_POST['usuario']);
  $records->bindParam(':correo', $_POST['usuario']);
  $records->execute();
  $results = $records->fetch(PDO::FETCH_ASSOC);

  $message = '';

  // Verificar si se encontró un resultado y la contraseña es correcta
  if ($results && password_verify($_POST['contraseña'], $results['contraseña'])) {
      $_SESSION['user_id'] = $results['id_usuario'];
      header("Location: home.php");
      exit;
  } else {
      $message = 'Lo sentimos, las credenciales no coinciden';
  }
}


?>