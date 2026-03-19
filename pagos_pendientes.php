<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

require_once 'includes/db.php';

$mensaje = '';
$error = '';

// Obtener reparaciones con saldo pendiente
$sql = "SELECT r.*, c.nombres, c.apellidos, c.telefono 
        FROM reparaciones r 
        JOIN clientes c ON r.cliente_id = c.id 
        WHERE r.saldo_pendiente > 0 
        ORDER BY r.fecha_ingreso DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos Pendientes - El Barón</title>
    <link rel="icon" href="assets/img/logo.png" type="image/png">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="topbar">
                <button class="menu-toggle" id="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Pagos Pendientes</h1>
                <div class="date"><?php echo date('d/m/Y'); ?></div>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <div class="recent-section">
                <h2>Listado de Clientes con Saldo Pendiente</h2>
                <p style="margin-bottom: 20px; color: var(--text-light);">
                    Aquí se muestran todas las reparaciones que aún no han sido canceladas en su totalidad.
                </p>
                
                <div class="search-box" style="margin-bottom: 20px; display: flex; gap: 10px;">
                    <input type="text" id="buscar-pago" placeholder="Buscar por cliente, teléfono o producto..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" onkeyup="buscarPagos()">
                    <button onclick="buscarPagos()" class="btn btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Total</th>
                            <th>Abono</th>
                            <th>Saldo Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows == 0): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No hay pagos pendientes en este momento.</td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_ingreso'])); ?></td>
                                <td>
                                    <strong><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></strong><br>
                                    <small><?php echo $row['telefono']; ?></small>
                                </td>
                                <td><?php echo $row['producto']; ?></td>
                                <td>$<?php echo number_format($row['costo_total'], 2); ?></td>
                                <td>$<?php echo number_format($row['abono_inicial'], 2); ?></td>
                                <td style="color: var(--danger); font-weight: bold;">
                                    $<?php echo number_format($row['saldo_pendiente'], 2); ?>
                                </td>
                                <td>
                                    <a href="reparaciones.php?action=pago&id=<?php echo $row['id']; ?>&from=pagos" class="btn-small btn-success" title="Registrar Abono">
                                        <i class="fas fa-money-bill-wave"></i> Pagar
                                    </a>
                                    <a href="reparaciones.php?action=ver&id=<?php echo $row['id']; ?>&from=pagos" class="btn-small" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
    <script>
    function buscarPagos() {
        const termino = document.getElementById('buscar-pago').value.toLowerCase();
        const filas = document.querySelectorAll('.data-table tbody tr');
        
        filas.forEach(fila => {
            // Ignorar la fila de "No hay pagos" si existe
            if (fila.children.length === 1 && fila.textContent.includes('No hay pagos pendientes')) {
                return;
            }
            const texto = fila.textContent.toLowerCase();
            if (texto.includes(termino)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
    </script>
</body>
</html>
