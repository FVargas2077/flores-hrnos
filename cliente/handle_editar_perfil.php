<?php
session_start();
include '../config/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: index.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// 1. Recoger y sanear datos personales
$nombre = $conn->real_escape_string($_POST['nombre']);
$apellidos = $conn->real_escape_string($_POST['apellidos']);
$email = $conn->real_escape_string($_POST['email']);
$telefono = $conn->real_escape_string($_POST['telefono']);
$direccion = $conn->real_escape_string($_POST['direccion']);
$fecha_nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento']);

// Validar campos vacíos
if (empty($nombre) || empty($apellidos) || empty($email)) {
    $_SESSION['mensaje_error'] = "Nombre, Apellidos y Email son campos obligatorios.";
    header('Location: editar_perfil.php');
    exit();
}

// 2. Construir la consulta de actualización de datos personales
// (DNI no se incluye, no se puede cambiar)
$sql_update = "UPDATE usuarios SET 
                    nombre = '$nombre',
                    apellidos = '$apellidos',
                    email = '$email',
                    telefono = '$telefono',
                    direccion = '$direccion',
                    fecha_nacimiento = '$fecha_nacimiento'
               WHERE id_usuario = $id_usuario";

if (!$conn->query($sql_update)) {
    $_SESSION['mensaje_error'] = "Error al actualizar los datos personales: " . $conn->error;
    header('Location: editar_perfil.php');
    exit();
}

// 3. Manejar cambio de contraseña (si se proporcionó)
$password_actual = $_POST['password_actual'];
$password_nueva = $_POST['password_nueva'];

if (!empty($password_actual) && !empty($password_nueva)) {
    // Verificar que la contraseña actual sea correcta
    // (Tu BD tiene passwords en texto plano (ej. '123'), así que comparamos directo)
    $sql_pass_check = "SELECT password FROM usuarios WHERE id_usuario = $id_usuario";
    $result_pass = $conn->query($sql_pass_check);
    $row_pass = $result_pass->fetch_assoc();
    
    if ($row_pass['password'] === $password_actual) {
        // La contraseña actual coincide, actualizar a la nueva
        $sql_pass_update = "UPDATE usuarios SET password = '$password_nueva' WHERE id_usuario = $id_usuario";
        if (!$conn->query($sql_pass_update)) {
            $_SESSION['mensaje_error'] = "Error al actualizar la contraseña: " . $conn->error;
            header('Location: editar_perfil.php');
            exit();
        }
    } else {
        // La contraseña actual no coincide
        $_SESSION['mensaje_error'] = "La 'Contraseña Actual' es incorrecta. No se pudo cambiar la contraseña.";
        header('Location: editar_perfil.php');
        exit();
    }
}

// 4. Redirigir al perfil con mensaje de éxito
$_SESSION['mensaje_exito'] = "¡Perfil actualizado correctamente!";
header('Location: index.php');
exit();

?>