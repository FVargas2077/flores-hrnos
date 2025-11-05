<?php
include 'includes/admin_header.php';

// Lógica para modo Edición
$modo_edicion = false;
$ruta_a_editar = null;
if (isset($_GET['edit_id'])) {
    $modo_edicion = true;
    $id_ruta_editar = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM rutas WHERE id_ruta = $id_ruta_editar");
    if ($result->num_rows > 0) {
        $ruta_a_editar = $result->fetch_assoc();
    }
}

// Lógica para mostrar mensajes
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'success_add') echo "<div class='alert success'>Ruta añadida correctamente.</div>";
    if ($msg == 'success_edit') echo "<div class='alert success'>Ruta actualizada correctamente.</div>";
    if ($msg == 'success_delete') echo "<div class='alert danger'>Ruta eliminada correctamente.</div>";
    if ($msg == 'error') echo "<div class='alert danger'>Ocurrió un error.</div>";
    if ($msg == 'error_delete_fk') echo "<div class='alert danger'>Error: No se puede eliminar la ruta, está siendo usada en un viaje.</div>";
}
?>

<!-- Formulario de Añadir/Editar Ruta -->
<div class="card">
    <h3 class="card-header"><?php echo $modo_edicion ? 'Editar Ruta' : 'Añadir Nueva Ruta'; ?></h3>
    <form action="handle_ruta.php" method="POST">
        <?php if ($modo_edicion): ?>
            <input type="hidden" name="id_ruta" value="<?php echo $ruta_a_editar['id_ruta']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="origen">Origen</label>
                <input type="text" id="origen" name="origen" value="<?php echo $ruta_a_editar['origen'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="destino">Destino</label>
                <input type="text" id="destino" name="destino" value="<?php echo $ruta_a_editar['destino'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="distancia">Distancia (Km)</label>
                <input type="number" step="0.01" id="distancia" name="distancia" value="<?php echo $ruta_a_editar['distancia'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="duracion_estimada">Duración Estimada (HH:MM)</label>
                <input type="time" id="duracion_estimada" name="duracion_estimada" value="<?php echo $ruta_a_editar['duracion_estimada'] ?? '00:00'; ?>">
            </div>
            <div class="form-group">
                <label for="precio_base">Precio Base (S/)</label>
                <input type="number" step="0.01" id="precio_base" name="precio_base" value="<?php echo $ruta_a_editar['precio_base'] ?? ''; ?>">
            </div>
        </div>
        <div class="form-actions">
            <?php if ($modo_edicion): ?>
                <button type="submit" name="edit_ruta" class="btn btn-primary">Actualizar Ruta</button>
                <a href="gestionar_rutas.php" class="btn">Cancelar Edición</a>
            <?php else: ?>
                <button type="submit" name="add_ruta" class="btn btn-primary">Añadir Ruta</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Lista de Rutas Existentes -->
<div class="card">
    <h3 class="card-header">Lista de Rutas</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Distancia</th>
                <th>Duración</th>
                <th>Precio Base</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM rutas ORDER BY origen, destino");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id_ruta'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['origen']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['destino']) . "</td>";
                    echo "<td>" . $row['distancia'] . " Km</td>";
                    echo "<td>" . $row['duracion_estimada'] . "</td>";
                    echo "<td>S/ " . number_format($row['precio_base'], 2) . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='gestionar_rutas.php?edit_id=" . $row['id_ruta'] . "' class='btn btn-warning'>Editar</a>";
                    echo "<a href='handle_ruta.php?delete_id=" . $row['id_ruta'] . "' class='btn btn-danger' onclick=\"return confirm('¿Estás seguro de eliminar esta ruta?');\">Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay rutas registradas.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>