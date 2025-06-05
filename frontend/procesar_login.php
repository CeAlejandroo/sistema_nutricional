<?php
session_start();
require_once 'includes/conexion.php';

// [HECHO] Recibimos los datos del formulario
$email = $_POST['email'];
$password = $_POST['password'];

// [REGLA] Consulta para buscar un usuario con ese correo
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// [HECHO] Si existe un usuario con ese email
if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();

    // [REGLA] Verificamos la contraseña con hash almacenado
    if (password_verify($password, $usuario['password'])) {
        // [HECHO] Credenciales válidas; guardar datos en sesión
        $_SESSION['id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];

        // [REGLA] Redirigir según el rol del usuario
        switch ($usuario['rol']) {
            case 'admin':
                header('Location: vistas/administrador/administrador.php');
                break;
            case 'nutriologo':
                header("Location: vistas/nutriologo/clientes.php");
                break;
            case 'cliente':
                header("Location: vistas/cliente/clientes.php");
                break;
            default:
                // [REGLA] Rol no reconocido
                $_SESSION['error'] = "Rol no reconocido.";
                header("Location: login.php");
        }
        exit();
    } else {
        // [HECHO] Contraseña incorrecta
        $_SESSION['error'] = "Contraseña incorrecta.";
        header("Location: login.php");
        exit();
    }
} else {
    // [HECHO] El usuario no existe
    $_SESSION['error'] = "Usuario no encontrado.";
    header("Location: login.php");
    exit();
}
?>