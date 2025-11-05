<?php
// cliente/index.php
include '../config/db.php';

// --- Control de Acceso ---
// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error_login'] = "Debes iniciar sesión para ver tu perfil.";
    header('Location: ../auth/login.php');
    exit;
}

// 2. Verificar si es cliente (o admin, aunque admin tiene su panel)
if ($_SESSION['rol'] != 'cliente' && $_SESSION['rol'] != 'admin') {
    // Si no es cliente, destruir sesión y redirigir
    session_destroy();
    $_SESSION['error_login'] = "Acceso denegado.";
    header('Location: ../auth/login.php');
    exit;
}
// --- Fin Control de Acceso ---

$id_usuario = $_SESSION['id_usuario'];

// 1. Obtener datos del usuario
$sql_usuario = "SELECT * FROM usuarios WHERE id_usuario = $id_usuario";
$user_res = $conn->query($sql_usuario);
$usuario = $user_res->fetch_assoc();

// 2. Obtener historial de viajes (reservas confirmadas)
$sql_viajes = "SELECT 
    res.fecha_reserva, res.precio_final,
    v.fecha_salida,
    ru.origen, ru.destino,
    a.numero_asiento, a.piso,
    res.estado AS estado_reserva
FROM reservas res
JOIN viajes v ON res.id_viaje = v.id_viaje
JOIN rutas ru ON v.id_ruta = ru.id_ruta
JOIN asientos a ON res.id_asiento = a.id_asiento
WHERE res.id_usuario = $id_usuario
ORDER BY v.fecha_salida DESC";

$viajes_res = $conn->query($sql_viajes);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo htmlspecialchars($usuario['nombre']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; }
        nav { background-color: #004a99; padding: 1em; color: white; }
        nav a { color: white; text-decoration: none; padding: 0 1em; }
        .container { max-width: 1200px; margin: 2em auto; display: flex; gap: 2em; }
        .sidebar { background-color: white; padding: 1.5em; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); width: 250px; flex-shrink: 0; }
        .sidebar h3 { margin-top: 0; color: #004a99; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li a { display: block; padding: 0.8em 0; color: #333; text-decoration: none; border-bottom: 1px solid #f0f0f0; }
        .sidebar ul li a:hover { color: #004a99; }
        .main-content { background-color: white; padding: 1.5em; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); flex-grow: 1; }
        .main-content h1 { margin-top: 0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1em; }
        .form-group label { font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 0.5em; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 1.5em; }
        table th, table td { border: 1px solid #ddd; padding: 0.8em; text-align: left; }
        table th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <nav>
        <a href="../index.php"><b>Buses Flores</b></a>
        <div style="float:right;">
            <a href="index.php">Mi Perfil</a>
            <a href="../auth/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <aside class="sidebar">
            <h3>Mi Cuenta</h3>
            <ul>
                <li><a href="#perfil">Mi Perfil</a></li>
                <li><a href="#viajes">Mis Viajes</a></li>
                <li><a href="../index.php">Comprar Pasaje</a></li>
                <li><a href="../auth/logout.php">Cerrar Sesión</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h1>
            
            <section id="perfil">
                <h2>Mi Perfil</h2>
                <form class="form-grid">
                    <div class="form-group">
                        <label>Nombres</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Apellidos</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>DNI</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['dni']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Nacimiento</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>" disabled>
                    </div>
                </form>
                <!-- Próximamente: Botón para editar perfil -->
            </section>
            
            <section id="viajes" style="margin-top: 2em;">
                <h2>Mis Viajes</h2>
                <?php if ($viajes_res->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Ruta</th>
                                <th>Fecha Salida</th>
                                <th>Asiento</th>
                                <th>Piso</th>
                                <th>Precio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($viaje = $viajes_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($viaje['origen']) . ' - ' . htmlspecialchars($viaje['destino']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?></td>
                                <td><?php echo htmlspecialchars($viaje['numero_asiento']); ?></td>
                                <td><?php echo htmlspecialchars($viaje['piso']); ?></td>
                                <td>S/ <?php echo htmlspecialchars($viaje['precio_final']); ?></td>
                                <td><?php echo htmlspecialchars($viaje['estado_reserva']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Aún no has comprado ningún pasaje.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>

</body>
</html>

