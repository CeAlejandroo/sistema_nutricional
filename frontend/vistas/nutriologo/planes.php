<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

// Obtener filtros
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : 'all';

// Construir consulta
$where_conditions = [];
if (!empty($search)) {
    $search_escaped = $conn->real_escape_string($search);
    $where_conditions[] = "(p.nombre LIKE '%$search_escaped%' OR c.nombre LIKE '%$search_escaped%')";
}
if ($filter_type !== 'all') {
    $filter_escaped = $conn->real_escape_string($filter_type);
    $where_conditions[] = "p.tipo = '$filter_escaped'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Obtener planes
$planes = [];
$result = $conn->query("
    SELECT p.*, c.nombre as cliente_nombre 
    FROM planes_nutricionales p 
    JOIN clientes c ON p.cliente_id = c.id 
    $where_clause 
    ORDER BY p.fecha_creacion DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $planes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes Nutricionales - NutriManager</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/planes.css">
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
                    <a href="citas.php" class="nav-link">Citas</a>
                    <a href="planes.php" class="nav-link active">Planes</a>
                    <a href="../../logout.php" class="nav-link">Cerrar Sesión</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h2>Planes Nutricionales</h2>
                <div class="header-actions">
                    <button class="btn-secondary" onclick="openModal('generarPlanModal')">
                        <i class="fas fa-magic"></i>
                        Generar con IA
                    </button>
                    <button class="btn-primary" onclick="openModal('planModal')">
                        <i class="fas fa-plus"></i>
                        Nuevo Plan
                    </button>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card search-card">
                <div class="card-header">
                    <h3>Filtros y Búsqueda</h3>
                </div>
                <div class="card-content">
                    <form method="GET" class="search-form">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Buscar planes o clientes..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <select name="filter_type" class="filter-select">
                            <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>Todos los tipos</option>
                            <option value="Pérdida de peso" <?php echo $filter_type === 'Pérdida de peso' ? 'selected' : ''; ?>>Pérdida de peso</option>
                            <option value="Ganancia muscular" <?php echo $filter_type === 'Ganancia muscular' ? 'selected' : ''; ?>>Ganancia muscular</option>
                            <option value="Control médico" <?php echo $filter_type === 'Control médico' ? 'selected' : ''; ?>>Control médico</option>
                            <option value="Mantenimiento" <?php echo $filter_type === 'Mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                        <button type="submit" class="btn-search">Buscar</button>
                    </form>
                </div>
            </div>

            <!-- Plans List -->
            <div class="plans-grid">
                <?php if (empty($planes)): ?>
                    <div class="no-data">
                        <i class="fas fa-file-text"></i>
                        <p>No se encontraron planes nutricionales</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($planes as $plan): ?>
                        <div class="plan-card">
                            <div class="plan-header">
                                <h3><?php echo htmlspecialchars($plan['nombre']); ?></h3>
                                <span class="status-badge <?php echo $plan['estado'] === 'activo' ? 'active' : 'inactive'; ?>">
                                    <?php echo ucfirst($plan['estado']); ?>
                                </span>
                            </div>
                            <div class="plan-content">
                                <div class="plan-info">
                                    <div class="info-row">
                                        <div class="info-item">
                                            <strong>Cliente:</strong> <?php echo htmlspecialchars($plan['cliente_nombre']); ?>
                                        </div>
                                        <div class="info-item">
                                            <strong>Calorías:</strong> <?php echo $plan['calorias_diarias']; ?> kcal/día
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <strong>Tipo:</strong> <?php echo htmlspecialchars($plan['tipo']); ?>
                                        </div>
                                        <div class="info-item">
                                            <strong>Duración:</strong> <?php echo $plan['duracion_meses']; ?> meses
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <strong>Creado:</strong> <?php echo $plan['fecha_creacion']; ?>
                                        </div>
                                        <?php if ($plan['fecha_inicio']): ?>
                                            <div class="info-item">
                                                <strong>Inicio:</strong> <?php echo $plan['fecha_inicio']; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($plan['descripcion']): ?>
                                        <div class="plan-description">
                                            <strong>Descripción:</strong> <?php echo htmlspecialchars($plan['descripcion']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="plan-actions">
                                <button class="btn-icon" title="Ver detalles" onclick="verPlan(<?php echo $plan['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" title="Editar" onclick="editarPlan(<?php echo $plan['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-danger" title="Eliminar" onclick="eliminarPlan(<?php echo $plan['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- AI Generator Card -->
            <div class="card ai-generator-card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-magic"></i>
                        Generador Automático de Planes
                    </h3>
                </div>
                <div class="card-content">
                    <p class="ai-description">
                        Utiliza nuestro motor de inferencia para generar planes nutricionales personalizados basados en:
                    </p>
                    <ul class="ai-features">
                        <li>Objetivos del cliente (pérdida de peso, ganancia muscular, etc.)</li>
                        <li>Restricciones alimentarias y alergias</li>
                        <li>Nivel de actividad física</li>
                        <li>Preferencias alimentarias</li>
                        <li>Historial médico relevante</li>
                    </ul>
                    <button class="btn-ai" onclick="openModal('generarPlanModal')">
                        <i class="fas fa-magic"></i>
                        Generar Plan Automático
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/planes.js"></script>
</body>
</html>
