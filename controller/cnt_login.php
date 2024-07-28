<?php

session_start();



//aqui pregunto si el user tiene la session activa lo rredireccione a home
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
}

require '../model/conexion.php';

if (!empty($_POST['usuario']) && !empty($_POST['contraseña'])) {
$records = $pdo ->prepare('SELECT id_usuario, usuario, contraseña FROM usuarios WHERE usuario = :usuario');
$records->bindParam(':usuario', $_POST['usuario']);
$records->execute();
$results = $records->fetch(PDO::FETCH_ASSOC);



$message = '';

if (count($results) > 0 && password_verify($_POST['contraseña'], $results['contraseña'])) {
  $_SESSION['user_id'] = $results['id_usuario'];
  header("Location: home.php");
} else {
  $message = 'Lo sentimos, las credenciales no coinciden';
}

}


?>