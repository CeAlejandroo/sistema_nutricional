<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

// Obtener citas
$fecha_seleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Citas de hoy
$citas_hoy = [];
$result = $conn->query("
    SELECT c.*, cl.nombre as cliente_nombre 
    FROM citas c 
    JOIN clientes cl ON c.cliente_id = cl.id 
    WHERE c.fecha = '$fecha_seleccionada' 
    ORDER BY c.hora ASC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $citas_hoy[] = $row;
    }
}

// Próximas citas
$proximas_citas = [];
$result = $conn->query("
    SELECT c.*, cl.nombre as cliente_nombre 
    FROM citas c 
    JOIN clientes cl ON c.cliente_id = cl.id 
    WHERE c.fecha > '$fecha_seleccionada' 
    ORDER BY c.fecha ASC, c.hora ASC 
    LIMIT 10
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
    <title>Citas - NutriManager</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/citas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>NutriManager</h1>
                <nav class="nav">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="clientes.php" class="nav-link">Clientes</a>
                    <a href="citas.php" class="nav-link active">Citas</a>
                    <a href="planes.php" class="nav-link">Planes</a>
                    <a href="../../logout.php" class="nav-link">Cerrar Sesión</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h2>Gestión de Citas</h2>
                <button class="btn-primary" onclick="openModal('citaModal')">
                    <i class="fas fa-plus"></i>
                    Nueva Cita
                </button>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <div class="tabs-nav">
                    <button class="tab-btn active" onclick="showTab('hoy')">Hoy</button>
                    <button class="tab-btn" onclick="showTab('proximas')">Próximas</button>
                    <button class="tab-btn" onclick="showTab('calendario')">Calendario</button>
                </div>

                <!-- Tab Content: Hoy -->
                <div id="hoy" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h3>
                                <i class="fas fa-calendar"></i>
                                Citas de Hoy - <?php echo $fecha_seleccionada; ?>
                            </h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($citas_hoy)): ?>
                                <p class="no-data">No hay citas programadas para hoy</p>
                            <?php else: ?>
                                <div class="appointments-list">
                                    <?php foreach ($citas_hoy as $cita): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-time">
                                                <i class="fas fa-clock"></i>
                                                <?php echo $cita['hora']; ?>
                                            </div>
                                            <div class="appointment-info">
                                                <h4><?php echo htmlspecialchars($cita['cliente_nombre']); ?></h4>
                                                <p><?php echo htmlspecialchars($cita['tipo']); ?></p>
                                                <?php if ($cita['notas']): ?>
                                                    <p class="appointment-notes"><?php echo htmlspecialchars($cita['notas']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="appointment-actions">
                                                <span class="status-badge <?php echo $cita['estado'] === 'confirmada' ? 'confirmed' : 'pending'; ?>">
                                                    <?php echo ucfirst($cita['estado']); ?>
                                                </span>
                                                <div class="action-buttons">
                                                    <?php if ($cita['estado'] === 'programada'): ?>
                                                        <button class="btn-icon btn-success" title="Confirmar" onclick="confirmarCita(<?php echo $cita['id']; ?>)">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn-icon btn-danger" title="Cancelar" onclick="cancelarCita(<?php echo $cita['id']; ?>)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn-icon" title="Editar" onclick="editarCita(<?php echo $cita['id']; ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Próximas -->
                <div id="proximas" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Próximas Citas</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($proximas_citas)): ?>
                                <p class="no-data">No hay próximas citas programadas</p>
                            <?php else: ?>
                                <div class="appointments-list">
                                    <?php foreach ($proximas_citas as $cita): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-date">
                                                <div class="date-day"><?php echo date('d', strtotime($cita['fecha'])); ?></div>
                                                <div class="date-month"><?php echo date('M', strtotime($cita['fecha'])); ?></div>
                                                <div class="appointment-time"><?php echo $cita['hora']; ?></div>
                                            </div>
                                            <div class="appointment-info">
                                                <h4><?php echo htmlspecialchars($cita['cliente_nombre']); ?></h4>
                                                <p><?php echo htmlspecialchars($cita['tipo']); ?></p>
                                            </div>
                                            <div class="appointment-actions">
                                                <span class="status-badge <?php echo $cita['estado'] === 'confirmada' ? 'confirmed' : 'pending'; ?>">
                                                    <?php echo ucfirst($cita['estado']); ?>
                                                </span>
                                                <button class="btn-icon" title="Editar" onclick="editarCita(<?php echo $cita['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Calendario -->
                <div id="calendario" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Vista de Calendario</h3>
                        </div>
                        <div class="card-content">
                            <div class="calendar-container">
                                <div class="calendar-header">
                                    <button class="btn-calendar" onclick="previousMonth()">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <h4 id="currentMonth">Marzo 2024</h4>
                                    <button class="btn-calendar" onclick="nextMonth()">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="calendar-grid">
                                    <div class="calendar-day-header">Dom</div>
                                    <div class="calendar-day-header">Lun</div>
                                    <div class="calendar-day-header">Mar</div>
                                    <div class="calendar-day-header">Mié</div>
                                    <div class="calendar-day-header">Jue</div>
                                    <div class="calendar-day-header">Vie</div>
                                    <div class="calendar-day-header">Sáb</div>
                                    
                                    <!-- Días del calendario -->
                                    <?php for ($i = 1; $i <= 31; $i++): ?>
                                        <div class="calendar-day <?php echo ($i == 15 || $i == 16) ? 'has-appointments' : ''; ?>">
                                            <span class="day-number"><?php echo $i; ?></span>
                                            <?php if ($i == 15): ?>
                                                <div class="appointment-indicator">3 citas</div>
                                            <?php elseif ($i == 16): ?>
                                                <div class="appointment-indicator">1 cita</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/citas.js"></script>
</body>
</html>
