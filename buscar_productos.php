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
    $sql = "SELECT id, codigo, nombre, precio, stock 
            FROM productos 
            WHERE nombre LIKE ? OR codigo LIKE ? OR nombre LIKE ? OR codigo LIKE ? 
            ORDER BY nombre ASC 
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $termino, $termino, $termino_any, $termino_any);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($productos);
} else {
    echo json_encode([]);
}
?>