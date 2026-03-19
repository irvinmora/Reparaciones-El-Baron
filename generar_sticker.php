<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    die('Acceso denegado');
}

require_once 'includes/db.php';

$tipo = $_GET['tipo'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$tipo || !$id) {
    die('Parámetros inválidos');
}

if ($tipo == 'reparacion') {
    // Obtener datos de la reparación
    $sql = "SELECT r.*, c.nombres, c.apellidos, c.telefono, c.direccion 
            FROM reparaciones r 
            JOIN clientes c ON r.cliente_id = c.id 
            WHERE r.id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data = [
            'id' => $row['id'],
            'nombres' => $row['nombres'],
            'apellidos' => $row['apellidos'],
            'telefono' => $row['telefono'],
            'direccion' => $row['direccion'],
            'producto' => $row['producto'],
            'descripcion' => $row['descripcion_problema'],
            'costo_total' => $row['costo_total'],
            'abono' => $row['abono_inicial'],
            'saldo' => $row['saldo_pendiente']
        ];
        
        echo generateSticker($data, 'reparacion');
    }
    
} elseif ($tipo == 'venta') {
    // Obtener datos de la venta
    $sql = "SELECT v.*, c.nombres, c.apellidos, c.telefono, c.direccion,
            COUNT(dv.id) as productos 
            FROM ventas v 
            JOIN clientes c ON v.cliente_id = c.id 
            LEFT JOIN detalle_venta dv ON v.id = dv.venta_id
            WHERE v.id = $id
            GROUP BY v.id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data = [
            'id' => $row['id'],
            'nombres' => $row['nombres'],
            'apellidos' => $row['apellidos'],
            'telefono' => $row['telefono'],
            'direccion' => $row['direccion'],
            'producto' => $row['productos'] . ' producto(s)',
            'cantidad' => 1,
            'total' => $row['total']
        ];
        
        echo generateSticker($data, 'venta');
    }
}
?>