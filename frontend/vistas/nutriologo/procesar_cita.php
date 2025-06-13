<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente = $_POST['cliente'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $tipo = $_POST['tipo'];
    $notas = $_POST['notas'];

    // Ajusta el nombre de tu tabla y columnas según tu base de datos
    $sql = "INSERT INTO citas (cliente_nombre, fecha, hora, tipo, notas, estado)
            VALUES (?, ?, ?, ?, ?, 'programada')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $cliente, $fecha, $hora, $tipo, $notas);

    if ($stmt->execute()) {
        // Redirige de vuelta a la página de citas
        header("Location: citas.php");
        exit();
    } else {
        echo "Error al guardar la cita.";
    }
}
?>