<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

require_once 'includes/db.php';

$q = $_GET['q'] ?? '';

if (strlen($q) >= 1) {
    $termino = "$q%";
    $termino_any = "%$q%";
    $sql = "SELECT id, nombres, apellidos, telefono, direccion, email 
            FROM clientes 
            WHERE nombres LIKE ? OR apellidos LIKE ? OR nombres LIKE ? OR apellidos LIKE ? OR telefono LIKE ? 
            ORDER BY nombres ASC 
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $termino, $termino, $termino_any, $termino_any, $termino_any);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($clientes);
} else {
    echo json_encode([]);
}
?>