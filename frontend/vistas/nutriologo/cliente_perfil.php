<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

// Obtener ID del cliente
$cliente_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($cliente_id === 0) {
    header('Location: clientes.php');
    exit();
}

// Obtener información del cliente
$cliente = null;
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $cliente = $result->fetch_assoc();
} else {
    header('Location: clientes.php');
    exit();
}

// Obtener historial clínico
$historial = [];
$result = $conn->query("SELECT * FROM historial_clinico WHERE cliente_id = $cliente_id ORDER BY fecha DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $historial[] = $row;
    }
}

// Obtener progreso
$progreso = [];
$result = $conn->query("SELECT * FROM seguimiento_progreso WHERE cliente_id = $cliente_id ORDER BY fecha ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $progreso[] = $row;
    }
}

// Obtener planes nutricionales
$planes = [];
$result = $conn->query("SELECT * FROM planes_nutricionales WHERE cliente_id = $cliente_id ORDER BY fecha_creacion DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $planes[] = $row;
    }
}

// Obtener citas
$citas = [];
$result = $conn->query("SELECT * FROM citas WHERE cliente_id = $cliente_id ORDER BY fecha DESC, hora DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $citas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($cliente['nombre']); ?> - NutriManager</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/cliente_perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Perfil cliente</h1>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="clientes.php" class="nav-link active">Clientes</a>
                    <a href="citas.php" class="nav-link">Citas</a>
                    <a href="planes.php" class="nav-link">Planes</a>
                    <a href="../../logout.php" class="nav-link">Cerrar Sesión</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="header-left">
                    <a href="clientes.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Volver
                    </a>
                    <h2>Perfil de <?php echo htmlspecialchars($cliente['nombre']); ?></h2>
                </div>
            </div>

            <!-- Client Info Cards -->
            <div class="info-grid">
                <div class="card">
                    <div class="card-header">
                        <h3>Información Personal</h3>
                    </div>
                    <div class="card-content">
                        <div class="info-item">
                            <strong>Email:</strong> <?php echo htmlspecialchars($cliente['email']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['telefono'] ?? 'No especificado'); ?>
                        </div>
                        <div class="info-item">
                            <strong>Edad:</strong> <?php echo $cliente['edad'] ?? 'N/A'; ?> años
                        </div>
                        <div class="info-item">
                            <strong>Altura:</strong> <?php echo $cliente['altura'] ?? 'N/A'; ?> cm
                        </div>
                        <div class="info-item">
                            <strong>Objetivo:</strong> <?php echo htmlspecialchars($cliente['objetivo'] ?? 'No especificado'); ?>
                        </div>
                        <div class="status-badge <?php echo $cliente['activo'] ? 'active' : 'inactive'; ?>">
                            <?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Progreso Actual</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($progreso)): ?>
                            <?php $ultimo_progreso = end($progreso); ?>
                            <div class="info-item">
                                <strong>Peso Actual:</strong> <?php echo $ultimo_progreso['peso'] ?? 'N/A'; ?> kg
                            </div>
                            <div class="info-item">
                                <strong>Grasa Corporal:</strong> <?php echo $ultimo_progreso['grasa_corporal'] ?? 'N/A'; ?>%
                            </div>
                            <div class="info-item">
                                <strong>Última Medición:</strong> <?php echo $ultimo_progreso['fecha']; ?>
                            </div>
                        <?php else: ?>
                            <p class="no-data">No hay datos de progreso registrados</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Acciones Rápidas</h3>
                    </div>
                    <div class="card-content">
                        <button class="action-btn" onclick="openModal('citaModal')">
                            <i class="fas fa-calendar"></i>
                            Agendar Cita
                        </button>
                        <button class="action-btn" onclick="openModal('planModal')">
                            <i class="fas fa-file-text"></i>
                            Nuevo Plan
                        </button>
                        <button class="action-btn" onclick="openModal('medicionModal')">
                            <i class="fas fa-plus"></i>
                            Registrar Medición
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-btn active" onclick="showTab('historial')">Historial Clínico</button>
                    <button class="tab-btn" onclick="showTab('progreso')">Progreso</button>
                    <button class="tab-btn" onclick="showTab('planes')">Planes Nutricionales</button>
                    <button class="tab-btn" onclick="showTab('citas')">Citas</button>
                </div>

                <!-- Tab Content: Historial -->
                <div id="historial" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h3>Historial Clínico</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($historial)): ?>
                                <p class="no-data">No hay registros en el historial clínico</p>
                            <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($historial as $registro): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <h4><?php echo htmlspecialchars($registro['tipo']); ?> - <?php echo $registro['fecha']; ?></h4>
                                                <p><?php echo htmlspecialchars($registro['descripcion']); ?></p>
                                                <?php if ($registro['observaciones']): ?>
                                                    <p class="observaciones"><?php echo htmlspecialchars($registro['observaciones']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Progreso -->
                <div id="progreso" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Gráfico de Progreso</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($progreso)): ?>
                                <p class="no-data">No hay datos de progreso para mostrar</p>
                            <?php else: ?>
                                <canvas id="progressChart" width="400" height="200"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Planes -->
                <div id="planes" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Planes Nutricionales</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($planes)): ?>
                                <p class="no-data">No hay planes nutricionales asignados</p>
                            <?php else: ?>
                                <div class="plans-list">
                                    <?php foreach ($planes as $plan): ?>
                                        <div class="plan-item">
                                            <div class="plan-info">
                                                <h4><?php echo htmlspecialchars($plan['nombre']); ?></h4>
                                                <p><?php echo $plan['calorias_diarias']; ?> kcal/día - <?php echo htmlspecialchars($plan['tipo']); ?></p>
                                                <p class="plan-date">Creado: <?php echo $plan['fecha_creacion']; ?></p>
                                            </div>
                                            <div class="plan-status">
                                                <span class="status-badge <?php echo $plan['estado'] === 'activo' ? 'active' : 'inactive'; ?>">
                                                    <?php echo ucfirst($plan['estado']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Citas -->
                <div id="citas" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Historial de Citas</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($citas)): ?>
                                <p class="no-data">No hay citas registradas</p>
                            <?php else: ?>
                                <div class="appointments-list">
                                    <?php foreach ($citas as $cita): ?>
                                        <div class="appointment-item">
                                            <div class="appointment-info">
                                                <h4><?php echo htmlspecialchars($cita['tipo']); ?></h4>
                                                <p><?php echo $cita['fecha']; ?> - <?php echo $cita['hora']; ?></p>
                                                <?php if ($cita['notas']): ?>
                                                    <p class="appointment-notes"><?php echo htmlspecialchars($cita['notas']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="appointment-status">
                                                <span class="status-badge <?php echo $cita['estado'] === 'completada' ? 'active' : 'inactive'; ?>">
                                                    <?php echo ucfirst($cita['estado']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Función para cambiar tabs
        function showTab(tabName) {
            // Ocultar todos los tabs
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Remover clase active de todos los botones
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));
            
            // Mostrar el tab seleccionado
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Gráfico de progreso
        <?php if (!empty($progreso)): ?>
        const ctx = document.getElementById('progressChart').getContext('2d');
        const progressData = {
            labels: [<?php echo "'" . implode("','", array_column($progreso, 'fecha')) . "'"; ?>],
            datasets: [{
                label: 'Peso (kg)',
                data: [<?php echo implode(',', array_column($progreso, 'peso')); ?>],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }, {
                label: 'Grasa Corporal (%)',
                data: [<?php echo implode(',', array_column($progreso, 'grasa_corporal')); ?>],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: progressData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
