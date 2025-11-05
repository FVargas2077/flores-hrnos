<?php
// compra/confirmar_pago.php
include '../config/db.php';

// --- Control de Acceso y Sesión ---
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['compra_actual'])) {
    header('Location: ../index.php');
    exit;
}

// Recuperar datos de la sesión
$id_usuario = $_SESSION['id_usuario'];
$compra = $_SESSION['compra_actual'];
$id_viaje = $compra['id_viaje'];
$id_asientos = $compra['id_asientos']; // Array
$total_pagado = $compra['total'];

$conn->autocommit(FALSE); // Iniciar transacción
$errores = [];
$ids_reservas_creadas = [];

// --- 1. Crear Reservas (usando el SP) ---
// Preparamos el statement para llamar al SP
$stmt_reserva = $conn->prepare("CALL sp_crear_reserva(?, ?, ?, @p_id_reserva)");

if ($stmt_reserva === false) {
    $errores[] = "Error al preparar el SP de reserva: " . $conn->error;
} else {
    foreach ($id_asientos as $id_asiento) {
        // Asignamos los parámetros IN
        $stmt_reserva->bind_param("iii", $id_viaje, $id_usuario, $id_asiento);
        
        if (!$stmt_reserva->execute()) {
            // El SP puede fallar si el asiento ya está ocupado (controlado por el TRIGGER)
            $errores[] = "Error al reservar asiento $id_asiento: " . $stmt_reserva->error;
        } else {
            // Obtener el ID de reserva (parámetro OUT)
            $result_sp = $conn->query("SELECT @p_id_reserva as id_reserva_creada;");
            $id_reserva_nueva = $result_sp->fetch_assoc()['id_reserva_creada'];
            
            if ($id_reserva_nueva > 0) {
                $ids_reservas_creadas[] = $id_reserva_nueva;
            } else {
                $errores[] = "No se pudo obtener el ID de la reserva para el asiento $id_asiento.";
            }
        }
    }
    $stmt_reserva->close();
}

// --- 2. Crear Pagos (Simulados) ---
// Asumimos un solo pago por el total de todas las reservas
$id_reserva_para_pago = $ids_reservas_creadas[0] ?? null; // Usamos la primera reserva para asociar el pago

if (empty($errores) && $id_reserva_para_pago) {
    
    // Insertamos un PAGO general
    // (En un sistema real, podrías crear un pago por CADA reserva)
    $sql_pago = "INSERT INTO pagos (id_reserva, monto, metodo_pago, estado) VALUES (?, ?, 'tarjeta', 'completado')";
    $stmt_pago = $conn->prepare($sql_pago);
    
    if ($stmt_pago) {
        $stmt_pago->bind_param("id", $id_reserva_para_pago, $total_pagado);
        if (!$stmt_pago->execute()) {
            $errores[] = "Error al registrar el pago: " . $stmt_pago->error;
        }
        $stmt_pago->close();
    } else {
        $errores[] = "Error al preparar el pago: " . $conn->error;
    }
}

// --- 3. Confirmar o Revertir Transacción ---
if (empty($errores)) {
    // ¡Éxito!
    $conn->commit();
    
    // Limpiar sesión de compra
    unset($_SESSION['compra_actual']);
    
    // Guardar IDs de reserva para mostrar el boleto
    $_SESSION['reservas_exitosas'] = $ids_reservas_creadas;
    
    // Redirigir a la página del boleto
    header('Location: boleto.php');
    exit;

} else {
    // ¡Error! Revertir todo
    $conn->rollback();
    
    // Mostrar error (en una app real, una página de error)
    echo "<h3>Ocurrió un error al procesar tu compra:</h3>";
    echo "<ul>";
    foreach ($errores as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul>";
    echo "<p>Por favor, <a href='../index.php'>inténtalo de nuevo</a>. (Los asientos seleccionados pueden ya no estar disponibles)</p>";
    
    // Limpiar sesión de compra
    unset($_SESSION['compra_actual']);
}

$conn->autocommit(TRUE); // Restaurar autocommit
?>
