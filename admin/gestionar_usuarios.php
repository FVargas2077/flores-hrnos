<?php
include 'includes/admin_header.php';

// Lógica para modo Edición
$modo_edicion = false;
$usuario_a_editar = null;
if (isset($_GET['edit_id'])) {
    $modo_edicion = true;
    $id_usuario_editar = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM usuarios WHERE id_usuario = $id_usuario_editar");
    if ($result->num_rows > 0) {
        $usuario_a_editar = $result->fetch_assoc();
    }
}

// Obtener roles para el dropdown
$roles_result = $conn->query("SELECT * FROM roles");

// Lógica para mostrar mensajes
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg == 'success_add') echo "<div class='alert success'>Usuario añadido correctamente.</div>";
    if ($msg == 'success_edit') echo "<div class='alert success'>Usuario actualizado correctamente.</div>";
    if ($msg == 'success_delete') echo "<div class='alert danger'>Usuario eliminado correctamente.</div>";
    if ($msg == 'error') echo "<div class='alert danger'>Ocurrió un error.</div>";
    if ($msg == 'error_dni') echo "<div class='alert danger'>Error: El DNI ya está registrado.</div>";
    if ($msg == 'error_email') echo "<div class='alert danger'>Error: El Email ya está registrado.</div>";
    if ($msg == 'error_delete_self') echo "<div class='alert danger'>Error: No puedes eliminarte a ti mismo.</div>";
}
?>

<!-- Formulario de Añadir/Editar Usuario -->
<div class="card">
    <h3 class="card-header"><?php echo $modo_edicion ? 'Editar Usuario' : 'Añadir Nuevo Usuario'; ?></h3>
    <form action="handle_usuario.php" method="POST">
        <?php if ($modo_edicion): ?>
            <input type="hidden" name="id_usuario" value="<?php echo $usuario_a_editar['id_usuario']; ?>">
        <?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo $usuario_a_editar['nombre'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" value="<?php echo $usuario_a_editar['apellidos'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" maxlength="8" value="<?php echo $usuario_a_editar['dni'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $usuario_a_editar['email'] ?? ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="password"><?php echo $modo_edicion ? 'Nueva Contraseña (Dejar en blanco para no cambiar)' : 'Contraseña'; ?></label>
                <input type="password" id="password" name="password" <?php echo !$modo_edicion ? 'required' : ''; ?>>
            </div>
             <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="<?php echo $usuario_a_editar['telefono'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="id_rol">Rol</label>
                <select id="id_rol" name="id_rol" required>
                    <?php
                    while ($rol = $roles_result->fetch_assoc()) {
                        $selected = (isset($usuario_a_editar) && $usuario_a_editar['id_rol'] == $rol['id_rol']) ? 'selected' : '';
                        echo "<option value='" . $rol['id_rol'] . "' $selected>" . htmlspecialchars($rol['nombre_rol']) . "</option>";
                    }
                    ?>
                </select>
            </div>
             <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" required>
                    <option value="1" <?php echo (isset($usuario_a_editar) && $usuario_a_editar['estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo (isset($usuario_a_editar) && $usuario_a_editar['estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <?php if ($modo_edicion): ?>
                <button type="submit" name="edit_usuario" class="btn btn-primary">Actualizar Usuario</button>
                <a href="gestionar_usuarios.php" class="btn">Cancelar Edición</a>
            <?php else: ?>
                <button type="submit" name="add_usuario" class="btn btn-primary">Añadir Usuario</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Lista de Usuarios Existentes -->
<div class="card">
    <h3 class="card-header">Lista de Usuarios</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Usamos la VISTA que ya tenías en tu SQL
            $result = $conn->query("SELECT * FROM v_usuarios_info ORDER BY id_usuario DESC");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $estado_label = $row['estado'] ? '<span style="color:green;">Activo</span>' : '<span style="color:red;">Inactivo</span>';
                    echo "<tr>";
                    echo "<td>" . $row['id_usuario'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['dni']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre_rol']) . "</td>";
                    echo "<td>" . $estado_label . "</td>";
                    echo "<td class='actions'>";
                    echo "<a href='gestionar_usuarios.php?edit_id=" . $row['id_usuario'] . "' class='btn btn-warning'>Editar</a>";
                    // Evitar que el admin se elimine a sí mismo
                    if ($row['id_usuario'] != $_SESSION['id_usuario']) {
                        echo "<a href='handle_usuario.php?delete_id=" . $row['id_usuario'] . "' class='btn btn-danger' onclick=\"return confirm('¿Estás seguro de eliminar este usuario?');\">Eliminar</a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No hay usuarios registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/admin_footer.php'; ?>