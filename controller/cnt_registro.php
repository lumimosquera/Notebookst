<?php
// Incluir el archivo de conexión
include '../model/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $confirma_contraseña = $_POST['confirma_contraseña'] ?? '';
    $foto_perfil = $_FILES['imagen'] ?? null;

    $mensaje = array(); // aquí se almacenan los mensajes de alerta

    // Validar que todos los campos estén completos
    if (empty($nombre) || empty($usuario) || empty($correo) || empty($contraseña) || empty($confirma_contraseña)) {
        $mensaje[] = "Todos los campos son obligatorios.";
    } else {
        // Comprobando si las contraseñas coinciden
        if ($contraseña != $confirma_contraseña) {
            $mensaje[] = "Las contraseñas no coinciden.";
        } else {
            // Validaciones de seguridad de la contraseña
            if (strlen($contraseña) < 8) {
                $mensaje[] = "La contraseña debe tener al menos 8 caracteres.";
            } else if (!preg_match('/[a-z]+/', $contraseña)) {
                $mensaje[] = "La contraseña debe contener al menos una letra minúscula.";
            } else if (!preg_match('/[A-Z]+/', $contraseña)) {
                $mensaje[] = "La contraseña debe contener al menos una letra mayúscula.";
            } else if (!preg_match('/[0-9]+/', $contraseña)) {
                $mensaje[] = "La contraseña debe contener al menos un número.";
            } else if (!preg_match('/[!@#$%^&*()]+/', $contraseña)) {
                $mensaje[] = "La contraseña debe contener al menos un símbolo.";
            } else {
                // Comprobando si el correo electrónico ya existe
                $sql = "SELECT * FROM usuarios WHERE correo = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$correo]);
                $result = $stmt->fetchAll();

                if (count($result) > 0) {
                    $mensaje[] = "Este correo ya está registrado.";
                } else {
                    // Comprobando que el usuario ingresado no exista
                    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$usuario]);
                    $result = $stmt->fetchAll();

                    if (count($result) > 0) {
                        $mensaje[] = "Este usuario ya existe.";
                    } else {
                        // Encriptación de la contraseña
                        $password = password_hash($contraseña, PASSWORD_BCRYPT);

                        // Manejo de la imagen de perfil
                        if ($foto_perfil && $foto_perfil['error'] === 0) {
                            $img_name = $foto_perfil['name'];
                            $tmp_name = $foto_perfil['tmp_name'];
                            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
                            $img_ex_to_lc = strtolower($img_ex);

                            $allowed_exs = array('jpg', 'jpeg', 'png');
                            if (in_array($img_ex_to_lc, $allowed_exs)) {
                                // Asegurarse de que el nombre de la imagen sea único
                                $img_upload_path = '../asset/img/user/' . $img_name;
                                $counter = 1;
                                while (file_exists($img_upload_path)) {
                                    $img_name = pathinfo($img_name, PATHINFO_FILENAME) . "($counter)." . $img_ex_to_lc;
                                    $img_upload_path = '../asset/img/user/' . $img_name;
                                    $counter++;
                                }
                                
                                move_uploaded_file($tmp_name, $img_upload_path);

                                // URL de la imagen
                                $img_url = '../asset/img/user/' . $img_name;

                                // Insertar en la base de datos
                                $sql = "INSERT INTO usuarios (nombre, usuario, correo, contraseña, imagen) VALUES (?, ?, ?, ?, ?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$nombre, $usuario, $correo, $password, $img_url]);

                                $mensaje2[] = "<strong>¡Felicidades!</strong> Te has registrado en Notbookst.";
                            } else {
                                $mensaje[] = "Formato de imagen no permitido. Solo se permiten JPG, JPEG y PNG.";
                            }
                        } else {
                            // Insertar sin imagen de perfil
                            $sql = "INSERT INTO usuarios (nombre, usuario, correo, contraseña) VALUES (?, ?, ?, ?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$nombre, $usuario, $correo, $password]);

                            $mensaje2[] = "<strong>¡Felicidades!</strong> Te has registrado en Notbookst.";
                        }
                    }
                }
            }
        }
    }
}
?>
