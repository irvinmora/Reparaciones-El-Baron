<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/logo.png" alt="Logo" class="sidebar-logo" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%232c3e50%22/><text x=%2220%22 y=%2265%22 fill=%22%23e67e22%22 font-size=%2250%22 font-weight=%22bold%22>EB</text></svg>'">
        <h2>El Barón</h2>
        <p>Bienvenido, <?php echo $_SESSION['user_name']; ?></p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="reparaciones.php" class="<?php echo ($current_page == 'reparaciones.php') ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i> Reparaciones
        </a>
        <a href="pagos_pendientes.php" class="<?php echo $current_page == 'pagos_pendientes.php' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-usd"></i> Pagos Pendientes
        </a>
        <a href="productos.php" class="<?php echo $current_page == 'productos.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Productos/Repuestos
        </a>
        <a href="ventas.php" class="<?php echo $current_page == 'ventas.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Ventas
        </a>
        <a href="clientes.php" class="<?php echo $current_page == 'clientes.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a href="gastos.php" class="<?php echo $current_page == 'gastos.php' ? 'active' : ''; ?>">
            <i class="fas fa-wallet"></i> Gastos del Local
        </a>
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <a href="usuarios.php" class="<?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>">
            <i class="fas fa-users-cog"></i> Usuarios
        </a>
        <a href="configempresa.php" class="<?php echo $current_page == 'configempresa.php' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i> Configuración Empresa
        </a>
        <?php endif; ?>
        <a href="reportes.php?tipo=ganancias" class="<?php echo $current_page == 'reportes.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>
        <a href="logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
    </nav>
</div>
