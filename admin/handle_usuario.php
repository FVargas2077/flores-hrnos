<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Ajuste de ruta

// --- Verificación de Admin ---
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../auth/login.php?msg=error_auth');
    exit;
}

// --- Función para verificar duplicados ---
function verificarDuplicado($conn, $campo, $valor, $id_actual = 0) {
    $sql = "SELECT id_usuario FROM usuarios WHERE $campo = ? AND id_usuario != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $valor, $id_actual);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// --- Lógica para AÑADIR (CREATE) ---
if (isset($_POST['add_usuario'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $dni = $conn->real_escape_string($_POST['dni']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']); // NOTA: Deberías hashear esto.
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $id_rol = (int)$_POST['id_rol'];
    $estado = (int)$_POST['estado'];

    // Verificar duplicados
    if (verificarDuplicado($conn, 'dni', $dni)) {
        header('Location: gestionar_usuarios.php?msg=error_dni'); exit;
    }
    if (verificarDuplicado($conn, 'email', $email)) {
        header('Location: gestionar_usuarios.php?msg=error_email'); exit;
    }

    $stmt = $conn->prepare("INSERT INTO usuarios (id_rol, nombre, apellidos, dni, email, password, telefono, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $id_rol, $nombre, $apellidos, $dni, $email, $password, $telefono, $estado);

    if ($stmt->execute()) {
        header('Location: gestionar_usuarios.php?msg=success_add');
    } else {
        header('Location: gestionar_usuarios.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para EDITAR (UPDATE) ---
elseif (isset($_POST['edit_usuario'])) {
    $id_usuario = (int)$_POST['id_usuario'];
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $dni = $conn->real_escape_string($_POST['dni']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $id_rol = (int)$_POST['id_rol'];
    $estado = (int)$_POST['estado'];

    // Verificar duplicados
    if (verificarDuplicado($conn, 'dni', $dni, $id_usuario)) {
        header('Location: gestionar_usuarios.php?msg=error_dni'); exit;
    }
    if (verificarDuplicado($conn, 'email', $email, $id_usuario)) {
        header('Location: gestionar_usuarios.php?msg=error_email'); exit;
    }

    // Actualizar contraseña SOLO si se proporcionó una nueva
    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE usuarios SET id_rol = ?, nombre = ?, apellidos = ?, dni = ?, email = ?, password = ?, telefono = ?, estado = ? WHERE id_usuario = ?");
        $stmt->bind_param("issssssii", $id_rol, $nombre, $apellidos, $dni, $email, $password, $telefono, $estado, $id_usuario);
    } else {
        // No actualizar la contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET id_rol = ?, nombre = ?, apellidos = ?, dni = ?, email = ?, telefono = ?, estado = ? WHERE id_usuario = ?");
        $stmt->bind_param("isssssii", $id_rol, $nombre, $apellidos, $dni, $email, $telefono, $estado, $id_usuario);
    }

    if ($stmt->execute()) {
        header('Location: gestionar_usuarios.php?msg=success_edit');
    } else {
        header('Location: gestionar_usuarios.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para ELIMINAR (DELETE) ---
elseif (isset($_GET['delete_id'])) {
    $id_usuario = (int)$_GET['delete_id'];

    // ¡Regla de seguridad! No permitir que un admin se borre a sí mismo.
    if ($id_usuario == $_SESSION['id_usuario']) {
        header('Location: gestionar_usuarios.php?msg=error_delete_self');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        header('Location: gestionar_usuarios.php?msg=success_delete');
    } else {
        header('Location: gestionar_usuarios.php?msg=error');
    }
    $stmt->close();
}

else {
    header('Location: gestionar_usuarios.php');
}

$conn->close();
?>