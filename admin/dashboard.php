<?php
include 'includes/admin_header.php';

// --- Consultas para las estadísticas del Dashboard ---

// 1. Total de Usuarios
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];

// 2. Total de Buses
$total_buses = $conn->query("SELECT COUNT(*) as total FROM buses")->fetch_assoc()['total'];

// 3. Total de Rutas
$total_rutas = $conn->query("SELECT COUNT(*) as total FROM rutas")->fetch_assoc()['total'];

// 4. Viajes Programados (a futuro)
$viajes_programados = $conn->query("SELECT COUNT(*) as total FROM viajes WHERE estado = 'programado' AND fecha_salida > NOW()")->fetch_assoc()['total'];

// 5. Total Reservas Confirmadas
$total_reservas = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE estado = 'confirmada'")->fetch_assoc()['total'];

// 6. Total de Ventas (Pagos completados)
$ventas_query = $conn->query("SELECT SUM(monto) as total FROM pagos WHERE estado = 'completado'");
$total_ventas = $ventas_query->fetch_assoc()['total'];
$total_ventas = $total_ventas ?? 0; // Asegurar que sea 0 si es NULL

?>

<h2 style="color: var(--dark-blue); font-weight: 500;">Resumen del Sistema</h2>

<div class="dashboard-stats-grid">
    <div class="stat-card">
        <span class="material-symbols-outlined icon">group</span>
        <div>
            <h4>Total de Usuarios</h4>
            <p><?php echo $total_usuarios; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <span class="material-symbols-outlined icon">directions_bus</span>
        <div>
            <h4>Total de Buses</h4>
            <p><?php echo $total_buses; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <span class="material-symbols-outlined icon">route</span>
        <div>
            <h4>Total de Rutas</h4>
            <p><?php echo $total_rutas; ?></p>
        </div>
    </div>
    <div class="stat-card">
        <span class="material-symbols-outlined icon">event_upcoming</span>
        <div>
            <h4>Viajes Programados</h4>
            <p><?php echo $viajes_programados; ?></p>
        </div>
    </div>
     <div class="stat-card">
        <span class="material-symbols-outlined icon">confirmation_number</span>
        <div>
            <h4>Reservas Confirmadas</h4>
            <p><?php echo $total_reservas; ?></p>
        </div>
    </div>
     <div class="stat-card">
        <span class="material-symbols-outlined icon">payments</span>
        <div>
            <h4>Ingresos Totales</h4>
            <p>S/ <?php echo number_format($total_ventas, 2); ?></p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <h3 class="card-header">Últimos Viajes Programados</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ruta</th>
                <th>Bus</th>
                <th>Fecha Salida</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT v.id_viaje, r.origen, r.destino, b.placa, v.fecha_salida, v.estado 
                    FROM viajes v
                    JOIN rutas r ON v.id_ruta = r.id_ruta
                    JOIN buses b ON v.id_bus = b.id_bus
                    WHERE v.estado = 'programado' AND v.fecha_salida > NOW()
                    ORDER BY v.fecha_salida ASC
                    LIMIT 5"; // Mostrar solo los 5 más próximos
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_viaje'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['origen'] . ' - ' . $row['destino']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['placa']) . "</td>";
                    echo "<td>" . $row['fecha_salida'] . "</td>";
                    echo "<td><span style='color:green; font-weight:bold;'>" . htmlspecialchars($row['estado']) . "</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay viajes programados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>


<?php include 'includes/admin_footer.php'; ?>