<?php
require_once 'includes/db.php';

echo "<h2>DEBUG: Gastos Table</h2>";
$res = $conn->query("SELECT *, DATE(fecha) as solo_fecha FROM gastos");
echo "<table border='1'><tr><th>ID</th><th>Desc</th><th>Monto</th><th>Fecha</th><th>Solo Fecha</th><th>CURDATE()</th></tr>";
$curdate = $conn->query("SELECT CURDATE() as c")->fetch_assoc()['c'];
while($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['descripcion']}</td><td>{$row['monto']}</td><td>{$row['fecha']}</td><td>{$row['solo_fecha']}</td><td>$curdate</td></tr>";
}
echo "</table>";

echo "<h2>DEBUG: Reparaciones con Saldo</h2>";
$res = $conn->query("SELECT r.*, c.nombres FROM reparaciones r JOIN clientes c ON r.cliente_id = c.id WHERE saldo_pendiente > 0");
echo "<table border='1'><tr><th>ID</th><th>Cliente</th><th>Saldo</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['nombres']}</td><td>{$row['saldo_pendiente']}</td></tr>";
}
echo "</table>";
?>
