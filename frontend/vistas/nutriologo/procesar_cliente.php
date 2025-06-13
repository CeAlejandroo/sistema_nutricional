<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = isset($_POST['cliente_id']) ? intval($_POST['cliente_id']) : 0;
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido'] ?? '');
    $edad = !empty($_POST['edad']) ? (int) $_POST['edad'] : null;
    $genero = trim($_POST['genero'] ?? '');
    $peso = !empty($_POST['peso']) ? (float) $_POST['peso'] : null;
    $altura = !empty($_POST['altura']) ? (float) $_POST['altura'] : null;
    $nivel_actividad = trim($_POST['nivel_actividad'] ?? '');
    $objetivo = trim($_POST['objetivo']);
    $restricciones = trim($_POST['observaciones'] ?? '');
    $enfermedades = trim($_POST['enfermedades'] ?? '');
    $nutriologo_id = $_SESSION['usuario']['id'];

    // Validaciones básicas
    if (empty($nombre)) {
        $_SESSION['error'] = "El nombre es obligatorio";
        header('Location: clientes.php');
        exit();
    }

    if ($cliente_id > 0) {
        // Actualizar
        $stmt = $conn->prepare("UPDATE pacientes SET nombre=?, apellido=?, edad=?, genero=?, peso=?, altura=?, nivel_actividad=?, objetivo=?, restricciones_alimentarias=?, enfermedades=? WHERE id=?");
        if (!$stmt) {
            $_SESSION['error'] = "Error en la consulta SQL: " . $conn->error;
            header('Location: clientes.php');
            exit();
        }
        $stmt->bind_param("ssisdsssssi", $nombre, $apellido, $edad, $genero, $peso, $altura, $nivel_actividad, $objetivo, $restricciones, $enfermedades, $cliente_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Paciente actualizado exitosamente";
        } else {
            $_SESSION['error'] = "Error al actualizar el paciente: " . $conn->error;
        }
        $stmt->close();
    } else {
        // Insertar nuevo paciente
        $stmt = $conn->prepare("INSERT INTO pacientes (nombre, apellido, edad, genero, peso, altura, nivel_actividad, objetivo, restricciones_alimentarias, enfermedades, nutriologo_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        if ($stmt) {
            $stmt->bind_param("ssisdsssssi", $nombre, $apellido, $edad, $genero, $peso, $altura, $nivel_actividad, $objetivo, $restricciones, $enfermedades, $nutriologo_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Paciente agregado exitosamente";
            } else {
                $_SESSION['error'] = "Error al agregar el paciente: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Error en la consulta SQL: " . $conn->error;
        }
    }

    header('Location: clientes.php');
    exit();
}

// Procesar eliminación
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM pacientes WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Paciente eliminado exitosamente";
    } else {
        $_SESSION['error'] = "Error al eliminar el paciente: " . $conn->error;
    }
    $stmt->close();
    header('Location: clientes.php');
    exit();
}
?>
<script src="../../js/clientes.js"></script>
</body>
</html>
<?php
function objetivo_legible($objetivo) {
    switch ($objetivo) {
        case 'perder_peso': return 'Pérdida de peso';
        case 'ganar_musculo': return 'Ganar músculo';
        case 'mantener_peso': return 'Mantener peso';
        case 'ganar_peso': return 'Ganar peso';
        default: return 'No especificado';
    }
}
?>
<span>Objetivo: <?php echo objetivo_legible($cliente['objetivo'] ?? ''); ?></span>