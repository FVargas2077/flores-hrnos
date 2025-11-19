<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// --- Verificación de Admin ---
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../auth/login.php?msg=error_auth');
    exit;
}

// --- Lógica para AÑADIR (CREATE) ---
if (isset($_POST['add_viaje'])) {
    $id_ruta = (int)$_POST['id_ruta'];
    $id_bus = (int)$_POST['id_bus'];
    $fecha_salida = $conn->real_escape_string($_POST['fecha_salida']);
    $fecha_llegada = $conn->real_escape_string($_POST['fecha_llegada']);
    $estado = $conn->real_escape_string($_POST['estado']);
    
    // Opcional: capturar conductor y copiloto si se añadieron
    $id_conductor = NULL; // (int)$_POST['id_conductor'];
    $id_copiloto = NULL; // (int)$_POST['id_copiloto'];

    $stmt = $conn->prepare("INSERT INTO viajes (id_ruta, id_bus, id_conductor, id_copiloto, fecha_salida, fecha_llegada, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiisss", $id_ruta, $id_bus, $id_conductor, $id_copiloto, $fecha_salida, $fecha_llegada, $estado);

    if ($stmt->execute()) {
        header('Location: gestionar_viajes.php?msg=success_add');
    } else {
        header('Location: gestionar_viajes.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para ELIMINAR (DELETE) ---
elseif (isset($_GET['delete_id'])) {
    $id_viaje = (int)$_GET['delete_id'];
    
    // Idealmente, verificar si hay reservas antes de borrar.
    
    $stmt = $conn->prepare("DELETE FROM viajes WHERE id_viaje = ?");
    $stmt->bind_param("i", $id_viaje);

    if ($stmt->execute()) {
        header('Location: gestionar_viajes.php?msg=success_delete');
    } else {
        header('Location: gestionar_viajes.php?msg=error_delete_fk');
    }
    $stmt->close();
}

else {
    header('Location: gestionar_viajes.php');
}

$conn->close();
?>