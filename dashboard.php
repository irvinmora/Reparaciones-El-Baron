<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

require_once 'includes/db.php';

// Obtener estadísticas
$hoy = date('Y-m-d');
$reparaciones_hoy = $conn->query("SELECT COUNT(*) as total FROM reparaciones WHERE DATE(fecha_ingreso) = '$hoy'")->fetch_assoc();
$ventas_hoy = $conn->query("SELECT COUNT(*) as total FROM ventas WHERE DATE(fecha_venta) = '$hoy'")->fetch_assoc();
$productos_bajos = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo")->fetch_assoc();
$clientes_nuevos = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE DATE(fecha_registro) = '$hoy'")->fetch_assoc();
$pagos_pendientes = $conn->query("SELECT COUNT(*) as total FROM reparaciones WHERE saldo_pendiente > 0")->fetch_assoc();
$gastos_hoy = $conn->query("SELECT SUM(monto) as total FROM gastos WHERE DATE(fecha) = '$hoy'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - El Barón</title>
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
                <h1>Dashboard</h1>
                <div class="date"><?php echo date('d/m/Y'); ?></div>
            </header>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?php echo COLOR_SECONDARY; ?>">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Reparaciones Hoy</h3>
                        <p class="stat-number"><?php echo $reparaciones_hoy['total']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?php echo COLOR_ACCENT; ?>">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Ventas Hoy</h3>
                        <p class="stat-number"><?php echo $ventas_hoy['total']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e74c3c;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Stock Bajo</h3>
                        <p class="stat-number"><?php echo $productos_bajos['total']; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?php echo COLOR_PRIMARY; ?>">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pagos Pendientes</h3>
                        <p class="stat-number"><?php echo $pagos_pendientes['total']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--danger);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Gastos Hoy</h3>
                        <p class="stat-number" style="color: var(--danger);">$<?php echo number_format($gastos_hoy['total'] ?: 0, 2); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Acciones Rápidas</h2>
                <div class="actions-grid">
                    <a href="reparaciones.php?action=nueva" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nueva Reparación</span>
                    </a>
                    <a href="ventas.php?action=nueva" class="action-card">
                        <i class="fas fa-cash-register"></i>
                        <span>Registrar Venta</span>
                    </a>
                    <a href="productos.php?action=nuevo" class="action-card">
                        <i class="fas fa-box-open"></i>
                        <span>Agregar Producto</span>
                    </a>
                    <a href="clientes.php?action=nuevo" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <span>Nuevo Cliente</span>
                    </a>
                    <?php if (isAdmin()): ?>
                    <a href="usuarios.php?action=nuevo" class="action-card">
                        <i class="fas fa-user-shield"></i>
                        <span>Nuevo Usuario</span>
                    </a>
                    <?php endif; ?>
                    <a href="gastos.php" class="action-card">
                        <i class="fas fa-wallet"></i>
                        <span>Registrar Gasto</span>
                    </a>
                    <a href="reportes.php?tipo=ganancias&periodo=diario&fecha_inicio=<?php echo $hoy; ?>&fecha_fin=<?php echo $hoy; ?>" class="action-card" style="background: var(--accent); color: white;">
                        <i class="fas fa-chart-line" style="color: white;"></i>
                        <span>Ver Balance de Hoy</span>
                    </a>
                </div>
            </div>            
            <!-- Recent Repairs -->
            <div class="recent-section">
                <h2>Reparaciones Recientes</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recientes = $conn->query("
                            SELECT r.*, c.nombres, c.apellidos 
                            FROM reparaciones r 
                            JOIN clientes c ON r.cliente_id = c.id 
                            ORDER BY r.fecha_ingreso DESC 
                            LIMIT 5
                        ");
                        
                        while ($row = $recientes->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></td>
                            <td><?php echo $row['producto']; ?></td>
                            <td>
                                <span class="badge <?php echo $row['estado']; ?>">
                                    <?php echo $row['estado']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_ingreso'])); ?></td>
                            <td>
                                <a href="reparaciones.php?action=ver&id=<?php echo $row['id']; ?>" class="btn-small">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
</body>
</html>