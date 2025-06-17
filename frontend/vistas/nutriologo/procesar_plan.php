<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Agrega este bloque al inicio ---
    $plan_id = isset($_POST['plan_id']) ? intval($_POST['plan_id']) : 0;
    $paciente_id = $_POST['paciente_id'];
    $calorias_totales = $_POST['calorias_diarias'];
    $proteinas_totales = $_POST['porcentaje_proteina'];
    $carbohidratos_totales = $_POST['porcentaje_carbos'];
    $grasas_totales = $_POST['porcentaje_grasa'];
    $activo = 1;

    // Validación básica
    if (empty($paciente_id) || empty($calorias_totales) || empty($proteinas_totales) || empty($carbohidratos_totales) || empty($grasas_totales)) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
        exit();
    }
    // --- Fin del bloque agregado ---

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

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
            exit();
        }
        // Obtén el ID del plan recién insertado
        $plan_id = $stmt->insert_id;

        // Guardar comidas sugeridas si existen
        if (isset($_POST['comidas'])) {
            $comidas = json_decode($_POST['comidas'], true);
            if (is_array($comidas) && count($comidas) > 0) {
                $sql_comida = "INSERT INTO comidas_plan (plan_id, tipo_comida, alimento_id, cantidad_gramos, calorias) VALUES (?, ?, ?, ?, ?)";
                $stmt_comida = $conn->prepare($sql_comida);
                foreach ($comidas as $comida) {
                    $tipo_comida = isset($comida['tipo_comida']) ? strtolower($comida['tipo_comida']) : '';
                    // Normaliza snacks: snack, snack_1, snack_2, etc. → snack
                    if (strpos($tipo_comida, 'snack') === 0) {
                        $tipo_comida = 'snack';
                    }
                    $alimento_id = isset($comida['alimento_id']) ? intval($comida['alimento_id']) : 0;
                    $cantidad_gramos = isset($comida['cantidad_gramos']) ? floatval($comida['cantidad_gramos']) : 0;
                    $calorias = isset($comida['calorias']) ? floatval($comida['calorias']) : 0;
                    $stmt_comida->bind_param(
                        "isidd",
                        $plan_id,
                        $tipo_comida,
                        $alimento_id,
                        $cantidad_gramos,
                        $calorias
                    );
                    $stmt_comida->execute();
                }
            }
        }
        echo json_encode(['success' => true]);
        exit();
    }
    echo json_encode(['success' => true]);
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