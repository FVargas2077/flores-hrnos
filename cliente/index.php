<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id_usuario = (int)$_SESSION['id_usuario'];

// 1. Obtener datos del perfil del usuario
$usuario_sql = "SELECT nombre, apellidos, dni, email, telefono, fecha_nacimiento 
                FROM usuarios 
                WHERE id_usuario = $id_usuario";
$usuario_result = $conn->query($usuario_sql);
$usuario = $usuario_result->fetch_assoc();

// 2. Obtener historial de compras (CORREGIDO)
// Esta consulta une la tabla pagos con reservas para obtener el monto y fecha real
$historial_sql = "
    SELECT 
        p.id_pago,
        p.monto,
        p.fecha_pago,
        p.metodo_pago,
        COUNT(r.id_reserva) AS cantidad_boletos,
        rt.origen,
        rt.destino,
        v.fecha_salida
    FROM pagos p
    JOIN reservas r ON p.id_pago = r.id_pago
    JOIN viajes v ON r.id_viaje = v.id_viaje
    JOIN rutas rt ON v.id_ruta = rt.id_ruta
    WHERE r.id_usuario = $id_usuario 
      AND p.estado = 'completado'
    GROUP BY p.id_pago, p.monto, p.fecha_pago, p.metodo_pago, rt.origen, rt.destino, v.fecha_salida
    ORDER BY p.fecha_pago DESC
";

$historial_result = $conn->query($historial_sql);

if (!$historial_result) {
    // Si hay un error en la consulta, muéstralo
    echo "Error en la consulta de historial: " . $conn->error;
    exit;
}

// 3. Revisar si hay mensajes de sesión (de éxito o error)
$mensaje_exito = '';
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_exito'];
    unset($_SESSION['mensaje_exito']); // Limpiar el mensaje
}

$mensaje_error = '';
if (isset($_SESSION['mensaje_error'])) {
    $mensaje_error = $_SESSION['mensaje_error'];
    unset($_SESSION['mensaje_error']); // Limpiar el mensaje
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #004a99; /* Azul primario */
            border-bottom: 2px solid #004a99;
            padding-bottom: 10px;
        }
        
        /* Sección de Perfil */
        .perfil-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .perfil-item p {
            font-size: 1.1em;
            color: #555;
            margin: 8px 0;
        }
        .perfil-item p strong {
            color: #333;
            width: 150px;
            display: inline-block;
        }
        .btn-edit {
            background-color: #f0ad4e; /* Naranja */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .btn-edit:hover {
            background-color: #ec971f;
        }

        /* Sección de Historial */
        .historial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .historial-table th, .historial-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .historial-table th {
            background-color: #f9f9f9;
            color: #333;
            font-weight: 700;
        }
        .historial-table tr:nth-child(even) {
            background-color: #fdfdfd;
        }
        .btn-ver {
            background-color: #5cb85c; /* Verde */
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
        }
        .btn-ver:hover {
            background-color: #4cae4c;
        }
        .no-compras {
            background-color: #fffde7; /* Amarillo claro */
            border: 1px solid #fff59d;
            color: #6d4c41;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .header-links {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header-links a {
            background-color: #d9534f;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        
        /* Mensajes de Alerta */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 1.1em;
            display: flex;
            align-items: center;
        }
        .alert-success {
            background-color: #dff0d8;
            border: 1px solid #c3e6cb;
            color: #3c763d;
        }
        .alert-error {
            background-color: #f2dede;
            border: 1px solid #ebcccc;
            color: #a94442;
        }
        .alert .material-icons-outlined {
            margin-right: 10px;
        }

    </style>
</head>
<body>
    <div class="container">

        <div class="header-links">
            <h1>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            <div>
                <a href="../index.php">
                    <span class="material-icons-outlined" style="vertical-align: middle; font-size: 1.2em;">home</span>
                    Inicio
                </a>
                <a href="../auth/logout.php" style="margin-left: 10px; background-color: #777;">
                    <span class="material-icons-outlined" style="vertical-align: middle; font-size: 1.2em;">logout</span>
                    Salir
                </a>
            </div>
        </div>

        <!-- Mostrar Mensajes de Sesión -->
        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success">
                <span class="material-icons-outlined">check_circle</span>
                <?php echo $mensaje_exito; ?>
            </div>
        <?php endif; ?>
        <?php if ($mensaje_error): ?>
            <div class="alert alert-error">
                <span class="material-icons-outlined">error</span>
                <?php echo $mensaje_error; ?>
            </div>
        <?php endif; ?>


        <!-- Sección de Datos Personales -->
        <h2>Mis Datos</h2>
        <div class="perfil-grid">
            <div class="perfil-item">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></p>
                <p><strong>DNI:</strong> <?php echo htmlspecialchars($usuario['dni']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            </div>
            <div class="perfil-item">
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono'] ?? 'No registrado'); ?></p>                
                <p><strong>Nacimiento:</strong> <?php echo htmlspecialchars($usuario['fecha_nacimiento'] ? date('d/m/Y', strtotime($usuario['fecha_nacimiento'])) : 'No registrada'); ?></t></p>
            </div>
        </div>
        <!-- BOTÓN EDITAR -->
        <a href="editar_perfil.php" class="btn-edit">
            <span class="material-icons-outlined" style="vertical-align: middle; font-size: 1.2em;">edit</span>
            Editar Mis Datos
        </a>

        <br><br>

        <!-- Sección de Historial de Compras -->
        <h2>Mis Compras</h2>
        <?php if ($historial_result->num_rows > 0): ?>
            <div style="overflow-x: auto;"> <!-- Para responsive en tablas -->
                <table class="historial-table">
                    <thead>
                        <tr>
                            <th>ID Transacción</th>
                            <th>Fecha Compra</th>
                            <th>Ruta</th>
                            <th>N° Boletos</th>
                            <th>Total Pagado</th>
                            <th>Método</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($compra = $historial_result->fetch_assoc()): ?>
                        <tr>
                            <!-- Validamos que existan las claves para evitar warnings -->
                            <td><?php echo str_pad($compra['id_pago'] ?? 0, 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo isset($compra['fecha_pago']) ? date('d/m/Y H:i', strtotime($compra['fecha_pago'])) : 'N/A'; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($compra['origen'] . ' - ' . $compra['destino']); ?></strong>
                                <br>
                                <small>Salida: <?php echo isset($compra['fecha_salida']) ? date('d/m/Y h:i A', strtotime($compra['fecha_salida'])) : ''; ?></small>
                            </td>
                            <td><?php echo $compra['cantidad_boletos']; ?></td>
                            <td>S/ <?php echo number_format($compra['monto'] ?? 0, 2); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($compra['metodo_pago'] ?? 'Desconocido')); ?></td>
                            <td>
                                <a href="../compra/boleto.php?pago_id=<?php echo $compra['id_pago'] ?? 0; ?>" class="btn-ver">
                                    <span class="material-icons-outlined" style="vertical-align: middle; font-size: 1.2em;">receipt_long</span>
                                    Ver Boletos
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-compras">
                <p>Aún no has realizado ninguna compra.</p>
                <a href="../index.php" style="color: #004a99; font-weight: bold;">¡Busca tu próximo viaje!</a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>