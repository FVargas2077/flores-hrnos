<?php
session_start();
// Configuración de errores para depuración (desactivar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';

// Recoger parámetros de búsqueda de manera segura
$origen = isset($_GET['origen']) ? $conn->real_escape_string($_GET['origen']) : '';
$destino = isset($_GET['destino']) ? $conn->real_escape_string($_GET['destino']) : '';
$fecha = isset($_GET['fecha_ida']) ? $conn->real_escape_string($_GET['fecha_ida']) : '';

// Verificar conexión a BD
if (!$conn) {
    die("Error crítico: No se pudo conectar a la base de datos.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda - Flores Hnos</title>
    
    <!-- Estilos y Fuentes (Guiado del repositorio Main) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- CSS Personalizado Corregido -->
    <link rel="stylesheet" href="../public/css/resultados.css">
</head>
<body>

    <!-- HEADER (Estructura idéntica al repositorio Main para consistencia) -->
    <!-- Top Bar -->
    <div class="header-top">
        <div class="header-top-content">
            <div class="left">
                <a href="../index.php"><span class="material-symbols-outlined icon">home</span> Inicio</a>
                <a href="#"><span class="material-symbols-outlined icon">desktop_windows</span> Compras Online</a>
            </div>
            <div class="right">
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <a href="index.php"><span class="material-symbols-outlined icon">account_circle</span> Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Usuario'); ?></a>
                    <a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Salir</a>
                <?php else: ?>
                    <a href="../auth/login.php"><span class="material-symbols-outlined icon">person</span> Iniciar Sesión</a>
                    <a href="../auth/register.php"><span class="material-symbols-outlined icon">person_add</span> Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Navbar -->
    <header class="main-header">
        <div class="navbar-content">
            <a href="../index.php">
                <!-- Ajuste de ruta para logo -->
                <img src="../public/img/logo.png" alt="Logo Flores Hnos" class="navbar-logo">
            </a>
            <div class="navbar-links">
                <a href="../index.php#services">Servicios</a>
                <a href="../index.php#quality">Calidad</a>
                <a href="../index.php#travel">Destinos</a>
            </div>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <div class="contenedor-viajes">
            <?php if ($origen && $destino && $fecha): ?>
                <h2>
                    <span class="material-symbols-outlined" style="vertical-align: bottom; font-size: 1.2em;">directions_bus</span>
                    Viajes: <?php echo htmlspecialchars(strtoupper($origen)); ?> - <?php echo htmlspecialchars(strtoupper($destino)); ?>
                    <br>
                    <small style="font-size: 0.6em; color: #777;">Fecha: <?php echo date('d/m/Y', strtotime($fecha)); ?></small>
                </h2>

                <?php
                // Lógica de consulta corregida para usar las VISTAS del Main
                $fecha_inicio = $fecha . ' 00:00:00';
                $fecha_fin = $fecha . ' 23:59:59';

                // Usamos v_viajes_programados como base, similar al main
                $query = "SELECT vvp.*, vvp.`N° Viaje` as id_viaje, v.id_bus
                          FROM v_viajes_programados AS vvp
                          JOIN viajes AS v ON vvp.`N° Viaje` = v.id_viaje
                          WHERE vvp.origen = ? 
                            AND vvp.destino = ? 
                            AND v.fecha_salida BETWEEN ? AND ?
                            AND vvp.estado = 'programado'
                          ORDER BY v.fecha_salida ASC";

                $stmt = $conn->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("ssss", $origen, $destino, $fecha_inicio, $fecha_fin);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0): 
                        while ($viaje = $result->fetch_assoc()): 
                            // Determinar precio más bajo para mostrar "desde"
                            // La vista devuelve strings '1° Piso S/ 100', extraemos solo el número si es posible o mostramos todo
                            $precio_mostrar = $viaje['Precio_Piso1'] ? $viaje['Precio_Piso1'] : ($viaje['Precio_Piso2'] ?? 'Consultar');
                ?>
                            <!-- Tarjeta de Viaje Estilo 'Main' -->
                            <div class="viaje-card">
                                <div>
                                    <strong>Servicio</strong>
                                    <span><?php echo htmlspecialchars($viaje['Servicio']); ?></span>
                                    <br><small style="color: #999;"><?php echo htmlspecialchars($viaje['Unidad']); ?></small>
                                </div>
                                <div>
                                    <strong>Hora Salida</strong>
                                    <span style="font-size: 1.3em; color: #333;"><?php echo htmlspecialchars($viaje['Salida']); ?></span>
                                </div>
                                <div>
                                    <strong>Duración</strong>
                                    <span><?php echo htmlspecialchars($viaje['Duracion']); ?></span>
                                </div>
                                <div>
                                    <strong>Asientos Libres</strong>
                                    <span style="color: green;">
                                        P1: <?php echo $viaje['Asientos_Libres_Piso1']; ?> | P2: <?php echo $viaje['Asientos_Libres_Piso2']; ?>
                                    </span>
                                </div>
                                <div>
                                    <strong>Precio</strong>
                                    <span style="color: var(--secondary-red); font-weight: bold;"><?php echo htmlspecialchars($precio_mostrar); ?></span>
                                    <form action="../compra/seleccionar_asientos.php" method="GET" style="margin-top: 10px;">
                                        <input type="hidden" name="viaje" value="<?php echo $viaje['id_viaje']; ?>">
                                        <button type="submit" class="btn-comprar">Seleccionar</button>
                                    </form>
                                </div>
                            </div>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <span class="material-symbols-outlined" style="font-size: 3em; color: #ccc;">search_off</span>
                            <p>No se encontraron viajes programados para esta ruta en la fecha seleccionada.</p>
                            <a href="../index.php" class="btn-comprar" style="background-color: #777; width: auto; display: inline-block; margin-top: 1em;">Realizar otra búsqueda</a>
                        </div>
                    <?php endif; 
                    $stmt->close();
                } else {
                    echo "<div class='alert danger'>Error en la consulta: " . $conn->error . "</div>";
                }
                ?>

            <?php else: ?>
                <div class="no-results">
                    <p>Faltan parámetros de búsqueda. Por favor regresa al inicio.</p>
                    <a href="../index.php">Volver al Inicio</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sección Informativa Mejorada -->
        <div class="info-grid-section">
            <div class="info-container">
                <h3>¿Por qué viajar con nosotros?</h3>
                <div class="grid-3-col">
                    <div class="info-item">
                        <span class="material-symbols-outlined info-icon">verified_user</span>
                        <h4>Compra Segura</h4>
                        <p>Tus datos están protegidos con los más altos estándares de seguridad (PCI DSS).</p>
                    </div>
                    <div class="info-item">
                        <span class="material-symbols-outlined info-icon">payments</span>
                        <h4>Medios de Pago</h4>
                        <p>Aceptamos todas las tarjetas de crédito, débito y transferencias bancarias.</p>
                    </div>
                    <div class="info-item">
                        <span class="material-symbols-outlined info-icon">devices</span>
                        <h4>Facilidad Digital</h4>
                        <p>Olvídate de las colas. Compra tu pasaje desde tu celular o computadora en minutos.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER (Idéntico al repositorio Main) -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <img src="../public/img/logo.png" alt="Logo Flores Hnos" class="footer-logo">
                <p>Ofrecemos a nuestros pasajeros un servicio de calidad a nivel nacional, con buses modernos y seguros.</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <ul>
                    <li><a href="#">Seguimiento de encomiendas</a></li>
                    <li><a href="#">Libro de reclamaciones</a></li>
                    <li><a href="#">Términos y condiciones</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contacto</h3>
                <p><i class="fas fa-map-marker-alt"></i> Av. Paseo de la República 627, Lima</p>
                <p><i class="fas fa-envelope"></i> info@floreshnos.pe</p>
                <p><i class="fas fa-phone"></i> (01) 4800 705</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Flores Hnos. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>