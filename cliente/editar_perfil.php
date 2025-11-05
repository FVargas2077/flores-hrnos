<?php
session_start();
include '../config/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener datos actuales del perfil
$sql = "SELECT nombre, apellidos, dni, email, telefono, direccion, fecha_nacimiento, genero 
        FROM usuarios 
        WHERE id_usuario = $id_usuario";
$result = $conn->query($sql);
$usuario = $result->fetch_assoc();

if (!$usuario) {
    echo "Error: Usuario no encontrado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mi Perfil</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #004a99;
            border-bottom: 2px solid #004a99;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="date"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Asegura que el padding no afecte el ancho */
            font-size: 1em;
        }
        .form-group input[readonly] {
            background-color: #eee;
            cursor: not-allowed;
        }
        .form-actions {
            display: flex;
            justify-content: flex-start;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            font-size: 1em;
            display: inline-flex;
            align-items: center;
        }
        .btn-primary {
            background-color: #004a99;
            color: white;
        }
        .btn-secondary {
            background-color: #777;
            color: white;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .password-section {
            grid-column: 1 / -1;
            border-top: 1px solid #eee;
            margin-top: 15px;
            padding-top: 25px;
        }
        .password-section h2 {
            font-size: 1.4em;
            color: #333;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Mis Datos Personales</h1>
        <p>Actualiza tu información. Tu DNI no puede ser modificado.</p>

        <!-- Formulario de Edición -->
        <form action="handle_editar_perfil.php" method="POST">
            <div class="form-grid">
                
                <!-- DNI (Solo lectura) -->
                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>" readonly>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>

                <!-- Nombre -->
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>

                <!-- Apellidos -->
                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
                </div>

                <!-- Teléfono -->
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                </div>

                <!-- Fecha de Nacimiento -->
                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($usuario['fecha_nacimiento'] ?? ''); ?>">
                </div>

                <!-- Dirección (Ancho completo) -->
                <div class="form-group full-width">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>">
                </div>

                <!-- Sección para Cambiar Contraseña -->
                <div class="password-section">
                    <h2>Cambiar Contraseña (Opcional)</h2>
                    <p>Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="password_actual">Contraseña Actual</label>
                            <input type="password" id="password_actual" name="password_actual">
                        </div>
                        <div class="form-group">
                            <label for="password_nueva">Nueva Contraseña</label>
                            <input type="password" id="password_nueva" name="password_nueva">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Botones de Acción -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons-outlined" style="margin-right: 8px;">save</span>
                    Guardar Cambios
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <span class="material-icons-outlined" style="margin-right: 8px;">cancel</span>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>