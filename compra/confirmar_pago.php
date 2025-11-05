<?php
session_start();
include '../config/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Validar que se recibieron los datos del formulario anterior
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['asientos']) || !isset($_POST['id_viaje']) || !isset($_POST['total_pagar'])) {
    echo "Error: Faltan datos para procesar el pago.";
    header('Location: ../index.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_viaje = (int)$_POST['id_viaje'];
$total_pagar = (float)$_POST['total_pagar'];
$metodo_pago = $conn->real_escape_string($_POST['metodo_pago']);
$asientos_ids = $_POST['asientos']; // Array de IDs

// IDs de reservas para el boleto
$ids_reservas_creadas = [];
$id_pago_grupal = null; // Para agrupar los pagos

// Iniciar transacción: Si algo falla, todo se revierte.
$conn->begin_transaction();

try {
    // 1. Crear un PAGO principal para el total
    // (Ahora la tabla 'pagos' no tiene 'id_reserva')
    $sql_pago = "INSERT INTO pagos (monto, metodo_pago, estado) 
                 VALUES ($total_pagar, '$metodo_pago', 'completado')";
    
    if (!$conn->query($sql_pago)) {
        throw new Exception("Error al crear el pago principal: " . $conn->error);
    }
    $id_pago_grupal = $conn->insert_id; // ID del pago que agrupa todo

    // 2. Iterar sobre cada asiento y crear la reserva
    foreach ($asientos_ids as $id_asiento) {
        $id_asiento_int = (int)$id_asiento;
        
        // Llamar al SP corregido
        $call_sp = "CALL sp_crear_reserva($id_viaje, $id_usuario, $id_asiento_int, @out_id_reserva)";
        
        if (!$conn->query($call_sp)) {
             throw new Exception("Error al ejecutar sp_crear_reserva para asiento $id_asiento_int: " . $conn->error);
        }
        
        // Obtener el ID de reserva devuelto por el SP
        $res_sp = $conn->query("SELECT @out_id_reserva AS id_reserva");
        $row_sp = $res_sp->fetch_assoc();
        $id_reserva_nueva = $row_sp['id_reserva'];
        
        if ($id_reserva_nueva > 0) {
            $ids_reservas_creadas[] = $id_reserva_nueva;
            
            // 3. Actualizar la reserva para vincularla al pago principal
            // (Usando la nueva columna 'id_pago' en 'reservas')
            $sql_update_reserva = "UPDATE reservas SET id_pago = $id_pago_grupal WHERE id_reserva = $id_reserva_nueva";
            if (!$conn->query($sql_update_reserva)) {
                throw new Exception("Error al actualizar la reserva $id_reserva_nueva con el ID de pago: " . $conn->error);
            }

        } else {
            throw new Exception("El SP no devolvió un ID de reserva válido para el asiento $id_asiento_int.");
        }
    }

    // Si todo salió bien, confirmar la transacción
    $conn->commit();
    
    // 5. Redirigir a la página de éxito (boleto)
    // Pasamos el ID del pago grupal para que boleto.php sepa qué mostrar
    header("Location: boleto.php?pago_id=" . $id_pago_grupal);
    exit();

} catch (Exception $e) {
    // Si algo falló, revertir todo
    $conn->rollback();
    
    // Mostrar error
    echo "Error en la transacción: " . $e->getMessage();
    echo "<br><a href='../index.php'>Volver al inicio</a>";
}

$conn->close();

?>