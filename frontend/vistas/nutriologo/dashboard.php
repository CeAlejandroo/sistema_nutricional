<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
require_once '../../includes/conexion.php';

// Obtener estadísticas del dashboard
$stats = [
    'total_clientes' => 0,
    'citas_hoy' => 0,
    'planes_activos' => 0,
    'progreso_promedio' => 0
];

// Consultar total de clientes
$result = $conn->query("SELECT COUNT(*) as total FROM pacientes");
if ($result) {
    $stats['total_clientes'] = $result->fetch_assoc()['total'];
}

// Consultar citas de hoy
$hoy = date('Y-m-d');
$result = $conn->query("SELECT COUNT(*) as total FROM citas WHERE fecha = '$hoy'");
if ($result) {
    $stats['citas_hoy'] = $result->fetch_assoc()['total'];
}

// Consultar planes activos
$result = $conn->query("SELECT COUNT(*) as total FROM planes_nutricionales WHERE activo = 1");
if ($result) {
    $stats['planes_activos'] = $result->fetch_assoc()['total'];
}

// Obtener próximas citas
$proximas_citas = [];
$result = $conn->query("
    SELECT c.fecha, c.hora, c.tipo, cl.nombre as cliente_nombre 
    FROM citas c 
    JOIN clientes cl ON c.cliente_id = cl.id 
    WHERE c.fecha = '$hoy' 
    ORDER BY c.hora ASC 
    LIMIT 3
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $proximas_citas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NutriManager</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Nutriologo</h1>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="clientes.php" class="nav-link">Clientes</a>
                    <a href="citas.php" class="nav-link">Citas</a>
                    <a href="planes.php" class="nav-link">Planes</a>
                    <a href="../../logout.php" class="nav-link">Cerrar Sesión</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="welcome">
                <h2>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                <p>Panel de control de tu práctica nutricional</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total_clientes']; ?></h3>
                        <p>Total Clientes</p>
                        <span class="stat-change"></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['citas_hoy']; ?></h3>
                        <p>Citas Hoy</p>
                        <span class="stat-change"></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-text"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['planes_activos']; ?></h3>
                        <p>Planes Activos</p>
                        <span class="stat-change"></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3>78%</h3>
                        <p>Progreso Promedio</p>
                        <span class="stat-change"></span>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3>Acciones Rápidas</h3>
                        <p>Gestiona tu práctica nutricional</p>
                    </div>
                    <div class="card-content">
                        <a href="clientes.php?action=new" class="action-btn">
                            <i class="fas fa-plus"></i>
                            Agregar Nuevo Cliente
                        </a>
                        <a href="citas.php?action=new" class="action-btn">
                            <i class="fas fa-calendar-plus"></i>
                            Agendar Cita
                        </a>
                        <a href="planes.php?action=new" class="action-btn">
                            <i class="fas fa-file-plus"></i>
                            Crear Plan Nutricional
                        </a>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="card">
                    <div class="card-header">
                        <h3>Próximas Citas</h3>
                        <p>Citas programadas para hoy</p>
                    </div>
                    <div class="card-content">
                        <?php if (empty($proximas_citas)): ?>
                            <p class="no-data">No hay citas programadas para hoy</p>
                        <?php else: ?>
                            <?php foreach ($proximas_citas as $cita): ?>
                                <div class="appointment-item">
                                    <div class="appointment-info">
                                        <h4><?php echo htmlspecialchars($cita['cliente_nombre']); ?></h4>
                                        <p><?php echo $cita['hora']; ?> - <?php echo htmlspecialchars($cita['tipo']); ?></p>
                                    </div>
                                    <button class="btn-small">Ver</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
