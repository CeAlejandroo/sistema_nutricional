<?php
session_start();

// Verificamos si ya hay una sesión iniciada
if (isset($_SESSION['usuario'])) {
    // Redirigir al dashboard correspondiente según el rol
    switch ($_SESSION['usuario']['rol']) {
        case 'admin':
            header('Location: vistas/administrador/administrador.php');
            break;
        case 'nutriologo':
            header('Location: vistas/nutriologo/clientes.php');
            break;
    }
    exit();
}

require 'includes/conexion.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validación básica de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Correo electrónico no válido.";
    } elseif (empty($email) || empty($password)) {
        $message = "Por favor completa todos los campos.";
    } else {
        // Consulta: Buscar usuario por correo
        $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Si encontramos una coincidencia de usuario
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $nombre, $hash, $rol);
            $stmt->fetch();

            // Verificar que la contraseña coincida
            // TEMPORAL: para probar si funciona con texto plano
            if ($password === $hash) {
                // Usuario autenticado correctamente
                $_SESSION['usuario'] = [
                    'id' => $id,
                    'nombre' => $nombre,
                    'email' => $email,
                    'rol' => $rol
                ];
                // Redirigir según el rol del usuario
                switch ($rol) {
                    case 'admin':
                        header('Location: vistas/administrador/administrador.php');
                        break;
                    case 'nutriologo':
                        header('Location: vistas/nutriologo/clientes.php');
                        break;
                }
                exit();
            } else {
                $message = "Correo o contraseña incorrectos.";
            }
        } else {
            $message = "Correo o contraseña incorrectos.";
        }
        $stmt->close();
    }
}

$registrado = isset($_GET['registrado']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Login - Sistema Nutricional</title>
    <link rel="stylesheet" href="css/styles.css"/>
</head>
<body>
    <div class="container">
        <h2>Iniciar sesión</h2>

        <!-- Mostrar mensaje de registro exitoso -->
        <?php if ($registrado): ?>
            <p class="message" style="color:green;">Registro exitoso, ahora inicia sesión.</p>
        <?php endif; ?>

        <!-- Mostrar mensaje de error u otros -->
        <?php if ($message): ?>
            <p class="message"><?=htmlspecialchars($message)?></p>
        <?php endif; ?>

        <!-- Formulario de login -->
        <form method="POST" action="login.php">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required />

            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required />

            <button type="submit">Iniciar sesión</button>
        </form>
        <!-- <a href="includes/registro.php">Registrarse</a> -->
    </div>
</body>
</html>

