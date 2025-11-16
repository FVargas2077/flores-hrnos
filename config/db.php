<?php
// config/db.php

// Configuración de la base de datos para XAMPP (MariaDB)
define('DB_HOST', 'localhost'); // Tu host, usualmente localhost
define('DB_USER', 'root');      // Tu usuario de XAMPP, usualmente root
define('DB_PASS', '');          // Tu contraseña de XAMPP, usualmente vacía
define('DB_NAME', 'db_buses');  // El nombre de tu base de datos
define('DB_PORT', '3306');      // Puerto de MySQL (Corregido a 3306)

// --- INICIO DE MODIFICACIÓN ---

// Variable global para almacenar el error de conexión
$db_connection_error = null;
$conn = null;

// Intentar la conexión
try {
    // Se suprime el error de conexión nativo de PHP con '@' 
    // para manejarlo nosotros mismos.
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    // Verificar la conexión
    if ($conn->connect_error) {
        // En lugar de die(), guardamos el error
        throw new Exception("Error de conexión: " . $conn->connect_error . " (Puerto: " . DB_PORT . ")");
    }

    // Establecer el charset a UTF-8 para soportar tildes y caracteres especiales
    if (!$conn->set_charset("utf8")) {
        throw new Exception("Error cargando el conjunto de caracteres utf8: " . $conn->error);
    }

} catch (Exception $e) {
    // Si algo falló (conexión o charset), guardamos el mensaje
    $db_connection_error = $e->getMessage();
    $conn = null; // Nos aseguramos de que $conn sea nulo
}

// --- FIN DE MODIFICACIÓN ---


// Iniciar la sesión en todas las páginas que incluyan este archivo
// Esto puede correr incluso si la BD falla.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>