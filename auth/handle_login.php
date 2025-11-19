<?php
// auth/handle_login.php
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    // --- ¡ALERTA DE SEGURIDAD! ---
    // Tu SP `sp_login_usuario` compara contraseñas en texto plano.
    // Esto es muy inseguro para un sistema real.
    // Lo ideal sería:
    // 1. Guardar contraseñas hasheadas en la BD (ej: password_hash('123', PASSWORD_DEFAULT))
    // 2. En el login, hacer un SELECT para obtener el hash por email.
    // 3. Usar password_verify($password, $hash_de_la_bd) en PHP.
    //
    // PERO, para cumplir el requisito de "usar al máximo los procedimientos",
    // usaremos el SP que proporcionaste (sp_login_usuario) tal cual.
    // Los datos de prueba en tu SQL (pass: '123') funcionarán.

    // Llamada al Procedimiento Almacenado
    // Preparamos los parámetros de salida
    $conn->query("SET @p_resultado = 0;");
    $conn->query("SET @p_mensaje = '';");

    // Construimos la llamada
    $sql_call = "CALL sp_login_usuario('$email', '$password', @p_resultado, @p_mensaje);";

    if ($conn->query($sql_call)) {
        // Obtenemos los resultados de los parámetros OUT
        $result_sp = $conn->query("SELECT @p_resultado as id_usuario, @p_mensaje as rol;");
        $out_params = $result_sp->fetch_assoc();
        
        $id_usuario = $out_params['id_usuario'];
        $rol = $out_params['rol'];

        if ($id_usuario > 0) {
            // Login exitoso
            // Guardamos los datos en la sesión
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['rol'] = $rol;

            // Obtenemos más datos del usuario (nombre)
            $user_data_sql = "SELECT nombre, apellidos FROM usuarios WHERE id_usuario = $id_usuario";
            $user_result = $conn->query($user_data_sql);
            $user = $user_result->fetch_assoc();
            $_SESSION['nombre_usuario'] = $user['nombre'] . ' ' . $user['apellidos'];

            // Redirigir según el rol
            if ($rol == 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../cliente/index.php'); // O a la pág principal
            }
            exit;

        } else {
            // Login fallido (Credenciales incorrectas según el SP)
            $_SESSION['error_login'] = $rol; // $rol contendrá 'Credenciales incorrectas'
            header('Location: login.php');
            exit;
        }

    } else {
        // Error al llamar al SP
        $_SESSION['error_login'] = "Error en el sistema: " . $conn->error;
        header('Location: login.php');
        exit;
    }

} else {
    // Si no es POST, redirigir
    header('Location: login.php');
    exit;
}
?>
