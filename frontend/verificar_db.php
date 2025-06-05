<?php
// Script para verificar la conexión a la base de datos y las tablas
// Coloca este archivo en la raíz del proyecto y accede a él desde el navegador

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_nutricional');
define('DB_USER', 'root');
define('DB_PASS', '');

echo "<h1>Verificación de Base de Datos</h1>";

// 1. Verificar conexión
echo "<h2>1. Verificando conexión a MySQL...</h2>";
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        die("<p style='color:red'>Error de conexión: " . $conn->connect_error . "</p>");
    }
    echo "<p style='color:green'>✓ Conexión exitosa a MySQL</p>";
} catch (Exception $e) {
    die("<p style='color:red'>Error al conectar con MySQL: " . $e->getMessage() . "</p>");
}

// 2. Verificar si existe la base de datos
echo "<h2>2. Verificando base de datos '{$DB_NAME}'...</h2>";
$result = $conn->query("SHOW DATABASES LIKE '{$DB_NAME}'");
if ($result->num_rows > 0) {
    echo "<p style='color:green'>✓ Base de datos '{$DB_NAME}' existe</p>";
    
    // Seleccionar la base de datos
    $conn->select_db(DB_NAME);
    
    // 3. Verificar tablas
    echo "<h2>3. Verificando tablas...</h2>";
    $tablas_requeridas = ['usuarios', 'clientes', 'citas', 'planes_nutricionales', 'seguimiento_progreso', 'historial_clinico'];
    
    foreach ($tablas_requeridas as $tabla) {
        $result = $conn->query("SHOW TABLES LIKE '{$tabla}'");
        if ($result->num_rows > 0) {
            echo "<p style='color:green'>✓ Tabla '{$tabla}' existe</p>";
            
            // Mostrar estructura de la tabla
            echo "<details>";
            echo "<summary>Ver estructura de '{$tabla}'</summary>";
            echo "<pre>";
            $estructura = $conn->query("DESCRIBE {$tabla}");
            if ($estructura) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
                while ($fila = $estructura->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$fila['Field']}</td>";
                    echo "<td>{$fila['Type']}</td>";
                    echo "<td>{$fila['Null']}</td>";
                    echo "<td>{$fila['Key']}</td>";
                    echo "<td>{$fila['Default']}</td>";
                    echo "<td>{$fila['Extra']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Error al obtener estructura: " . $conn->error;
            }
            echo "</pre>";
            echo "</details>";
            
        } else {
            echo "<p style='color:red'>✗ Tabla '{$tabla}' no existe</p>";
        }
    }
    
} else {
    echo "<p style='color:red'>✗ Base de datos '{$DB_NAME}' no existe. Debes crearla e importar el esquema.</p>";
    echo "<p>Puedes crear la base de datos con el siguiente comando SQL:</p>";
    echo "<pre>CREATE DATABASE {$DB_NAME};</pre>";
    echo "<p>Luego importa el archivo schema.sql desde phpMyAdmin o usando el comando:</p>";
    echo "<pre>mysql -u " . DB_USER . " -p " . DB_NAME . " < sql/schema.sql</pre>";
}

// Cerrar conexión
$conn->close();
?>
