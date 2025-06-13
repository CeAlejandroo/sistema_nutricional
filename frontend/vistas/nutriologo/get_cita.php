<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(403);
    exit('No autorizado');
}
require_once '../../includes/conexion.php';

$id = intval($_GET['id']);
$sql = "SELECT * FROM citas WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());

// Todas las citas
$sql_todas = "SELECT * FROM citas ORDER BY fecha DESC, hora DESC";
$result_todas = $conn->query($sql_todas);
$todas_citas = $result_todas->fetch_all(MYSQLI_ASSOC);
?>