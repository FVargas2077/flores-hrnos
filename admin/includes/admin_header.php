<?php
session_start();
// Asegúrate de que la ruta a db.php sea correcta desde la ubicación de este archivo
require_once __DIR__ . '/../../config/db.php';

// --- ¡COMPROBACIÓN DE SEGURIDAD VITAL! ---
// Si no hay sesión, o el rol no es 'admin', se redirige al login.
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    // Guardar un mensaje de error en la sesión (opcional)
    $_SESSION['error_msg'] = "Acceso denegado. Debe ser administrador.";
    header('Location: ../../auth/login.php');
    exit;
}

// Obtener nombre del admin para la bienvenida
$id_admin = $_SESSION['id_usuario'];
$admin_query = $conn->query("SELECT nombre, apellidos FROM usuarios WHERE id_usuario = $id_admin");
$admin_data = $admin_query->fetch_assoc();
$admin_nombre = $admin_data['nombre'] . " " . $admin_data['apellidos'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <!-- Google Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Estilo del Admin -->
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <h3>Admin Flores Hnos</h3>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <span class="material-symbols-outlined icon">dashboard</span> Dashboard
                    </a>
                </li>
                <li>
                    <a href="gestionar_buses.php">
                        <span class="material-symbols-outlined icon">directions_bus</span> Gestionar Buses
                    </a>
                </li>
                <li>
                    <a href="gestionar_usuarios.php">
                        <span class="material-symbols-outlined icon">group</span> Gestionar Usuarios
                    </a>
                </li>
                <li>
                    <a href="gestionar_rutas.php">
                        <span class="material-symbols-outlined icon">route</span> Gestionar Rutas
                    </a>
                </li>
                <li>
                    <a href="gestionar_viajes.php">
                        <span class="material-symbols-outlined icon">event_upcoming</span> Gestionar Viajes
                    </a>
                </li>
                <li>
                    <a href="reportes.php">
                        <span class="material-symbols-outlined icon">monitoring</span> Reportes
                    </a>
                </li>
                <li class="sidebar-divider"></li>
                <li>
                    <a href="../index.php" target="_blank">
                        <span class="material-symbols-outlined icon">visibility</span> Ver Sitio Web
                    </a>
                </li>
                <li>
                    <a href="../auth/logout.php">
                        <span class="material-symbols-outlined icon">logout</span> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </nav>
        <main class="admin-content">
            <header class="admin-topbar">
                <h2>Bienvenido, <?php echo htmlspecialchars($admin_nombre); ?></h2>
            </header>
            <div class="content-wrapper">