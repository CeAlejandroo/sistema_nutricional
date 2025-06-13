<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
    $paciente_id = $_POST['paciente_id'];
    $calorias_totales = $_POST['calorias_diarias'];
    $proteinas_totales = $_POST['porcentaje_proteina'];
    $carbohidratos_totales = $_POST['porcentaje_carbos'];
    $grasas_totales = $_POST['porcentaje_grasa'];
    $activo = 1;

    if ($plan_id > 0) {
        // Editar plan existente
        $sql = "UPDATE planes_nutricionales SET paciente_id=?, calorias_totales=?, proteinas_totales=?, carbohidratos_totales=?, grasas_totales=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiii", $paciente_id, $calorias_totales, $proteinas_totales, $carbohidratos_totales, $grasas_totales, $plan_id);
        $stmt->execute();
    } else {
        // Nuevo plan
        $sql = "INSERT INTO planes_nutricionales 
            (paciente_id, calorias_totales, proteinas_totales, carbohidratos_totales, grasas_totales, activo, fecha_generacion)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiiii", $paciente_id, $calorias_totales, $proteinas_totales, $carbohidratos_totales, $grasas_totales, $activo);
        $stmt->execute();
    }
    header('Location: planes.php?success=1');
    exit();
}

// Eliminar plan
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM planes_nutricionales WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: planes.php?deleted=1');
    exit();
}
?>