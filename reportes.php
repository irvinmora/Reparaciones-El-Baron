<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

require_once 'includes/db.php';

$tipo_reporte = $_GET['tipo'] ?? 'ganancias';
$periodo = $_GET['periodo'] ?? 'diario';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

function obtenerReporte($tipo, $periodo, $fecha_inicio, $fecha_fin) {
    global $conn;
    
    switch ($periodo) {
        case 'diario':
            $where = "DATE(fecha) = CURDATE()";
            break;
        case 'semanal':
            $where = "YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'quincenal':
            $where = "fecha >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)";
            break;
        case 'mensual':
            $where = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
            break;
        case 'anual':
            $where = "YEAR(fecha) = YEAR(CURDATE())";
            break;
        case 'personalizado':
            $where = "DATE(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
            break;
        default:
            $where = "DATE(fecha) = CURDATE()";
    }
    
    $reportes = [];
    
    switch ($tipo) {
        case 'ventas':
            $sql = "SELECT 
                        DATE(v.fecha_venta) as fecha,
                        COUNT(*) as cantidad,
                        SUM(v.total) as total
                    FROM ventas v
                    WHERE " . str_replace('fecha', 'v.fecha_venta', $where) . "
                    GROUP BY DATE(v.fecha_venta)
                    ORDER BY fecha DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $reportes[] = $row;
            }
            break;
            
        case 'reparaciones':
            $sql = "SELECT 
                        DATE(r.fecha_ingreso) as fecha,
                        COUNT(*) as cantidad,
                        SUM(r.costo_total) as total,
                        SUM(r.abono_inicial) as abonos,
                        SUM(r.saldo_pendiente) as pendiente
                    FROM reparaciones r
                    WHERE " . str_replace('fecha', 'r.fecha_ingreso', $where) . "
                    GROUP BY DATE(r.fecha_ingreso)
                    ORDER BY fecha DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $reportes[] = $row;
            }
            break;
            
        case 'productos':
            $sql = "SELECT 
                        p.nombre,
                        p.codigo,
                        p.stock,
                        p.stock_minimo,
                        COUNT(dv.id) as veces_vendido,
                        SUM(dv.cantidad) as total_vendido
                    FROM productos p
                    LEFT JOIN detalle_venta dv ON p.id = dv.producto_id
                    LEFT JOIN ventas v ON dv.venta_id = v.id AND " . str_replace('fecha', 'v.fecha_venta', $where) . "
                    GROUP BY p.id
                    ORDER BY veces_vendido DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $reportes[] = $row;
            }
            break;
            
        case 'gastos':
            $sql = "SELECT 
                        DATE(g.fecha) as fecha,
                        COUNT(*) as cantidad,
                        SUM(g.monto) as total
                    FROM gastos g
                    WHERE " . str_replace('fecha', 'g.fecha', $where) . "
                    GROUP BY DATE(g.fecha)
                    ORDER BY fecha DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $reportes[] = $row;
            }
            break;
            
        case 'ganancias':
            $sql = "SELECT 
                        DATE(fecha) as fecha,
                        SUM(ventas) as ventas,
                        SUM(reparaciones) as reparaciones,
                        SUM(gastos) as gastos,
                        (SUM(ventas) + SUM(reparaciones)) - SUM(gastos) as total
                    FROM (
                        SELECT 
                            v.fecha_venta as fecha,
                            v.total as ventas,
                            0 as reparaciones,
                            0 as gastos
                        FROM ventas v
                        WHERE " . str_replace('fecha', 'v.fecha_venta', $where) . "
                        UNION ALL
                        SELECT 
                            r.fecha_ingreso as fecha,
                            0 as ventas,
                            r.costo_total as reparaciones,
                            0 as gastos
                        FROM reparaciones r
                        WHERE " . str_replace('fecha', 'r.fecha_ingreso', $where) . "
                        UNION ALL
                        SELECT 
                            g.fecha as fecha,
                            0 as ventas,
                            0 as reparaciones,
                            g.monto as gastos
                        FROM gastos g
                        WHERE " . str_replace('fecha', 'g.fecha', $where) . "
                    ) AS combined
                    GROUP BY DATE(fecha)
                    ORDER BY fecha DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $reportes[] = $row;
            }
            break;
    }
    
    return $reportes;
}

