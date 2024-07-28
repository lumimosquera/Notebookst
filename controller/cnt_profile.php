<?php



require '../model/conexion.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = ""; // Variable para almacenar los mensajes de error o éxito
    
    // Obtener los datos del formulario
    $nombre = $_POST["nombre"];
    $usuario = $_POST["usuario"];
    $correo = $_POST["correo"];
    
    // Validar y manejar la imagen
    if ($_FILES["imagen"]["name"]) {
        $imagen_nombre = $_FILES["imagen"]["name"];
        $imagen_temp = $_FILES["imagen"]["tmp_name"];
        $imagen_tamano = $_FILES["imagen"]["size"];
        $imagen_tipo = $_FILES["imagen"]["type"];
        
        // Validar el formato y tamaño de la imagen
        $formatos_permitidos = array("image/jpeg", "image/png");
        $tamano_maximo = 5 * 1024 * 1024; // 5 MB
        
        if (in_array($imagen_tipo, $formatos_permitidos) && $imagen_tamano <= $tamano_maximo) {
            $ruta_destino = "../ruta/de/destino/" . $imagen_nombre;
            
            // Mover la imagen al servidor
            if (move_uploaded_file($imagen_temp, $ruta_destino)) {
                // Guardar la ruta en la base de datos
                // Aquí debes usar tu lógica de conexión y ejecución de consulta SQL
                // Ejemplo: $consulta = "UPDATE usuarios SET imagen = '$ruta_destino' WHERE usuario = '$usuario'";
                // Ejecutar la consulta y manejar cualquier error
                
                $mensaje = "Imagen subida y datos actualizados correctamente.";
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        } else {
            $mensaje = "Formato de imagen no válido o tamaño excede el límite.";
        }
    } else {
        // No se subió una nueva imagen, solo actualizar los otros datos en la base de datos
        // Aquí puedes realizar la actualización de los campos nombre, usuario y correo en la base de datos
        // Ejemplo: $consulta = "UPDATE usuarios SET nombre = '$nombre', usuario = '$usuario', correo = '$correo' WHERE usuario = '$usuario'";
        // Ejecutar la consulta y manejar cualquier error
        
        $mensaje = "Datos actualizados correctamente.";
    }
}


?>
