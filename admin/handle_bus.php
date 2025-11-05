<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Ajuste de ruta para salir de 'admin'

// --- Verificación de Admin ---
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../auth/login.php?msg=error_auth');
    exit;
}

// --- Lógica para AÑADIR (CREATE) ---
if (isset($_POST['add_bus'])) {
    $placa = $conn->real_escape_string($_POST['placa']);
    $modelo = $conn->real_escape_string($_POST['modelo']);
    $tipo_servicio = $conn->real_escape_string($_POST['tipo_servicio']);
    $cap1 = (int)$_POST['capacidad_piso1'];
    $cap2 = empty($_POST['capacidad_piso2']) ? NULL : (int)$_POST['capacidad_piso2'];
    $año = empty($_POST['año_fabricacion']) ? NULL : (int)$_POST['año_fabricacion'];
    $estado = $conn->real_escape_string($_POST['estado']);

    $stmt = $conn->prepare("INSERT INTO buses (placa, modelo, tipo_servicio, capacidad_piso1, capacidad_piso2, año_fabricacion, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiss", $placa, $modelo, $tipo_servicio, $cap1, $cap2, $año, $estado);

    if ($stmt->execute()) {
        header('Location: gestionar_buses.php?msg=success_add');
    } else {
        header('Location: gestionar_buses.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para EDITAR (UPDATE) ---
elseif (isset($_POST['edit_bus'])) {
    $id_bus = (int)$_POST['id_bus'];
    $placa = $conn->real_escape_string($_POST['placa']);
    $modelo = $conn->real_escape_string($_POST['modelo']);
    $tipo_servicio = $conn->real_escape_string($_POST['tipo_servicio']);
    $cap1 = (int)$_POST['capacidad_piso1'];
    $cap2 = empty($_POST['capacidad_piso2']) ? NULL : (int)$_POST['capacidad_piso2'];
    $año = empty($_POST['año_fabricacion']) ? NULL : (int)$_POST['año_fabricacion'];
    $estado = $conn->real_escape_string($_POST['estado']);

    $stmt = $conn->prepare("UPDATE buses SET placa = ?, modelo = ?, tipo_servicio = ?, capacidad_piso1 = ?, capacidad_piso2 = ?, año_fabricacion = ?, estado = ? WHERE id_bus = ?");
    $stmt->bind_param("sssiissi", $placa, $modelo, $tipo_servicio, $cap1, $cap2, $año, $estado, $id_bus);

    if ($stmt->execute()) {
        header('Location: gestionar_buses.php?msg=success_edit');
    } else {
        header('Location: gestionar_buses.php?msg=error');
    }
    $stmt->close();
}

// --- Lógica para ELIMINAR (DELETE) ---
elseif (isset($_GET['delete_id'])) {
    $id_bus = (int)$_GET['delete_id'];

    // PRECAUCIÓN: Deberías verificar si este bus está en uso en la tabla 'viajes' antes de borrarlo.
    // Por simplicidad, aquí lo borramos directamente.
    
    $stmt = $conn->prepare("DELETE FROM buses WHERE id_bus = ?");
    $stmt->bind_param("i", $id_bus);

    if ($stmt->execute()) {
        header('Location: gestionar_buses.php?msg=success_delete');
    } else {
        // Error (probablemente por clave foránea en 'viajes')
        header('Location: gestionar_buses.php?msg=error_delete_fk');
    }
    $stmt->close();
}

// Si no se cumple ninguna condición, redirigir
else {
    header('Location: gestionar_buses.php');
}

$conn->close();
?>