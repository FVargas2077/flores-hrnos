<?php
// config/db.php

// Configuración de la base de datos REMOTA (Hosting)
define('DB_HOST', 'srv812.hstgr.io'); // Tu host, usualmente localhost
define('DB_USER', 'u914095763_g7');      // Tu usuario de XAMPP, usualmente root
define('DB_PASS', 'bdvCl176');          // Tu contraseña de XAMPP, usualmente vacía
define('DB_NAME', 'u914095763_g7');  // El nombre de tu base de datos
define('DB_PORT', '3306');      // Puerto de MySQL (Corregido a 3306)

// Crear la conexión usando mysqli
// Omitimos el puerto, ya que 3306 es el estándar 
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error . " (Host: " . DB_HOST . ")");
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