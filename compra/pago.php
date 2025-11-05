<?php
// compra/pago.php
include '../config/db.php';

// --- Control de Acceso ---
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error_login'] = "Debes iniciar sesión para pagar.";
    header('Location: ../auth/login.php');
    exit;
}
if (!isset($_POST['asientos']) || empty($_POST['asientos']) || !isset($_POST['id_viaje'])) {
    // Si no hay asientos o viaje, redirigir
    header('Location: ../index.php');
    exit;
}

$id_viaje = (int)$_POST['id_viaje'];
$id_asientos_seleccionados = $_POST['asientos']; // Esto es un array

// --- Obtener datos del Viaje ---
$sql_viaje = "SELECT v.fecha_salida, r.origen, r.destino, b.tipo_servicio
FROM viajes v
JOIN rutas r ON v.id_ruta = r.id_ruta
JOIN buses b ON v.id_bus = b.id_bus
WHERE v.id_viaje = $id_viaje";
$viaje = $conn->query($sql_viaje)->fetch_assoc();

// --- Obtener detalle de asientos y calcular total ---
$total_a_pagar = 0;
$asientos_detalle = [];
$placeholders = implode(',', array_fill(0, count($id_asientos_seleccionados), '?')); // para "IN (?, ?, ?)"

$sql_asientos = "SELECT id_asiento, numero_asiento, piso, precio 
                 FROM asientos 
                 WHERE id_asiento IN ($placeholders)";

$stmt = $conn->prepare($sql_asientos);
// 's' * count(...) genera 'sss...'
$stmt->bind_param(str_repeat('i', count($id_asientos_seleccionados)), ...$id_asientos_seleccionados);
$stmt->execute();
$result_asientos = $stmt->get_result();

while ($row = $result_asientos->fetch_assoc()) {
    $asientos_detalle[] = $row;
    $total_a_pagar += $row['precio'];
}

// Guardar en sesión para el siguiente paso
$_SESSION['compra_actual'] = [
    'id_viaje' => $id_viaje,
    'id_asientos' => $id_asientos_seleccionados,
    'total' => $total_a_pagar
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pago</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .pago-container { background-color: white; padding: 2em; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 600px; }
        h1 { color: #004a99; border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5em; }
        .resumen-viaje, .resumen-pago { margin-bottom: 1.5em; }
        .resumen-viaje p, .resumen-pago p { margin: 0.5em 0; font-size: 1.1em; }
        .resumen-viaje strong { color: #333; }
        .asientos-lista { list-style: none; padding: 0; }
        .asientos-lista li { display: flex; justify-content: space-between; padding: 0.5em 0; border-bottom: 1px dashed #ccc; }
        .total { font-size: 1.5em; font-weight: bold; color: #d9534f; display: flex; justify-content: space-between; margin-top: 1em; }
        .metodo-pago h3 { margin-bottom: 0.5em; }
        .metodo-pago .info-simulacion { background-color: #f0f8ff; border: 1px solid #b6dfff; padding: 1em; border-radius: 5px; color: #0056b3; }
        .form-pago { margin-top: 1em; }
        .form-pago label { display: block; margin-bottom: 0.5em; font-weight: bold; }
        .form-pago input { width: 100%; padding: 0.8em; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 1em; }
        .form-pago button { width: 100%; padding: 0.8em; background-color: #d9534f; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1.2em; }
        .form-pago button:hover { background-color: #c9302c; }
    </style>
</head>
<body>
    <div class="pago-container">
        <h1>Confirmar Compra</h1>
        
        <div class="resumen-viaje">
            <h3>Datos del Viaje</h3>
            <p><strong>Ruta:</strong> <?php echo htmlspecialchars($viaje['origen'] . ' a ' . $viaje['destino']); ?></p>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?></p>
            <p><strong>Servicio:</strong> <?php echo htmlspecialchars($viaje['tipo_servicio']); ?></p>
        </div>

        <div class="resumen-pago">
            <h3>Resumen de Asientos</h3>
            <ul class="asientos-lista">
                <?php foreach ($asientos_detalle as $asiento): ?>
                    <li>
                        <span>Asiento N° <?php echo $asiento['numero_asiento']; ?> (Piso <?php echo $asiento['piso']; ?>)</span>
                        <strong>S/ <?php echo number_format($asiento['precio'], 2); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total">
                <span>TOTAL A PAGAR:</span>
                <span>S/ <?php echo number_format($total_a_pagar, 2); ?></span>
            </div>
        </div>
        
        <div class="metodo-pago">
            <h3>Método de Pago (Simulado)</h3>
            <div class="info-simulacion">
                <p>Esto es una simulación. No se requiere información real. Al hacer clic en "Confirmar", la compra se procesará.</p>
            </div>
            
            <form action="confirmar_pago.php" method="POST" class="form-pago">
                <!-- Campos simulados -->
                <label for="tarjeta">N° de Tarjeta (Simulada)</label>
                <input type="text" id="tarjeta" value="4242 4242 4242 4242" disabled>
                
                <label for="nombre">Nombre (Simulado)</label>
                <input type="text" id="nombre" value="<?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>" disabled>

                <button type="submit">Confirmar Compra</button>
            </form>
        </div>
    </div>
</body>
</html>
