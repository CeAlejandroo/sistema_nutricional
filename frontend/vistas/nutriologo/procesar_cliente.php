<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar nuevo cliente
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $edad = !empty($_POST['edad']) ? (int)$_POST['edad'] : null;
    $altura = !empty($_POST['altura']) ? (int)$_POST['altura'] : null;
    $peso = !empty($_POST['peso']) ? (float)$_POST['peso'] : null;
    $objetivo = trim($_POST['objetivo']);
    $observaciones = trim($_POST['observaciones']);
    
    // Validaciones básicas
    if (empty($nombre) || empty($email)) {
        $_SESSION['error'] = "Nombre y email son obligatorios";
        header('Location: clientes.php');
        exit();
    }
    
    // Verificar si el email ya existe
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Ya existe un cliente con ese email";
        header('Location: clientes.php');
        exit();
    }
    
    // Insertar nuevo cliente
    $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, edad, altura, peso, objetivo, observaciones, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
    $stmt->bind_param("sssiidss", $nombre, $email, $telefono, $edad, $altura, $peso, $objetivo, $observaciones);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Cliente agregado exitosamente";
    } else {
        $_SESSION['error'] = "Error al agregar el cliente: " . $conn->error;
    }
    
    header('Location: clientes.php');
    exit();
}

// Procesar eliminación
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("UPDATE clientes SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Cliente eliminado exitosamente";
    } else {
        $_SESSION['error'] = "Error al eliminar el cliente: " . $conn->error;
    }
    
    header('Location: clientes.php');
    exit();
}
?>
