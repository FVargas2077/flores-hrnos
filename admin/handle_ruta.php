<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// --- Verificación de Admin ---
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../auth/login.php?msg=error_auth');
    exit;
}

// --- Lógica para AÑADIR (CREATE) ---
if (isset($_POST['add_ruta'])) {
    $origen = $conn->real_escape_string($_POST['origen']);
    $destino = $conn->real_escape_string($_POST['destino']);
    $distancia = empty($_POST['distancia']) ? NULL : (float)$_POST['distancia'];
    $duracion = empty($_POST['duracion_estimada']) ? NULL : $conn->real_escape_string($_POST['duracion_estimada']);
    $precio = empty($_POST['precio_base']) ? NULL : (float)$_POST['precio_base'];

    $stmt = $conn->prepare("INSERT INTO rutas (origen, destino, distancia, duracion_estimada, precio_base) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdds", $origen, $destino, $distancia, $duracion, $precio);

    if ($stmt->execute()) {
        header('Location: gestionar_rutas.php?msg=success_add');
    } else {
        header('Location: gestionar_rutas.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para EDITAR (UPDATE) ---
elseif (isset($_POST['edit_ruta'])) {
    $id_ruta = (int)$_POST['id_ruta'];
    $origen = $conn->real_escape_string($_POST['origen']);
    $destino = $conn->real_escape_string($_POST['destino']);
    $distancia = empty($_POST['distancia']) ? NULL : (float)$_POST['distancia'];
    $duracion = empty($_POST['duracion_estimada']) ? NULL : $conn->real_escape_string($_POST['duracion_estimada']);
    $precio = empty($_POST['precio_base']) ? NULL : (float)$_POST['precio_base'];

    $stmt = $conn->prepare("UPDATE rutas SET origen = ?, destino = ?, distancia = ?, duracion_estimada = ?, precio_base = ? WHERE id_ruta = ?");
    $stmt->bind_param("ssddsi", $origen, $destino, $distancia, $duracion, $precio, $id_ruta);

    if ($stmt->execute()) {
        header('Location: gestionar_rutas.php?msg=success_edit');
    } else {
        header('Location: gestionar_rutas.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para ELIMINAR (DELETE) ---
elseif (isset($_GET['delete_id'])) {
    $id_ruta = (int)$_GET['delete_id'];
    
    $stmt = $conn->prepare("DELETE FROM rutas WHERE id_ruta = ?");
    $stmt->bind_param("i", $id_ruta);

    if ($stmt->execute()) {
        header('Location: gestionar_rutas.php?msg=success_delete');
    } else {
        // Error (probablemente por clave foránea en 'viajes')
        header('Location: gestionar_rutas.php?msg=error_delete_fk');
    }
    $stmt->close();
}

else {
    header('Location: gestionar_rutas.php');
}

$conn->close();
?>