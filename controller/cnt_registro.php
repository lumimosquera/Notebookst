<?php
// Incluir el archivo de conexión
include '../model/conexion.php';

// Validando el formulario
if (isset($_POST['nombre']) && isset($_POST['usuario']) && isset($_POST['correo']) && isset($_POST['contraseña']) && isset($_POST['confirma_contraseña'])) {
    $nombre = $_POST['nombre'];
    $usuario = $_POST['usuario'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];
    $confirma_contraseña = $_POST['confirma_contraseña'];

    $mensaje = array(); //aqui se almacenan los mensajes de alerta
    // Comprobando si las contraseñas coinciden
    if ($contraseña != $confirma_contraseña) {
        $mensaje[] = "Las contraseñas no coinciden.";
    } else {


        // Comprobando si el correo electrónico ya existe
        $sql = "SELECT * FROM usuarios WHERE correo = '$correo'";
        $stmt = $pdo ->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            $mensaje[] = "Este correo ya está registrado.";
        } else {
            if (strlen($contraseña) < 8) {
                $mensaje[] = "La contraseña debe tener al menos 8 caracteres.";
            } else if (!preg_match('/[a-z]+/', $contraseña)) {
                $mensaje[] =  "La contraseña debe contener al menos una letra minúscula.";
            } else if (!preg_match('/[A-Z]+/', $contraseña)) {
                $mensaje[] =  "La contraseña debe contener al menos una letra mayúscula.";
            } else if (!preg_match('/[0-9]+/', $contraseña)) {
                $mensaje[] =  "La contraseña debe contener al menos un número.";
            } else if (!preg_match('/[!@#$%^&*()]+/', $contraseña)) {
                $mensaje[] =  "La contraseña debe contener al menos un símbolo.";
            } else {

                // Comprobando que el usuario ingresado no exista
                $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
                $stmt = $pdo ->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll();

                if (count($result) > 0) {
                    $mensaje[] = "Este usuario ya existe.";
                } else {
                    // Encriptación de la contraseña
                    $password = password_hash($contraseña, PASSWORD_BCRYPT);

                    //Insertando los datos en la base de datos
                    $sql = "INSERT INTO usuarios (nombre, usuario, correo, contraseña) VALUES ('$nombre', '$usuario', '$correo', '$password')";
                    $stmt = $pdo ->prepare($sql);
                    $stmt->execute();

                    // Mostrar un mensaje de confirmación
                    $mensaje2[] = "<strong>¡Felicidades!</strong> Te has registrado en Notbookst.";
                    


                    //header("Location: registro.php");
                }
            }
        }
    }
}
?>