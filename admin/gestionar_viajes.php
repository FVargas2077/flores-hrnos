<?php
include 'includes/admin_header.php';

// Obtener datos para los dropdowns
$rutas_result = $conn->query("SELECT id_ruta, origen, destino FROM rutas ORDER BY origen");
$buses_result = $conn->query("SELECT id_bus, placa, tipo_servicio FROM buses WHERE estado = 'activo' ORDER BY placa");

// Opcional: Obtener conductores (requiere unir usuarios y trabajadores)
// $conductores_result = $conn->query("SELECT t.id_trabajador, u.nombre, u.apellidos FROM trabajadores t JOIN usuarios u ON t.id_usuario = u.id_usuario JOIN tipos_trabajador tt ON t.id_tipo = tt.id_tipo WHERE tt.nombre_tipo = 'Conductor'");

// Lógica para mostrar mensajes
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'success_add') echo "<div class='alert success'>Viaje programado correctamente.</div>";
    if ($msg == 'success_delete') echo "<div class='alert danger'>Viaje eliminado correctamente.</div>";
    if ($msg == 'error') echo "<div class='alert danger'>Ocurrió un error.</div>";
}
?>

<!-- Formulario de Añadir Viaje -->
<div class="card">
    <h3 class="card-header">Programar Nuevo Viaje</h3>
    <form action="handle_viaje.php" method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label for="id_ruta">Ruta</label>
                <select id="id_ruta" name="id_ruta" required>
                    <option value="">Seleccione una ruta...</option>
                    <?php while($r = $rutas_result->fetch_assoc()): ?>
                        <option value="<?php echo $r['id_ruta']; ?>">
                            <?php echo htmlspecialchars($r['origen'] . ' - ' . $r['destino']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="id_bus">Bus</label>
                <select id="id_bus" name="id_bus" required>
                     <option value="">Seleccione un bus...</option>
                    <?php while($b = $buses_result->fetch_assoc()): ?>
                        <option value="<?php echo $b['id_bus']; ?>">
                            <?php echo htmlspecialchars($b['placa'] . ' (' . $b['tipo_servicio'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="fecha_salida">Fecha y Hora de Salida</label>
                <input type="datetime-local" id="fecha_salida" name="fecha_salida" required>
            </div>
            <div class="form-group">
                <label for="fecha_llegada">Fecha y Hora de Llegada (Estimada)</label>
                <input type="datetime-local" id="fecha_llegada" name="fecha_llegada" required>
            </div>
            
            <!-- Opcional: Añadir selects para Conductor y Copiloto si se implementó la consulta -->
            <!--
            <div class="form-group">
                <label for="id_conductor">Conductor</label>
                <select id="id_conductor" name="id_conductor"> ... </select>
            </div>
            <div class="form-group">
                <label for="id_copiloto">Copiloto</label>
                <select id="id_copiloto" name="id_copiloto"> ... </select>
            </div>
            -->
            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="programado" selected>Programado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" name="add_viaje" class="btn btn-primary">Programar Viaje</button>
        </div>
    </form>
</div>

<!-- Lista de Viajes Programados -->
<div class="card">
    <h3 class="card-header">Próximos Viajes Programados</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ruta</th>
                <th>Bus</th>
                <th>Fecha Salida</th>
                <th>Fecha Llegada</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT v.id_viaje, r.origen, r.destino, b.placa, v.fecha_salida, v.fecha_llegada, v.estado 
                    FROM viajes v
                    JOIN rutas r ON v.id_ruta = r.id_ruta
                    JOIN buses b ON v.id_bus = b.id_bus
                    WHERE v.estado = 'programado' AND v.fecha_salida > NOW()
                    ORDER BY v.fecha_salida ASC";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_viaje'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['origen'] . ' - ' . $row['destino']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['placa']) . "</td>";
                    echo "<td>" . $row['fecha_salida'] . "</td>";
                    echo "<td>" . $row['fecha_llegada'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='handle_viaje.php?delete_id=" . $row['id_viaje'] . "' class='btn btn-danger' onclick=\"return confirm('¿Estás seguro de eliminar este viaje?');\">Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay viajes programados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>