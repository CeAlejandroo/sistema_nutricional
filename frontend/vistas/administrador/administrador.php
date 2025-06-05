<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administrador</title>
  <link rel="stylesheet" href="../../css/admin.css">
  <!-- Font Awesome para íconos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="dashboard">
    <h2><i class="fas fa-user-circle"></i> Administrador</h2>
    <p>¿Qué haremos hoy?</p>

    <div class="admin-actions">
      <div class="action">
        <a href="gestionar_usuarios.php" class="circle">
          <i class="fas fa-users"></i>
          <span>Usuarios</span>
        </a>
      </div>
      <div class="action">
        <a href="reportes.php" class="circle">
          <i class="fas fa-file-alt"></i>
          <span>Reportes</span>
        </a>
      </div>
      <div class="action">
        <a href="sesiones.php" class="circle">
          <i class="fas fa-clock"></i>
          <span>Sesiones</span>
        </a>
      </div>
      <div class="action">
        <a href="configuraciones.php" class="circle">
          <i class="fas fa-cog"></i>
          <span>Configuración</span>
        </a>
      </div>
      <div class="action">
        <a href="/WEB/Programacion_logica_funcional/nutricion_experto/frontend/logout.php" class="circle">
          <i class="fas fa-sign-out-alt"></i>
          <span>Salir</span>
        </a>
      </div>
    </div>
  </div>
</body>
</html>