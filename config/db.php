<?php
// config/db.php

// Configuración de la base de datos para XAMPP (MariaDB)
define('DB_HOST', 'localhost'); // Tu host, usualmente localhost
define('DB_USER', 'root');      // Tu usuario de XAMPP, usualmente root
define('DB_PASS', '');          // Tu contraseña de XAMPP, usualmente vacía
define('DB_NAME', 'db_buses');  // El nombre de tu base de datos
define('DB_PORT', '3306');      // Puerto de MySQL (Corregido a 3306)

// Crear la conexión usando mysqli
// Se añade el puerto a la conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error . " (Puerto: " . DB_PORT . ")");
}

// Establecer el charset a UTF-8 para soportar tildes y caracteres especiales
if (!$conn->set_charset("utf8")) {
    printf("Error cargando el conjunto de caracteres utf8: %s\n", $conn->error);
    exit();
}

// Iniciar la sesión en todas las páginas que incluyan este archivo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