$datos_reporte = obtenerReporte($tipo_reporte, $periodo, $fecha_inicio, $fecha_fin);

// Calcular totales
$total_ventas = 0;
$total_reparaciones = 0;
$total_gastos = 0;
$total_ganancias = 0;

foreach ($datos_reporte as $row) {
    if ($tipo_reporte == 'ganancias') {
        $total_ventas += $row['ventas'];
        $total_reparaciones += $row['reparaciones'];
        $total_gastos += $row['gastos'];
        $total_ganancias += $row['total'];
    } elseif ($tipo_reporte == 'ventas' || $tipo_reporte == 'reparaciones' || $tipo_reporte == 'gastos') {
        $total_ganancias += $row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - El Barón</title>
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
                <h1>Reportes y Estadísticas</h1>
            </header>
            
            <!-- Filtros -->
            <div class="form-container" style="margin-bottom: 20px;">
                <form method="GET" action="" class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo de Reporte</label>
                        <select id="tipo" name="tipo" onchange="this.form.submit()">
                            <option value="ventas" <?php echo $tipo_reporte == 'ventas' ? 'selected' : ''; ?>>Ventas</option>
                            <option value="reparaciones" <?php echo $tipo_reporte == 'reparaciones' ? 'selected' : ''; ?>>Reparaciones</option>
                            <option value="productos" <?php echo $tipo_reporte == 'productos' ? 'selected' : ''; ?>>Productos/Repuestos</option>
                            <option value="gastos" <?php echo $tipo_reporte == 'gastos' ? 'selected' : ''; ?>>Gastos del Local</option>
                            <option value="ganancias" <?php echo $tipo_reporte == 'ganancias' ? 'selected' : ''; ?>>Resumen (Balance)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="periodo">Período</label>
                        <select id="periodo" name="periodo" onchange="this.form.submit()">
                            <option value="diario" <?php echo $periodo == 'diario' ? 'selected' : ''; ?>>Diario</option>
                            <option value="semanal" <?php echo $periodo == 'semanal' ? 'selected' : ''; ?>>Semanal</option>
                            <option value="quincenal" <?php echo $periodo == 'quincenal' ? 'selected' : ''; ?>>Quincenal</option>
                            <option value="mensual" <?php echo $periodo == 'mensual' ? 'selected' : ''; ?>>Mensual</option>
                            <option value="anual" <?php echo $periodo == 'anual' ? 'selected' : ''; ?>>Anual</option>
                            <option value="personalizado" <?php echo $periodo == 'personalizado' ? 'selected' : ''; ?>>Personalizado</option>
                        </select>
                    </div>
                    
                    <div id="periodo_reporte" style="display: <?php echo $periodo == 'personalizado' ? 'block' : 'none'; ?>; grid-column: span 2;">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fecha_inicio">Fecha Inicio</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                            </div>
                            <div class="form-group">
                                <label for="fecha_fin">Fecha Fin</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="align-self: flex-end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <button type="button" onclick="mostrarVistaPrevia()" class="btn btn-success">
                            <i class="fas fa-file-pdf"></i> Ver Vista Previa / Descargar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Resumen -->
            <?php if ($tipo_reporte == 'ganancias'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?php echo COLOR_SECONDARY; ?>">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Ventas</h3>
                        <p class="stat-number">$<?php echo number_format($total_ventas, 2); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?php echo COLOR_ACCENT; ?>">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Ingreso Reparaciones</h3>
                        <p class="stat-number">$<?php echo number_format($total_reparaciones, 2); ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--danger);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Gastos</h3>
                        <p class="stat-number" style="color: var(--danger);">-$<?php echo number_format($total_gastos, 2); ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--primary);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Balance Neto</h3>
                        <p class="stat-number">$<?php echo number_format($total_ganancias, 2); ?></p>
                    </div>
                </div>
            </div>
<?php elseif ($tipo_reporte == 'gastos'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: var(--danger);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Gastos</h3>
                        <p class="stat-number">$<?php echo number_format($total_ganancias, 2); ?></p>
                    </div>
                </div>
            </div>
<?php endif; ?>
            
            <!-- Tabla de Reporte -->
            <table class="data-table" id="tabla-reporte">
                <thead>
                    <tr>
                        <?php if ($tipo_reporte == 'ventas'): ?>
                            <th>Fecha</th>
                            <th>Cantidad de Ventas</th>
                            <th>Total</th>
                        <?php elseif ($tipo_reporte == 'reparaciones'): ?>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Abonos</th>
                            <th>Pendiente</th>
                        <?php elseif ($tipo_reporte == 'productos'): ?>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Vendido</th>
                            <th>Stock</th>
                        <?php elseif ($tipo_reporte == 'gastos'): ?>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>Total Gasto</th>
                        <?php elseif ($tipo_reporte == 'ganancias'): ?>
                            <th>Fecha</th>
                            <th>Ventas</th>
                            <th>Reparaciones</th>
                            <th>Gastos</th>
                            <th>Ganancia-Neta</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos_reporte)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">No hay datos para mostrar</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos_reporte as $row): ?>
                            <tr>
                                <?php if ($tipo_reporte == 'ventas'): ?>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                    <td><?php echo $row['cantidad']; ?></td>
                                    <td>$<?php echo number_format($row['total'], 2); ?></td>
                                <?php elseif ($tipo_reporte == 'reparaciones'): ?>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                    <td><?php echo $row['cantidad']; ?></td>
                                    <td>$<?php echo number_format($row['total'], 2); ?></td>
                                    <td>$<?php echo number_format($row['abonos'], 2); ?></td>
                                    <td>$<?php echo number_format($row['pendiente'], 2); ?></td>
                                <?php elseif ($tipo_reporte == 'productos'): ?>
                                    <td><?php echo $row['codigo']; ?></td>
                                    <td><?php echo $row['nombre']; ?></td>
                                    <td><?php echo $row['total_vendido']; ?></td>
                                    <td><?php echo $row['stock']; ?></td>
                                <?php elseif ($tipo_reporte == 'gastos'): ?>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                    <td><?php echo $row['cantidad']; ?></td>
                                    <td style="color: var(--danger); font-weight: bold;">$<?php echo number_format($row['total'], 2); ?></td>
                                <?php elseif ($tipo_reporte == 'ganancias'): ?>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                    <td>$<?php echo number_format($row['ventas'], 2); ?></td>
                                    <td>$<?php echo number_format($row['reparaciones'], 2); ?></td>
                                    <td style="color: var(--danger);">-$<?php echo number_format($row['gastos'], 2); ?></td>
                                    <td style="font-weight: bold; background: #f8f9fa;">$<?php echo number_format($row['total'], 2); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
    function mostrarVistaPrevia() {
        const element = document.getElementById('tabla-reporte');
        if (!element) return;
        
        const previewContent = document.getElementById('preview-content');
        const tipoReporte = '<?php echo ucfirst($tipo_reporte); ?>';
        const periodo = '<?php echo ucfirst($periodo); ?>';
        const titulo = 'Reporte de ' + tipoReporte + ' - ' + periodo;
        
        previewContent.innerHTML = `
            <div style="background: white; padding: 40px; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); width: 100%; max-width: 1000px; margin: 0 auto; min-height: 400px; font-family: Arial, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #d35400; padding-bottom: 15px;">
                    <h1 style="color: #1a252f; margin: 0; font-size: 28px;">EL BARÓN REPARACIONES</h1>
                    <p style="margin: 5px 0; color: #555;">Digitalizando la confianza - Tel: (000) 000-0000</p>
                    <h2 style="color: #d35400; text-transform: uppercase; margin-top: 15px;">${titulo}</h2>
                    <p style="color: #888; font-size: 13px;">Fecha de emisión: ${new Date().toLocaleString()}</p>
                </div>
                <div style="margin-top: 20px;">
                    ${element.outerHTML}
                </div>
                <div style="margin-top: 40px; text-align: center; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 20px;">
                    <p>Este documento es una vista previa del reporte generado por el Sistema El Barón.</p>
                </div>
            </div>
        `;
        
        document.getElementById('modal-preview').style.display = 'flex';
    }

    function cerrarVistaPrevia() {
        document.getElementById('modal-preview').style.display = 'none';
    }

    function confirmarDescarga() {
        const element = document.getElementById('tabla-reporte');
        const tipoReporte = '<?php echo ucfirst($tipo_reporte); ?>';
        const periodo = '<?php echo ucfirst($periodo); ?>';
        const titulo = 'Reporte de ' + tipoReporte + ' - ' + periodo;
        
        const container = document.createElement('div');
        container.style.padding = '20px';
        container.innerHTML = `
            <div style="text-align: center; margin-bottom: 30px; border-bottom: 4px solid #d35400; padding-bottom: 15px; font-family: Arial, sans-serif;">
                <h1 style="color: #1a252f; margin: 0; font-size: 36px; letter-spacing: 1px;">EL BARÓN REPARACIONES</h1>
                <p style="margin: 8px 0; font-size: 16px; color: #333;">Digitalizando la confianza - Servicio Técnico Especializado</p>
                <div style="background: #f8f9fa; padding: 15px; margin-top: 15px; border-radius: 8px;">
                    <h2 style="color: #d35400; text-transform: uppercase; font-size: 24px; margin: 0;">${titulo}</h2>
                    <p style="font-size: 13px; color: #666; margin-top: 5px;">Fecha de emisión: ${new Date().toLocaleString()}</p>
                </div>
            </div>
            <style>
                table { width: 100%; border-collapse: collapse; margin-top: 20px; font-family: Arial, sans-serif; }
                th { background-color: #1a252f; color: white; padding: 12px 10px; text-align: left; font-size: 12px; text-transform: uppercase; }
                td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 11px; color: #333; }
                tr:nth-child(even) { background-color: #fcfcfc; }
                .text-danger { color: #e74c3c !important; font-weight: bold; }
                .ganancia-neta { font-weight: bold; background-color: #f1f1f1 !important; }
            </style>
            ${element.outerHTML}
            <div style="margin-top: 60px; text-align: center; font-size: 11px; color: #7f8c8d; border-top: 1px solid #ddd; padding-top: 20px; font-family: Arial, sans-serif;">
                <p>Este documento es un reporte oficial de actividades del Sistema de Gestión El Barón.</p>
                <p>&copy; <?php echo date('Y'); ?> El Barón - Tecnología y Confianza</p>
            </div>
        `;
        
        const opt = {
            margin: [0.5, 0.5],
            filename: 'Reporte_' + tipoReporte + '_<?php echo date('Y-m-d'); ?>.pdf',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { 
                scale: 4, 
                useCORS: true,
                letterRendering: true,
                allowTaint: true
            },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
        };
        
        html2pdf().set(opt).from(container).save().then(() => {
            cerrarVistaPrevia();
        });
    }
    </script>

    <!-- Modal de Vista Previa -->
    <div id="modal-preview" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; overflow-y: auto; padding: 20px;">
        <div style="background: white; border-radius: 12px; width: 100%; max-width: 1100px; padding: 20px; position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <h2 style="margin: 0; color: var(--primary);">Vista Previa del Reporte</h2>
                <div style="display: flex; gap: 10px;">
                    <button onclick="confirmarDescarga()" class="btn btn-success">
                        <i class="fas fa-download"></i> Confirmar y Descargar
                    </button>
                    <button onclick="cerrarVistaPrevia()" class="btn btn-danger">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
            <div id="preview-content" style="background: #f0f0f0; padding: 20px; border-radius: 8px; max-height: 70vh; overflow-y: auto;">
                <!-- El contenido se cargará aquí -->
            </div>
        </div>
    </div>
</body>
</html>