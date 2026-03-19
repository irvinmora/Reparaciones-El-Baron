<?php
require_once 'includes/db.php';

$sql = "CREATE TABLE IF NOT EXISTS gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(255) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50) DEFAULT 'General',
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Tabla 'gastos' creada correctamente.";
} else {
    echo "Error al crear la tabla: " . $conn->error;
}
?>
