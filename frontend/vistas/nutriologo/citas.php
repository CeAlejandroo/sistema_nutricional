<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

// Establecer zona horaria
date_default_timezone_set('America/Mexico_City'); // O tu zona horaria local

// Obtener citas
$fecha_seleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Citas de hoy
$fecha_hoy = date('Y-m-d');
$sql_hoy = "SELECT * FROM citas WHERE fecha = ? ORDER BY hora ASC";
$stmt_hoy = $conn->prepare($sql_hoy);
$stmt_hoy->bind_param("s", $fecha_hoy);
$stmt_hoy->execute();
$result_hoy = $stmt_hoy->get_result();
$citas_hoy = $result_hoy->fetch_all(MYSQLI_ASSOC);

// Próximas citas
$sql_proximas = "SELECT * FROM citas WHERE fecha > ? ORDER BY fecha ASC, hora ASC";
$stmt_proximas = $conn->prepare($sql_proximas);
$stmt_proximas->bind_param("s", $fecha_hoy);
$stmt_proximas->execute();
$result_proximas = $stmt_proximas->get_result();
$proximas_citas = $result_proximas->fetch_all(MYSQLI_ASSOC);

// Todas las citas
$sql_todas = "SELECT * FROM citas ORDER BY fecha DESC, hora DESC";
$result_todas = $conn->query($sql_todas);
$todas_citas = $result_todas->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Nutriologo</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/citas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Citas</h1>
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
                    <button class="tab-btn active" onclick="showTab('hoy', event)">Hoy</button>
                    <button class="tab-btn" onclick="showTab('proximas', event)">Próximas</button>
                    <button class="tab-btn" onclick="showTab('todas', event)">Todas</button>
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
                                                    <!-- MARCADOR: Hoy -->
                                                    <button class="btn-icon btn-marker" title="Marcar cita importante" onclick="marcarCita(<?php echo $cita['id']; ?>, this)">
                                                        <i class="fas fa-star"></i>
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
                                                <!-- MARCADOR: Próximas -->
                                                <button class="btn-icon btn-marker" title="Marcar cita importante" onclick="marcarCita(<?php echo $cita['id']; ?>, this)">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Todas (nueva pestaña) -->
                <div id="todas" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h3>Todas las Citas</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($todas_citas)): ?>
                                <p class="no-data">No hay citas registradas</p>
                            <?php else: ?>
                                <div class="appointments-list">
                                    <?php foreach ($todas_citas as $cita): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-date">
                                                <div class="date-day"><?php echo date('d', strtotime($cita['fecha'])); ?></div>
                                                <div class="date-month"><?php echo date('M', strtotime($cita['fecha'])); ?></div>
                                                <div class="appointment-time"><?php echo $cita['hora']; ?></div>
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
                                                    <button class="btn-icon btn-marker" title="Marcar cita importante" onclick="marcarCita(<?php echo $cita['id']; ?>, this)">
                                                        <i class="fas fa-star"></i>
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
            </div>
        </main>
    </div> <!-- .container -->

    <!-- Modal fuera del .container, centrado en toda la pantalla -->
    <div id="citaModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('citaModal')">&times;</span>
            <h3>Nueva Cita</h3>
            <form action="procesar_cita.php" method="POST">
                <label>Cliente:</label>
                <input type="text" name="cliente" required>
                <label>Fecha:</label>
                <input type="date" name="fecha" required>
                <label>Hora:</label>
                <input type="time" name="hora" required>
                <label>Tipo:</label>
                <input type="text" name="tipo" required>
                <label>Notas:</label>
                <textarea name="notas"></textarea>
                <button type="submit" class="btn-primary">Guardar</button>
            </form>
        </div>
    </div>
    <script src="../../js/citas.js"></script>
</body>
</html>
