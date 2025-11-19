<?php
include '../config/db.php';

// Si el usuario ya está logueado, redirigirlo
if (isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; padding: 2em 0; }
        .register-box { background-color: white; padding: 2em; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 600px; }
        .register-box h1 { text-align: center; color: #004a99; margin-bottom: 1em; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1em; }
        .form-group { margin-bottom: 1em; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; margin-bottom: 0.5em; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 0.8em; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-group button { width: 100%; padding: 0.8em; background-color: #004a99; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        .error { background-color: #f8d7da; color: #721c24; padding: 0.8em; border-radius: 4px; margin-bottom: 1em; text-align: center; grid-column: 1 / -1; }
        .info { text-align: center; margin-top: 1em; grid-column: 1 / -1; }
        .info a { color: #004a99; text-decoration: none; }
    </style>
</head>
<body>
    <div class="register-box">
        <h1>Crear Cuenta</h1>

        <?php
        if (isset($_SESSION['error_register'])) {
            echo "<div class='error'>" . $_SESSION['error_register'] . "</div>";
            unset($_SESSION['error_register']);
        }
        ?>

        <form action="handle_register.php" method="POST" class="form-grid">
            <div class="form-group">
                <label for="nombre">Nombres:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" required>
            </div>
            <div class="form-group">
                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" required maxlength="8" pattern="\d{8}" title="El DNI debe tener 8 dígitos">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar Contraseña:</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono">
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
            </div>
            <div class="form-group full-width">
                <label for="genero">Género:</label>
                <select id="genero" name="genero">
                    <option value="">Seleccione...</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="O">Otro</option>
                </select>
            </div>
            <div class="form-group full-width">
                <button type="submit">Registrarse</button>
            </div>
            <div class="info">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia Sesión</a></p>
                <p><a href="../index.php">Volver al inicio</a></p>
            </div>
        </form>
    </div>
</body>
</html>
