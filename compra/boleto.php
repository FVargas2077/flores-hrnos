<?php
session_start();
include '../config/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Validar que se recibió un ID de pago
if (!isset($_GET['pago_id'])) {
    header('Location: ../cliente/index.php'); // Redirigir al perfil si no hay ID
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_pago_grupal = (int)$_GET['pago_id'];

// 1. Obtener la información del pago
$pago_sql = "SELECT monto, metodo_pago, fecha_pago FROM pagos WHERE id_pago = $id_pago_grupal AND estado = 'completado'";
$pago_result = $conn->query($pago_sql);
if ($pago_result->num_rows == 0) {
    echo "Error: Pago no encontrado o no completado.";
    exit();
}
$pago = $pago_result->fetch_assoc();

// 2. Obtener los datos del usuario (Pasajero)
$usuario_sql = "SELECT nombre, apellidos, dni, email FROM usuarios WHERE id_usuario = $id_usuario";
$usuario_result = $conn->query($usuario_sql);
$usuario = $usuario_result->fetch_assoc();

// 3. Obtener TODAS las reservas (boletos) asociadas a este pago
$boletos_sql = "
    SELECT 
        res.id_reserva, res.precio_final,
        a.numero_asiento, a.piso,
        v.fecha_salida,
        r.origen, r.destino,
        b.tipo_servicio, b.placa
    FROM reservas res
    JOIN asientos a ON res.id_asiento = a.id_asiento
    JOIN viajes v ON res.id_viaje = v.id_viaje
    JOIN rutas r ON v.id_ruta = r.id_ruta
    JOIN buses b ON v.id_bus = b.id_bus
    WHERE res.id_pago = $id_pago_grupal 
      AND res.id_usuario = $id_usuario
      AND res.estado = 'confirmada'
";
$boletos_result = $conn->query($boletos_sql);

if ($boletos_result->num_rows == 0) {
    echo "Error: No se encontraron boletos asociados a este pago.";
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Exitosa - Boleto</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            width: 100%;
            max-width: 800px; /* Más ancho para los boletos */
        }
        .success-header {
            background: #5cb85c; /* Verde éxito */
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .success-header h1 {
            margin: 0;
            font-size: 2em;
        }
        .success-header p {
            font-size: 1.2em;
            margin: 5px 0 0;
        }
        .payment-summary {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .payment-summary h2 {
            margin-top: 0;
            color: #333;
        }
        .payment-summary p {
            font-size: 1.1em;
            color: #555;
            display: flex;
            justify-content: space-between;
        }
        
        /* Estilo de Boleto Individual */
        .boleto {
            background: #fff;
            border: 1px solid #ddd;
            border-left: 10px solid #004a99; /* Borde azul */
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .boleto-header {
            background-color: #f9f9f9;
            padding: 15px 20px;
            border-bottom: 1px dashed #ccc;
        }
        .boleto-header h2 {
            margin: 0;
            color: #004a99;
        }
        .boleto-header span {
            font-size: 1.1em;
            font-weight: bold;
            color: #d9534f;
        }
        .boleto-body {
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .boleto-seccion h3 {
            font-size: 1em;
            color: #888;
            text-transform: uppercase;
            margin: 0 0 5px 0;
        }
        .boleto-seccion p {
            font-size: 1.1em;
            color: #333;
            font-weight: bold;
            margin: 0;
        }
        .actions {
            text-align: center;
            padding: 20px 0;
        }
        .btn {
            background-color: #004a99;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
        }
        .btn-print {
            background-color: #f0ad4e; /* Naranja */
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="success-header">
            <span class="material-icons-outlined" style="font-size: 48px;">check_circle</span>
            <h1>¡Compra Exitosa!</h1>
            <p>Tus boletos han sido generados. Gracias por preferirnos.</p>
        </div>

        <div class="payment-summary">
            <h2>Resumen del Pago (ID: <?php echo $id_pago_grupal; ?>)</h2>
            <p><span>Monto Total Pagado:</span> <strong>S/ <?php echo number_format($pago['monto'], 2); ?></strong></p>
            <p><span>Método de Pago:</span> <strong><?php echo htmlspecialchars(ucfirst($pago['metodo_pago'])); ?></strong></p>
            <p><span>Fecha de Pago:</span> <strong><?php echo date('d/m/Y H:i:s', strtotime($pago['fecha_pago'])); ?></strong></p>
        </div>

        <!-- Iterar y mostrar cada boleto comprado -->
        <?php while($boleto = $boletos_result->fetch_assoc()): ?>
        <div class="boleto">
            <div class="boleto-header">
                <h2>Boleto N°: <?php echo str_pad($boleto['id_reserva'], 6, '0', STR_PAD_LEFT); ?></h2>
                <span>Precio: S/ <?php echo number_format($boleto['precio_final'], 2); ?></span>
            </div>
            <div class="boleto-body">
                <div class="boleto-seccion">
                    <h3>Pasajero</h3>
                    <p><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p>
                </div>
                <div class="boleto-seccion">
                    <h3>DNI</h3>
                    <p><?php echo htmlspecialchars($usuario['dni']); ?></p>
                </div>
                <div class="boleto-seccion">
                    <h3>Ruta</h3>
                    <p><?php echo htmlspecialchars($boleto['origen'] . ' - ' . $boleto['destino']); ?></p>
                </div>
                <div class="boleto-seccion">
                    <h3>Fecha y Hora de Salida</h3>
                    <p><?php echo date('d/m/Y H:i', strtotime($boleto['fecha_salida'])); ?></p>
                </div>
                <div class="boleto-seccion">
                    <h3>Asiento</h3>
                    <p>N° <?php echo $boleto['numero_asiento']; ?> (Piso <?php echo $boleto['piso']; ?>)</p>
                </div>
                <div class="boleto-seccion">
                    <h3>Servicio</h3>
                    <p><?php echo htmlspecialchars($boleto['tipo_servicio']); ?> (Placa: <?php echo $boleto['placa']; ?>)</p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

        <div class="actions">
            <a href="../cliente/index.php" class="btn">Ir a Mi Perfil</a>
            <a href="javascript:window.print()" class="btn btn-print">
                <span class="material-icons-outlined" style="vertical-align: middle; font-size: 1.2em;">print</span>
                Imprimir Boletos
            </a>
        </div>

    </div>

</body>
</html>