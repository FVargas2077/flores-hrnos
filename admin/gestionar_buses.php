<?php
include 'includes/admin_header.php';

// Lógica para modo Edición
$modo_edicion = false;
$bus_a_editar = null;
if (isset($_GET['edit_id'])) {
    $modo_edicion = true;
    $id_bus_editar = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM buses WHERE id_bus = $id_bus_editar");
    if ($result->num_rows > 0) {
        $bus_a_editar = $result->fetch_assoc();
    }
}

// Lógica para mostrar mensajes
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'success_add') echo "<div class='alert success'>Bus añadido correctamente.</div>";
    if ($msg == 'success_edit') echo "<div class='alert success'>Bus actualizado correctamente.</div>";
    if ($msg == 'success_delete') echo "<div class='alert danger'>Bus eliminado correctamente.</div>";
    if ($msg == 'error') echo "<div class='alert danger'>Ocurrió un error.</div>";
}
?>

<!-- Formulario de Añadir/Editar Bus -->
<div class="card">
    <h3 class="card-header"><?php echo $modo_edicion ? 'Editar Bus' : 'Añadir Nuevo Bus'; ?></h3>
    <form action="handle_bus.php" method="POST">
        <!-- Campo oculto para ID en modo edición -->
        <?php if ($modo_edicion): ?>
            <input type="hidden" name="id_bus" value="<?php echo $bus_a_editar['id_bus']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="placa">Placa</label>
                <input type="text" id="placa" name="placa" value="<?php echo $bus_a_editar['placa'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="modelo">Modelo</label>
                <input type="text" id="modelo" name="modelo" value="<?php echo $bus_a_editar['modelo'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="tipo_servicio">Tipo de Servicio</label>
                <select id="tipo_servicio" name="tipo_servicio" required>
                    <option value="BUS CAMA" <?php echo (isset($bus_a_editar) && $bus_a_editar['tipo_servicio'] == 'BUS CAMA') ? 'selected' : ''; ?>>BUS CAMA</option>
                    <option value="DOR VIP 160" <?php echo (isset($bus_a_editar) && $bus_a_editar['tipo_servicio'] == 'DOR VIP 160') ? 'selected' : ''; ?>>DOR VIP 160</option>
                    <option value="ECONOCAMA" <?php echo (isset($bus_a_editar) && $bus_a_editar['tipo_servicio'] == 'ECONOCAMA') ? 'selected' : ''; ?>>ECONOCAMA</option>
                    <option value="PLATINUM" <?php echo (isset($bus_a_editar) && $bus_a_editar['tipo_servicio'] == 'PLATINUM') ? 'selected' : ''; ?>>PLATINUM</option>
                </select>
            </div>
            <div class="form-group">
                <label for="capacidad_piso1">Capacidad Piso 1</label>
                <input type="number" id="capacidad_piso1" name="capacidad_piso1" value="<?php echo $bus_a_editar['capacidad_piso1'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="capacidad_piso2">Capacidad Piso 2 (Opcional)</label>
                <input type="number" id="capacidad_piso2" name="capacidad_piso2" value="<?php echo $bus_a_editar['capacidad_piso2'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="año_fabricacion">Año Fabricación</label>
                <input type="number" id="año_fabricacion" name="año_fabricacion" value="<?php echo $bus_a_editar['año_fabricacion'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="activo" <?php echo (isset($bus_a_editar) && $bus_a_editar['estado'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="mantenimiento" <?php echo (isset($bus_a_editar) && $bus_a_editar['estado'] == 'mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                    <option value="fuera_servicio" <?php echo (isset($bus_a_editar) && $bus_a_editar['estado'] == 'fuera_servicio') ? 'selected' : ''; ?>>Fuera de Servicio</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <?php if ($modo_edicion): ?>
                <button type="submit" name="edit_bus" class="btn btn-primary">Actualizar Bus</button>
                <a href="gestionar_buses.php" class="btn">Cancelar Edición</a>
            <?php else: ?>
                <button type="submit" name="add_bus" class="btn btn-primary">Añadir Bus</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Lista de Buses Existentes -->
<div class="card">
    <h3 class="card-header">Lista de Buses</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Servicio</th>
                <th>Piso 1</th>
                <th>Piso 2</th>
                <th>Año</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM buses ORDER BY id_bus DESC");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_bus'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['placa']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['modelo']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tipo_servicio']) . "</td>";
                    echo "<td>" . $row['capacidad_piso1'] . "</td>";
                    echo "<td>" . $row['capacidad_piso2'] . "</td>";
                    echo "<td>" . $row['año_fabricacion'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='gestionar_buses.php?edit_id=" . $row['id_bus'] . "' class='btn btn-warning'>Editar</a>";
                    echo "<a href='handle_bus.php?delete_id=" . $row['id_bus'] . "' class='btn btn-danger' onclick=\"return confirm('¿Estás seguro de eliminar este bus? Esta acción no se puede deshacer.');\">Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No hay buses registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>