<?php
include '../config/db.php';

// Si el usuario ya está logueado, redirigirlo
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['rol'] == 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../cliente/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background-color: white; padding: 2em; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 300px; }
        .login-box h1 { text-align: center; color: #004a99; margin-bottom: 1em; }
        .form-group { margin-bottom: 1em; }
        .form-group label { display: block; margin-bottom: 0.5em; font-weight: bold; }
        .form-group input { width: 100%; padding: 0.8em; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-group button { width: 100%; padding: 0.8em; background-color: #004a99; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; }
        .error { background-color: #f8d7da; color: #721c24; padding: 0.8em; border-radius: 4px; margin-bottom: 1em; text-align: center; }
        .info { text-align: center; margin-top: 1em; }
        .info a { color: #004a99; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>Iniciar Sesión</h1>

        <?php
        // Mostrar mensaje de error si existe
        if (isset($_SESSION['error_login'])) {
            echo "<div class='error'>" . $_SESSION['error_login'] . "</div>";
            unset($_SESSION['error_login']); // Limpiar el error
        }
        ?>

        <form action="handle_login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Ingresar</button>
            </div>
        </form>
        <div class="info">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
            <p><a href="../index.php">Volver al inicio</a></p>
        </div>
    </div>
</body>
</html>
