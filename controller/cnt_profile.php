<?php

require '../model/conexion.php';

$mensaje = array(); // aquí se almacenan los mensajes de alerta

if (isset($_SESSION['id']) && isset($_SESSION['nombre'])) {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $old_pp = $_POST['old_pp'] ?? '';
        $id = $_SESSION['id'];

        if (empty($nombre)) {
            $mensaje[] = "El nombre completo es obligatorio";
        } else if (empty($usuario)) {
            $mensaje[] = "El nombre de usuario es obligatorio";
        } else {
            if (isset($_FILES['imagen']['name']) && !empty($_FILES['imagen']['name'])) {
                $img_name = $_FILES['imagen']['name'];
                $tmp_name = $_FILES['imagen']['tmp_name'];
                $error = $_FILES['imagen']['error'];

                if ($error === 0) {
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

                        // Delete old profile pic if exists
                        $old_pp_des = "../asset/img/user/$old_pp";
                        if (file_exists($old_pp_des)) {
                            unlink($old_pp_des);
                        }

                        // Update the Database
                        $sql = "UPDATE usuarios SET nombre=?, usuario=?, imagen=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$nombre, $usuario, $img_url, $id]);

                        $mensaje[] = "<strong>¡Éxito!</strong> Tu cuenta ha sido actualizada exitosamente";
                        $_SESSION['nombre'] = $nombre;
                    } else {
                        $mensaje[] = "No puedes subir archivos de este tipo";
                    }
                } else {
                    $mensaje[] = "¡Ocurrió un error desconocido!";
                }
            } else {
                // If no new profile picture is uploaded
                $sql = "UPDATE usuarios SET nombre=?, usuario=? WHERE id=?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombre, $usuario, $id]);

                $mensaje[] = "<strong>¡Éxito!</strong> Tu cuenta ha sido actualizada exitosamente";
                $_SESSION['nombre'] = $nombre;
            }
        }
    } else {
        $mensaje[] = "Datos inválidos";
    }
} else {
    $mensaje[] = "No estás autenticado. Por favor, inicia sesión.";
}
?>



