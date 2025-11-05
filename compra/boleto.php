<?php
// compra/boleto.php
include '../config/db.php';

// --- Control de Acceso y Sesión ---
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['reservas_exitosas'])) {
    header('Location: ../index.php');
    exit;
}

$ids_reservas = $_SESSION['reservas_exitosas'];
$id_usuario = $_SESSION['id_usuario'];

// --- Obtener datos del Pasajero ---
$sql_pasajero = "SELECT nombre, apellidos, dni FROM usuarios WHERE id_usuario = $id_usuario";
$pasajero = $conn->query($sql_pasajero)->fetch_assoc();

// --- Obtener datos de las Reservas ---
$placeholders = implode(',', array_fill(0, count($ids_reservas), '?'));
$sql_boletos = "SELECT 
    res.id_reserva, res.precio_final,
    v.fecha_salida, v.fecha_llegada,
    r.origen, r.destino,
    b.placa, b.tipo_servicio,
    a.numero_asiento, a.piso
FROM reservas res
JOIN viajes v ON res.id_viaje = v.id_viaje
JOIN rutas r ON v.id_ruta = r.id_ruta
JOIN buses b ON v.id_bus = b.id_bus
JOIN asientos a ON res.id_asiento = a.id_asiento
WHERE res.id_reserva IN ($placeholders)";

$stmt = $conn->prepare($sql_boletos);
$stmt->bind_param(str_repeat('i', count($ids_reservas)), ...$ids_reservas);
$stmt->execute();
$boletos_res = $stmt->get_result();
$boletos = [];
while ($row = $boletos_res->fetch_assoc()) {
    $boletos[] = $row;
}

// Limpiar la sesión para que no se muestre este boleto de nuevo al recargar
unset($_SESSION['reservas_exitosas']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Compra Exitosa! - Tu Boleto</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 2em 0; }
        .container { background-color: white; padding: 2em; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 800px; text-align: center; }
        h1 { color: #4CAF50; margin-top: 0; }
        .boleto-card { border: 2px dashed #004a99; border-radius: 8px; padding: 1.5em; margin-top: 1.5em; text-align: left; }
        .boleto-header { display: flex; justify-content: space-between; border-bottom: 2px solid #f0f0f0; padding-bottom: 1em; }
        .boleto-header h2 { color: #004a99; margin: 0; }
        .boleto-body { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5em; padding: 1.5em 0; }
        .detalle strong { display: block; color: #555; font-size: 0.9em; margin-bottom: 0.3em; }
        .detalle span { font-size: 1.1em; color: #000; }
        .detalle-asiento { background-color: #f9f9f9; padding: 1em; border-radius: 5px; grid-column: 1 / -1; }
        .links-footer { margin-top: 2em; }
        .links-footer a { background-color: #004a99; color: white; padding: 0.8em 1.5em; text-decoration: none; border-radius: 5px; margin: 0 0.5em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Compra Exitosa!</h1>
        <p>Tu reserva ha sido confirmada. Gracias por viajar con nosotros.</p>

        <?php foreach ($boletos as $boleto): ?>
        <div class="boleto-card">
            <div class="boleto-header">
                <h2>Boleto N°: <?php echo str_pad($boleto['id_reserva'], 6, '0', STR_PAD_LEFT); ?></h2>
                <strong>S/ <?php echo number_format($boleto['precio_final'], 2); ?></strong>
            </div>
            
            <div class="boleto-body">
                <div class="detalle">
                    <strong>Pasajero:</strong>
                    <span><?php echo htmlspecialchars($pasajero['nombre'] . ' ' . $pasajero['apellidos']); ?></span>
                </div>
                <div class="detalle">
                    <strong>DNI:</strong>
                    <span><?php echo htmlspecialchars($pasajero['dni']); ?></span>
                </div>
                <div class="detalle">
                    <strong>Ruta:</strong>
                    <span><?php echo htmlspecialchars($boleto['origen'] . ' a ' . $boleto['destino']); ?></span>
                </div>
                <div class="detalle">
                    <strong>Servicio:</strong>
                    <span><?php echo htmlspecialchars($boleto['tipo_servicio']); ?> (Placa: <?php echo $boleto['placa']; ?>)</span>
                </div>
                <div class="detalle">
                    <strong>Salida:</strong>
                    <span><?php echo date('d/m/Y H:i', strtotime($boleto['fecha_salida'])); ?></span>
                </div>
                <div class="detalle">
                    <strong>Llegada Aprox:</strong>
                    <span><?php echo date('d/m/Y H:i', strtotime($boleto['fecha_llegada'])); ?></span>
                </div>
                <div class="detalle-asiento">
                    <strong>Asiento:</strong>
                    <span>N° <?php echo $boleto['numero_asiento']; ?> (Piso <?php echo $boleto['piso']; ?>)</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="links-footer">
            <a href="../cliente/index.php">Ir a Mi Perfil</a>
            <a href="../index.php">Comprar otro pasaje</a>
        </div>
    </div>
</body>
</html>
