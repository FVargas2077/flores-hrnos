<?php
// auth/handle_register.php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar contraseñas
    if ($_POST['password'] !== $_POST['password_confirm']) {
        $_SESSION['error_register'] = "Las contraseñas no coinciden.";
        header('Location: register.php');
        exit;
    }

    // Recoger datos del formulario
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $dni = $conn->real_escape_string($_POST['dni']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']); // Texto plano
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $fecha_nacimiento = $conn->real_escape_string($_POST['fecha_nacimiento']);
    $genero = $conn->real_escape_string($_POST['genero']);
    
    // Asignar rol 'cliente' (id_rol = 2 según tu db_buses.sql)
    $id_rol = 2; 

    // --- ¡ADVERTENCIA DE SEGURIDAD! ---
    // Estamos guardando la contraseña en texto plano para que coincida
    // con tu SP de login (sp_login_usuario).
    // En un proyecto real, NUNCA hagas esto. Deberías usar:
    // $hash_password = password_hash($password, PASSWORD_DEFAULT);
    // y guardar $hash_password en la BD.
    // Y tu SP de login debería ser modificado (o no usarse para login).

    // Verificar si el DNI o Email ya existen
    $check_sql = "SELECT * FROM usuarios WHERE dni = '$dni' OR email = '$email'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        if ($existing['dni'] == $dni) {
            $_SESSION['error_register'] = "El DNI ingresado ya está registrado.";
        } else {
            $_SESSION['error_register'] = "El Email ingresado ya está registrado.";
        }
        header('Location: register.php');
        exit;
    }

    // Insertar el nuevo usuario
    $sql = "INSERT INTO usuarios (id_rol, nombre, apellidos, dni, email, password, telefono, fecha_nacimiento, genero, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $_SESSION['error_register'] = "Error al preparar la consulta: " . $conn->error;
        header('Location: register.php');
        exit;
    }
    
    // 'ss' para string, 'i' para integer, 'd' para double
    $stmt->bind_param("issssssss", $id_rol, $nombre, $apellidos, $dni, $email, $password, $telefono, $fecha_nacimiento, $genero);

    if ($stmt->execute()) {
        // Registro exitoso. Ahora, loguear al usuario
        
        // Llamamos al SP de login para crear la sesión
        $conn->query("SET @p_resultado = 0;");
        $conn->query("SET @p_mensaje = '';");
        $conn->query("CALL sp_login_usuario('$email', '$password', @p_resultado, @p_mensaje);");

        $result_sp = $conn->query("SELECT @p_resultado as id_usuario, @p_mensaje as rol;");
        $out_params = $result_sp->fetch_assoc();
        
        $id_usuario = $out_params['id_usuario'];
        $rol = $out_params['rol'];

        if ($id_usuario > 0) {
            // Login automático exitoso
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['rol'] = $rol;
            $_SESSION['nombre_usuario'] = $nombre . ' ' . $apellidos;
            
            header('Location: ../cliente/index.php'); // Redirigir al dashboard del cliente
        } else {
            // Hubo un error en el login post-registro (raro, pero posible)
            $_SESSION['error_login'] = "Registro exitoso, pero falló el inicio de sesión automático. Por favor, intente ingresar manualmente.";
            header('Location: login.php');
        }
        exit;

    } else {
        $_SESSION['error_register'] = "Error al registrar el usuario: " . $stmt->error;
        header('Location: register.php');
        exit;
    }

} else {
    header('Location: register.php');
    exit;
}
?>
