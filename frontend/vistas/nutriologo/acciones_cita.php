<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit('No autorizado');
}
require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $accion = $_POST['accion'];

    if ($accion === 'confirmar') {
        $sql = "UPDATE citas SET estado='confirmada' WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "ok";
    } elseif ($accion === 'cancelar') {
        $sql = "DELETE FROM citas WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo "ok";
    } elseif ($accion === 'editar') {
        $cliente = $_POST['cliente'];
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];
        $tipo = $_POST['tipo'];
        $notas = $_POST['notas'];
        $sql = "UPDATE citas SET cliente_nombre=?, fecha=?, hora=?, tipo=?, notas=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $cliente, $fecha, $hora, $tipo, $notas, $id);
        $stmt->execute();
        echo "ok";
    } else {
        echo "Acción no válida";
    }
}
?>