<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_nutricional');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }
    
    // Establecer charset
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Función para limpiar datos de entrada
function limpiar_dato($dato) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($dato));
}

// Función para verificar sesión
function verificar_sesion() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: ../login.php');
        exit();
    }
}

// Función para verificar tipo de usuario
function verificar_tipo_usuario($tipo_requerido) {
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo_usuario'] !== $tipo_requerido) {
        header('Location: ../login.php');
        exit();
    }
}
?>
