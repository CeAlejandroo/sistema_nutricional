<?php
require_once '../../includes/conexion.php';
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM planes_nutricionales WHERE id=?");
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
    exit();
}
$plan = $stmt->get_result()->fetch_assoc();

$stmt2 = $conn->prepare("SELECT * FROM comidas_plan WHERE plan_id=?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$comidas = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// Restructuring the response to match the suggested code change
$response = [
    'success' => true,
    'plan' => $plan,
    'comidas' => array_map(function($comida) {
        return [
            'tipo_comida' => $comida['tipo_comida'],
            'alimento_id' => $comida['alimento_id'],
            'cantidad_gramos' => $comida['cantidad_gramos'],
            'calorias' => $comida['calorias']
        ];
    }, $comidas)
];

echo json_encode($response);
exit();
?>