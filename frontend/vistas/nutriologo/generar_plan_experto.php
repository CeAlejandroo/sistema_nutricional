<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}
require_once '../../includes/conexion.php';

// 1. Recibe los datos del paciente (pueden venir por POST o por consulta a la BD)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['paciente_id'])) {
    $paciente_id = intval($_POST['paciente_id']);
    // Obtén los datos del paciente desde la base de datos
    $sql = "SELECT nombre, edad, genero, peso, altura, objetivo, nivel_actividad FROM pacientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $paciente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) {
        echo json_encode(['success' => false, 'error' => 'Paciente no encontrado']);
        exit();
    }
    $paciente = $result->fetch_assoc();

    // 2. Prepara los datos para Python
    $datos_paciente = [
        'nombre' => $paciente['nombre'],
        'edad' => intval($paciente['edad']),
        'genero' => $paciente['genero'],
        'peso' => floatval($paciente['peso']),
        'altura' => floatval($paciente['altura']),
        'objetivo' => $paciente['objetivo'],
        'nivel_actividad' => $paciente['nivel_actividad']
    ];

    // 3. Guarda los datos en un archivo temporal
    $tempFile = tempnam(sys_get_temp_dir(), 'nutri_') . '.json';
    file_put_contents($tempFile, json_encode($datos_paciente));

    // 4. Ejecuta el script Python y captura la salida
    $python = 'C:\\Users\\teamv\\AppData\\Local\\Programs\\Python\\Python312\\python.exe';
    $script = __DIR__ . '/../../backend/motor_experto.py';
    $cmd = "$python " . escapeshellarg($script) . " " . escapeshellarg($tempFile) . " 2>&1";
    $output = shell_exec($cmd);
    file_put_contents(__DIR__ . '/debug_motor.txt', $output);

    // 5. Borra el archivo temporal
    unlink($tempFile);

    // 6. Procesa la respuesta de Python
    $resultado = json_decode($output, true);
    if (!$resultado || !isset($resultado['success'])) {
        echo json_encode(['success' => false, 'error' => 'Error al procesar el motor experto']);
        exit();
    }

    // 7. Devuelve el resultado al frontend (puedes mostrarlo o guardarlo)
    echo json_encode($resultado);
    exit();
}

// Si llegaste aquí por GET, muestra el formulario o la vista normal
if (!file_exists($tempFile)) {
    file_put_contents(__DIR__ . '/debug_motor.txt', 'Archivo temporal no existe: ' . $tempFile);
}
file_put_contents(__DIR__ . '/debug_tempfile.txt', file_get_contents($tempFile));
?>