<?php
require 'conexion.php';

$mensaje = '';

// Hecho: Se recibe una solicitud POST para registrar un nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $edad = (int)$_POST['edad'];
    $sexo = $_POST['sexo'];
    $rol = 'cliente'; // Hecho: Todo usuario registrado por esta vista será cliente por defecto

    // Regla: Todos los campos deben estar llenos para proceder con el registro
    if (empty($nombre) || empty($correo) || empty($password) || empty($edad) || empty($sexo)) {
        $mensaje = 'Todos los campos son obligatorios.';
    } else {
        // Regla: No puede haber dos usuarios con el mismo correo
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        // Hecho: Si el correo ya está registrado, se bloquea el registro
        if ($stmt->num_rows > 0) {
            $mensaje = "El correo ya está registrado. Usa otro.";
        } else {
            $stmt->close();

            // Regla: Se debe guardar la contraseña de forma segura (hash)
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Hecho: Si todo es válido, se registra al nuevo usuario en la base de datos
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contraseña, edad, sexo, rol) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiss", $nombre, $correo, $hash, $edad, $sexo, $rol);

            // Regla: Si el registro es exitoso, se redirige al login
            if ($stmt->execute()) {
                header("location:/WEB/Programacion_logica_funcional/nutricion_experto/frontend/login.php");
                exit();
            } else {
                $mensaje = 'Error al registrar: ' . $stmt->error;
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="container">
        <h2>Registro de nuevo usuario</h2>

        <?php if ($mensaje): ?>
            <p class="message"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <label for="nombre">Nombre completo:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="correo">Correo electrónico:</label>
            <input type="email" id="correo" name="correo" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="edad">Edad:</label>
            <input type="number" id="edad" name="edad" min="1" required>

            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required> ?
                <option value="">Selecciona</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
                <option value="No binario">No binario</option>
            </select>

            <button type="submit">Registrarse</button>
        </form>
        <a href="/WEB/Programacion_logica_funcional/nutricion_experto/frontend/login.php">Volver al login</a>
    </div>
</body>

</html>
