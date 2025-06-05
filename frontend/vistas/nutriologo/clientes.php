<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../../login.php');
    exit();
}

require_once '../../includes/conexion.php';

// Mostrar mensajes de éxito o error
$message = '';
$message_type = '';
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $message_type = 'success';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $message_type = 'error';
    unset($_SESSION['error']);
}

// Obtener lista de clientes
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
if (!empty($search)) {
    $search_escaped = $conn->real_escape_string($search);
    $where_clause = "WHERE nombre LIKE '%$search_escaped%' OR email LIKE '%$search_escaped%'";
}

$clientes = [];
$result = $conn->query("SELECT * FROM clientes $where_clause ORDER BY nombre ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - NutriManager</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/clientes.css">
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
                    <a href="clientes.php" class="nav-link active">Clientes</a>
                    <a href="citas.php" class="nav-link">Citas</a>
                    <a href="planes.php" class="nav-link">Planes</a>
                    <a href="../../logout.php" class="nav-link">Cerrar Sesión</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Mostrar mensajes -->
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="page-header">
                <h2>Gestión de Clientes</h2>
                <button class="btn-primary" onclick="openModal('clientModal')">
                    <i class="fas fa-plus"></i>
                    Nuevo Cliente
                </button>
            </div>

            <!-- Search Card -->
            <div class="card search-card">
                <div class="card-header">
                    <h3>Buscar Clientes</h3>
                </div>
                <div class="card-content">
                    <form method="GET" class="search-form">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Buscar por nombre o email..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn-search">Buscar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Clients List -->
            <div class="clients-grid">
                <?php if (empty($clientes)): ?>
                    <div class="no-data">
                        <i class="fas fa-users"></i>
                        <p>No se encontraron clientes</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <div class="client-card">
                            <div class="client-info">
                                <div class="client-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="client-details">
                                    <h3><?php echo htmlspecialchars($cliente['nombre']); ?></h3>
                                    <p class="client-email"><?php echo htmlspecialchars($cliente['email']); ?></p>
                                    <p class="client-phone"><?php echo htmlspecialchars($cliente['telefono'] ?? 'No especificado'); ?></p>
                                    <div class="client-meta">
                                        <span>Edad: <?php echo $cliente['edad'] ?? 'N/A'; ?> años</span>
                                        <span>Objetivo: <?php echo htmlspecialchars($cliente['objetivo'] ?? 'No especificado'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="client-actions">
                                <span class="status-badge <?php echo $cliente['activo'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $cliente['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                                <div class="action-buttons">
                                    <a href="cliente_perfil.php?id=<?php echo $cliente['id']; ?>" class="btn-icon" title="Ver perfil">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn-icon" title="Editar" onclick="editClient(<?php echo $cliente['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon btn-danger" title="Eliminar" onclick="deleteClient(<?php echo $cliente['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal para nuevo cliente -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nuevo Cliente</h3>
                <button class="modal-close" onclick="closeModal('clientModal')">&times;</button>
            </div>
            <form id="clientForm" action="procesar_cliente.php" method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre completo *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" min="1" max="120">
                    </div>
                    <div class="form-group">
                        <label for="altura">Altura (cm)</label>
                        <input type="number" id="altura" name="altura" min="100" max="250">
                    </div>
                    <div class="form-group">
                        <label for="peso">Peso (kg)</label>
                        <input type="number" id="peso" name="peso" min="30" max="300" step="0.1">
                    </div>
                </div>
                <div class="form-group">
                    <label for="objetivo">Objetivo</label>
                    <select id="objetivo" name="objetivo">
                        <option value="">Seleccionar objetivo</option>
                        <option value="Pérdida de peso">Pérdida de peso</option>
                        <option value="Ganancia muscular">Ganancia muscular</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Control médico">Control médico</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('clientModal')">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../js/clientes.js"></script>
</body>
</html>
