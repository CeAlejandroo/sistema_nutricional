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
    $where_conditions[] = "pa.nombre LIKE '%$search_escaped%'";
}
if ($filter_type !== 'all') {
    $filter_escaped = $conn->real_escape_string($filter_type);
    $where_conditions[] = "pa.objetivo = '$filter_escaped'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Obtener planes
$planes = [];
$result = $conn->query("
    SELECT p.*, pa.nombre as cliente_nombre 
    FROM planes_nutricionales p 
    JOIN pacientes pa ON p.paciente_id = pa.id 
    $where_clause 
    ORDER BY p.fecha_generacion DESC
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $planes[] = $row;
    }
}

// Obtener lista de pacientes
$pacientes = [];
$res = $conn->query("SELECT id, nombre FROM pacientes ORDER BY nombre ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $pacientes[] = $row;
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
                <h1>Planes</h1>
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
                    <button class="btn-secondary" id="btnGenerarExperto" onclick="abrirGenerarExperto()">
                        <i class="fas fa-magic"></i>
                        Generar con el sistema experto
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
                            <input type="text" name="search" placeholder="Buscar planes..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <select name="filter_type" class="filter-select">
                            <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>Todos los tipos
                            </option>
                            <option value="perder_peso" <?php echo $filter_type === 'perder_peso' ? 'selected' : ''; ?>>Pérdida de peso</option>
                            <option value="ganar_musculo" <?php echo $filter_type === 'ganar_musculo' ? 'selected' : ''; ?>>Ganancia muscular</option>
                            <option value="ganar_peso" <?php echo $filter_type === 'ganar_peso' ? 'selected' : ''; ?>>Ganar peso</option>
                            <option value="mantener_peso" <?php echo $filter_type === 'mantener_peso' ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                        <button type="submit" class="btn-search">Buscar</button>
                    </form>
                </div>
            </div>

            <!-- Plans List -->
            <div class="plans-grid">
                <?php if (empty($planes)): ?>
                    <div class="no-data">
                        <i class="fas fa-file-alt"></i>
                        <p>No se encontraron planes nutricionales</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($planes as $plan): ?>
                        <div class="plan-card">
                            <div class="plan-header">
                                <h3>Plan #<?php echo $plan['id']; ?></h3>
                                <span class="status-badge <?php echo $plan['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $plan['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </div>
                            <div class="plan-content">
                                <div class="plan-info">
                                    <div class="info-row">
                                        <div class="info-item">
                                            <strong>Paciente:</strong> <?php echo htmlspecialchars($plan['cliente_nombre']); ?>
                                        </div>
                                        <div class="info-item">
                                            <strong>Calorías:</strong> <?php echo $plan['calorias_totales']; ?> kcal/día
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <strong>Proteínas:</strong> <?php echo $plan['proteinas_totales']; ?>%
                                        </div>
                                        <div class="info-item">
                                            <strong>Grasas:</strong> <?php echo $plan['grasas_totales']; ?>%
                                        </div>
                                        <div class="info-item">
                                            <strong>Carbohidratos:</strong> <?php echo $plan['carbohidratos_totales']; ?>%
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <strong>Creado:</strong> <?php echo $plan['fecha_generacion']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="plan-actions">
                                <button class="btn-icon" title="Editar"
                                    onclick="editarPlan(this)"
                                    data-id="<?php echo $plan['id']; ?>"
                                    data-paciente_id="<?php echo $plan['paciente_id']; ?>"
                                    data-calorias="<?php echo $plan['calorias_totales']; ?>"
                                    data-proteinas="<?php echo $plan['proteinas_totales']; ?>"
                                    data-grasas="<?php echo $plan['grasas_totales']; ?>"
                                    data-carbos="<?php echo $plan['carbohidratos_totales']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-danger" title="Eliminar"
                                    onclick="eliminarPlan(<?php echo $plan['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn-icon" title="Ver plan" onclick="verPlan(<?php echo $plan['id']; ?>)">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zm-8 4a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-1.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/>
  </svg>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
        </main>
    </div>
    <!-- Modal Nuevo Plan Personalizado -->
    <div id="planModal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close" onclick="closeModal('planModal')">&times;</span>
        <h2 style="margin-bottom: 1.5rem;">Nuevo Plan Nutricional Personalizado</h2>
        <form id="nuevoPlanForm" class="form-plan" method="POST" action="procesar_plan.php" autocomplete="off">
          <div class="form-group">
            <label for="paciente_id">Paciente *</label>
            <select name="paciente_id" id="paciente_id" required>
              <option value="">Selecciona un paciente</option>
              <?php foreach ($pacientes as $p): ?>
                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="calorias_diarias">Calorías totales *</label>
              <input type="number" name="calorias_diarias" id="calorias_diarias" required>
            </div>
            <div class="form-group">
              <label for="porcentaje_proteina">Proteínas (%) *</label>
              <input type="number" name="porcentaje_proteina" id="porcentaje_proteina" min="0" max="100" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="porcentaje_grasa">Grasas (%) *</label>
              <input type="number" name="porcentaje_grasa" id="porcentaje_grasa" min="0" max="100" required>
            </div>
            <div class="form-group">
              <label for="porcentaje_carbos">Carbohidratos (%) *</label>
              <input type="number" name="porcentaje_carbos" id="porcentaje_carbos" min="0" max="100" required>
            </div>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('planModal')">Cancelar</button>
            <button type="submit" class="btn-primary">Guardar Plan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para seleccionar paciente y generar plan experto -->
    <div id="expertoModal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close" onclick="closeModal('expertoModal')">&times;</span>
        <h2>Selecciona un paciente</h2>
        <form id="formExperto" onsubmit="return generarPlanExperto(event)">
          <div class="form-group">
            <label for="paciente_experto">Paciente *</label>
            <select id="paciente_experto" required>
              <option value="">Selecciona un paciente</option>
              <?php foreach ($pacientes as $p): ?>
                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- AQUÍ va el resumen, dentro del modal-content -->
          <div id="resumen_experto" style="display:none; margin:1em 0; background:#f8fafc; border-radius:8px; padding:1em; color:#222;">
            <!-- Resumen generado por JS -->
          </div>
          <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('expertoModal')">Cancelar</button>
            <button type="submit" class="btn-primary">Generar Plan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para ver plan -->
    <div id="verPlanModal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close" onclick="closeModal('verPlanModal')">&times;</span>
        <div id="contenido_ver_plan"></div>
      </div>
    </div>

    <script src="../../js/planes.js"></script>
    <script>
      const alimentosPorId = {
        <?php
          $resAlim = $conn->query("SELECT id, nombre FROM alimentos");
          while ($row = $resAlim->fetch_assoc()) {
            echo $row['id'] . ': "' . addslashes($row['nombre']) . '",';
          }
        ?>
      };
    </script>
</body>

</html>