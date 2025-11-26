<?php
// config/db.php

// Configuración de la base de datos LOCAL (XAMPP)
define('DB_HOST', 'srv812.hstgr.io'); // Tu host, usualmente localhost
define('DB_USER', 'u914095763_g7');      // Tu usuario de XAMPP, usualmente root
define('DB_PASS', 'bdvCl176');          // Tu contraseña de XAMPP, usualmente vacía
define('DB_NAME', 'u914095763_g7');  // El nombre de tu base de datos
define('DB_PORT', '3306');      // Puerto de MySQL (Corregido a 3306)

// Inicializamos la conexión como nula por defecto
$conn = null;
$db_connection_error = null; // Variable para saber si hubo error (opcional para mostrar en UI)

// Usamos try-catch para evitar que la página muera si falla la BD
try {
    // El '@' suprime los warnings nativos de PHP para manejarlos nosotros
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    // Verificamos si hubo error lógico en la conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Si conecta, configuramos UTF-8
    if (!$conn->set_charset("utf8")) {
        // No es fatal, pero es bueno controlarlo
        error_log("Error cargando utf8: " . $conn->error);
    }

} catch (Exception $e) {
    // CAPTURAMOS EL ERROR PERO NO DETENEMOS EL SCRIPT
    // Guardamos el error por si queremos mostrarlo discretamente, pero $conn se queda como null
    $db_connection_error = $e->getMessage();
    $conn = null; 
}

// La sesión debe iniciar sí o sí, funcione la BD o no
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>