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
    // Redirigir al inicio o mostrar un error más amigable
    header('Location: ../index.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_viaje = (int)$_POST['id_viaje'];
$total_pagar = (float)$_POST['total_pagar'];
$asientos_ids = $_POST['asientos']; // Esto es un array de IDs

// 1. Obtener información del viaje
$viaje_sql = "
    SELECT v.fecha_salida, r.origen, r.destino, b.tipo_servicio
    FROM viajes v
    JOIN rutas r ON v.id_ruta = r.id_ruta
    JOIN buses b ON v.id_bus = b.id_bus
    WHERE v.id_viaje = $id_viaje
";
$viaje_result = $conn->query($viaje_sql);
$viaje = $viaje_result->fetch_assoc();

// 2. Obtener información de los asientos seleccionados
$asientos_info = [];
if (count($asientos_ids) > 0) {
    // Escapar los IDs para seguridad
    $asientos_ids_seguros = array_map('intval', $asientos_ids);
    $ids_string = implode(',', $asientos_ids_seguros);
    
    $asientos_sql = "SELECT numero_asiento, piso, precio FROM asientos WHERE id_asiento IN ($ids_string)";
    $asientos_result = $conn->query($asientos_sql);
    while($asiento = $asientos_result->fetch_assoc()) {
        $asientos_info[] = $asiento;
    }
}

// 3. Obtener datos del usuario
$usuario_sql = "SELECT nombre, apellidos, dni, email, telefono FROM usuarios WHERE id_usuario = $id_usuario";
$usuario_result = $conn->query($usuario_sql);
$usuario = $usuario_result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Pago</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #004a99;
            text-align: center;
            margin-top: 0;
        }
        .resumen-seccion {
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .resumen-seccion h2 {
            color: #333;
            border-bottom: 2px solid #004a99;
            padding-bottom: 5px;
            font-size: 1.3em;
            margin-bottom: 10px;
        }
        .resumen-seccion p {
            margin: 5px 0;
            font-size: 1.1em;
            color: #555;
        }
        .resumen-seccion p strong {
            color: #333;
            width: 120px;
            display: inline-block;
        }
        .asientos-lista {
            list-style: none;
            padding-left: 0;
        }
        .asientos-lista li {
            font-size: 1.1em;
            padding: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .total-container {
            text-align: right;
            margin: 20px 0;
        }
        .total-container span {
            font-size: 1.8em;
            font-weight: 700;
            color: #d9534f;
        }
        
        /* Formulario de Pago Simulado */
        .payment-form {
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Importante para el padding */
            font-size: 1em;
        }
        .btn-confirmar {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #5cb85c; /* Verde éxito */
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
        }
        .btn-confirmar:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Confirmar y Pagar</h1>

        <!-- Formulario que enviará todo a confirmar_pago.php -->
        <form action="confirmar_pago.php" method="POST" class="payment-form">
            
            <!-- Datos Ocultos (Viaje, Asientos, Total) -->
            <input type="hidden" name="id_viaje" value="<?php echo $id_viaje; ?>">
            <input type="hidden" name="total_pagar" value="<?php echo $total_pagar; ?>">
            <?php foreach ($asientos_ids as $id): ?>
                <input type="hidden" name="asientos[]" value="<?php echo $id; ?>">
            <?php endforeach; ?>

            <!-- Sección 1: Datos del Pasajero -->
            <div class="resumen-seccion">
                <h2>Datos del Pasajero</h2>
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p>
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($usuario['dni']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            </div>

            <!-- Sección 2: Resumen del Viaje -->
            <div class="resumen-seccion">
                <h2>Resumen del Viaje</h2>
                <p><strong>Ruta:</strong> <?php echo htmlspecialchars($viaje['origen'] . ' - ' . $viaje['destino']); ?></p>
                <p><strong>Salida:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?></p>
                <p><strong>Servicio:</strong> <?php echo htmlspecialchars($viaje['tipo_servicio']); ?></p>
                
                <h3>Asientos Seleccionados</h3>
                <ul class="asientos-lista">
                    <?php foreach ($asientos_info as $asiento): ?>
                        <li>
                            <span>Asiento N° <?php echo $asiento['numero_asiento']; ?> (Piso <?php echo $asiento['piso']; ?>)</span>
                            <strong>S/ <?php echo number_format($asiento['precio'], 2); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Sección 3: Método de Pago (Simulado) -->
            <div class="resumen-seccion">
                <h2>Método de Pago (Simulado)</h2>
                
                <div class="form-group">
                    <label for="metodo_pago">Seleccionar Método</label>
                    <select id="metodo_pago" name="metodo_pago" class="form-group" style="width: 100%; padding: 10px; font-size: 1em;">
                        <option value="tarjeta">Tarjeta de Crédito/Débito (Simulado)</option>
                        <option value="transferencia">Transferencia Bancaria (Simulado)</option>
                        <option value="efectivo">Pago en Agencia (Simulado)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tarjeta_num">Número de Tarjeta (Simulado)</label>
                    <input type="text" id="tarjeta_num" name="tarjeta_num" value="4242 4242 4242 4242" required>
                </div>
                
            </div>

            <!-- Sección 4: Total -->
            <div class="total-container">
                <span>Total a Pagar: S/ <?php echo number_format($total_pagar, 2); ?></span>
            </div>

            <button type="submit" class="btn-confirmar">
                <span class="material-icons-outlined" style="vertical-align: middle;">lock</span>
                Confirmar y Pagar
            </button>
        </form>

    </div>

</body>
</html>