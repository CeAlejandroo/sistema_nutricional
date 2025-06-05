<?php
// [HECHO] Datos de conexión a la base de datos
$host = "localhost";
$user = "root";
$password = ""; // [IMPORTANTE] Cambia esta línea si configuraste contraseña en XAMPP
$dbname = "sistema_nutricional";

// [REGLA] Crear conexión con la base de datos
$conn = new mysqli($host, $user, $password, $dbname);

// [REGLA] Verificar si la conexión falló
if ($conn->connect_error) {
    // [HECHO] Si falla, muestra mensaje y detiene ejecución
    die("Conexión fallida: " . $conn->connect_error);
}
?>
