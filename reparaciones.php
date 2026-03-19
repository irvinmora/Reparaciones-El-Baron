<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

require_once 'includes/db.php';

$action = $_GET['action'] ?? 'lista';
$mensaje = '';
$error = '';

// API para obtener reparaciones pendientes de un cliente (usado por el buscador de pagos)
if ($action == 'api_pendientes' && isset($_GET['cliente_id'])) {
    $cliente_id = (int)$_GET['cliente_id'];
    $sql = "SELECT id, producto, costo_total, saldo_pendiente, DATE_FORMAT(fecha_ingreso, '%d/%m/%Y') as fecha_ingreso 
            FROM reparaciones 
            WHERE cliente_id = $cliente_id AND saldo_pendiente > 0 
            ORDER BY fecha_ingreso DESC";
    $result = $conn->query($sql);
    $pendientes = [];
    while ($row = $result->fetch_assoc()) {
        $pendientes[] = $row;
    }
    header('Content-Type: application/json');
    echo json_encode($pendientes);
    exit;
}

// Procesar formulario de nueva reparación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_reparacion'])) {
    $cliente_id = (int)$_POST['cliente_id'];
    $producto = $conn->real_escape_string($_POST['producto']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $costo_total = (float)$_POST['costo_total'];
    $abono = (float)($_POST['abono'] ?? 0);
    $saldo = $costo_total - $abono;
    
    // Si no hay ID de cliente, es un cliente nuevo
    if ($cliente_id == 0) {
        $nombres = $conn->real_escape_string($_POST['nombres_nuevo']);
        $apellidos = $conn->real_escape_string($_POST['apellidos_nuevo']);
        $telefono = $conn->real_escape_string($_POST['telefono_nuevo']);
        $direccion = $conn->real_escape_string($_POST['direccion_nuevo']);
        
        if (!empty($nombres) && !empty($apellidos)) {
            $sql_cliente = "INSERT INTO clientes (nombres, apellidos, telefono, direccion) VALUES ('$nombres', '$apellidos', '$telefono', '$direccion')";
            if ($conn->query($sql_cliente)) {
                $cliente_id = $conn->insert_id;
            } else {
                $error = "Error al registrar el nuevo cliente: " . $conn->error;
            }
        } else {
            $error = "Los datos del nuevo cliente son incompletos.";
        }
    }
    
    if ($cliente_id > 0) {
        $sql = "INSERT INTO reparaciones (cliente_id, producto, descripcion_problema, costo_total, abono_inicial, saldo_pendiente) 
                VALUES ($cliente_id, '$producto', '$descripcion', $costo_total, $abono, $saldo)";
        
        if ($conn->query($sql)) {
            $reparacion_id = $conn->insert_id;
            
            // Registrar pago inicial si hay abono
            if ($abono > 0) {
                $conn->query("INSERT INTO pagos_reparacion (reparacion_id, monto, tipo_pago) VALUES ($reparacion_id, $abono, 'abono')");
            }
            
            // Redirigir para mostrar el sticker
            header("Location: generar_sticker.php?tipo=reparacion&id=$reparacion_id");
            exit;
        } else {
            $error = "Error al guardar la reparación: " . $conn->error;
        }
    }
}

// Procesar pago adicional
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_pago'])) {
    $reparacion_id = (int)$_POST['reparacion_id'];
    $monto_pago = (float)$_POST['monto_pago'];
    
    // Obtener reparación actual
    $reparacion = $conn->query("SELECT * FROM reparaciones WHERE id = $reparacion_id")->fetch_assoc();
    $nuevo_saldo = $reparacion['saldo_pendiente'] - $monto_pago;
    $tipo_pago = ($nuevo_saldo <= 0) ? 'pago_completo' : 'abono';
    
    // Actualizar saldo
    $conn->query("UPDATE reparaciones SET saldo_pendiente = $nuevo_saldo WHERE id = $reparacion_id");
    
    // Registrar pago
    $conn->query("INSERT INTO pagos_reparacion (reparacion_id, monto, tipo_pago) VALUES ($reparacion_id, $monto_pago, '$tipo_pago')");
    
    if ($nuevo_saldo <= 0) {
        $conn->query("UPDATE reparaciones SET estado = 'completado', fecha_entrega = NOW() WHERE id = $reparacion_id");
    }
    
    $mensaje = "Pago registrado correctamente";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reparaciones - El Barón</title>
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
                <h1>Gestión de Reparaciones</h1>
                <a href="?action=nueva" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Reparación
                </a>
                <a href="?action=buscar_pago" class="btn btn-success">
                    <i class="fas fa-money-bill-wave"></i> Agregar Pago
                </a>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'nueva'): ?>
                <!-- Formulario de nueva reparación -->
                <div class="form-container">
                    <h2>Registrar Nueva Reparación</h2>
                    
                    <form method="POST" action="" id="form-reparacion">
                        <div class="form-group">
                            <label for="search-cliente">Buscar Cliente</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="search-cliente" class="form-control" placeholder="Escriba nombre del cliente..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                <button type="button" class="btn btn-primary" onclick="buscarClientes()">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                            <div id="suggestions-cliente" class="suggestions" style="display: none; position: absolute; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; width: 50%; z-index: 1000;"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cliente_id">ID Cliente (0 para nuevo)</label>
                                <input type="number" id="cliente_id" name="cliente_id" value="0" readonly class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nombres_nuevo">Nombres *</label>
                                <input type="text" id="nombres_nuevo" name="nombres_nuevo" required class="form-control" placeholder="Escriba nombres..." oninput="this.value = this.value.toUpperCase(); checkClienteExistente()">
                            </div>
                            <div class="form-group">
                                <label for="apellidos_nuevo">Apellidos *</label>
                                <input type="text" id="apellidos_nuevo" name="apellidos_nuevo" required class="form-control" placeholder="Escriba apellidos..." oninput="this.value = this.value.toUpperCase(); checkClienteExistente()">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono_nuevo">Teléfono</label>
                                <input type="text" id="telefono_nuevo" name="telefono_nuevo" class="form-control" placeholder="Escriba teléfono...">
                            </div>
                            <div class="form-group">
                                <label for="direccion_nuevo">Dirección</label>
                                <input type="text" id="direccion_nuevo" name="direccion_nuevo" class="form-control" placeholder="Escriba dirección...">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="producto">Electrodoméstico</label>
                            <select id="producto" name="producto" required class="form-control">
                                <option value="">Seleccione...</option>
                                <option value="Licuadora">Licuadora</option>
                                <option value="Microondas">Microondas</option>
                                <option value="Ventilador">Ventilador</option>
                                <option value="Plancha">Plancha</option>
                                <option value="Refrigeradora">Refrigeradora</option>
                                <option value="Lavadora">Lavadora</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción del Problema</label>
                            <textarea id="descripcion" name="descripcion" rows="4" required class="form-control"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="costo_total">Costo Total ($)</label>
                                <input type="number" id="costo_total" name="costo_total" step="0.01" min="0" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="abono">Abono Inicial ($)</label>
                                <input type="number" id="abono" name="abono" step="0.01" min="0" value="0" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="guardar_reparacion" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar y Generar Sticker
                            </button>
                            <a href="?action=lista" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php elseif ($action == 'buscar_pago'): ?>
                <!-- Buscador de cliente para pago -->
                <div class="form-container">
                    <h2>Buscar Cliente para Registrar Pago</h2>
                    <p>Ingrese el nombre o apellido del cliente para ver sus reparaciones pendientes.</p>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="search-pago-cliente" class="form-control" placeholder="Nombre del cliente..." oninput="buscarClientesPago(this.value)">
                        </div>
                        <div id="suggestions-pago" class="suggestions" style="display: none; position: relative; border: 1px solid #ddd; width: 100%;"></div>
                    </div>
                    
                    <div id="resultado-pago" style="margin-top: 30px;">
                        <!-- Aquí se mostrarán las reparaciones del cliente seleccionado -->
                    </div>
                </div>

                <script>
                function buscarClientesPago(termino) {
                    if (termino.length < 1) {
                        document.getElementById('suggestions-pago').style.display = 'none';
                        return;
                    }
                    fetch('buscar_clientes.php?q=' + encodeURIComponent(termino))
                        .then(response => response.json())
                        .then(data => {
                            const suggestionsDiv = document.getElementById('suggestions-pago');
                            if (data.length > 0) {
                                let html = '';
                                data.forEach(cliente => {
                                    html += `
                                        <div class="suggestion-item" onclick="cargarReparacionesPendientes(${cliente.id}, '${cliente.nombres} ${cliente.apellidos}')" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                                            <strong>${cliente.nombres} ${cliente.apellidos}</strong><br>
                                            <small>Tel: ${cliente.telefono}</small>
                                        </div>
                                    `;
                                });
                                suggestionsDiv.innerHTML = html;
                                suggestionsDiv.style.display = 'block';
                            } else {
                                suggestionsDiv.style.display = 'none';
                            }
                        });
                }

                function cargarReparacionesPendientes(clienteId, nombre) {
                    document.getElementById('suggestions-pago').style.display = 'none';
                    document.getElementById('search-pago-cliente').value = nombre;
                    
                    const resultadoDiv = document.getElementById('resultado-pago');
                    resultadoDiv.innerHTML = '<p>Cargando reparaciones...</p>';
                    
                    fetch('reparaciones.php?action=api_pendientes&cliente_id=' + clienteId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                let html = `<h3>Reparaciones Pendientes de ${nombre}</h3>
                                           <table class="data-table">
                                           <thead><tr><th>Equipo</th><th>Fecha</th><th>Costo total</th><th>Saldo pendiente</th><th>Acción</th></tr></thead>
                                           <tbody>`;
                                data.forEach(r => {
                                    html += `<tr>
                                        <td>${r.producto}</td>
                                        <td>${r.fecha_ingreso}</td>
                                        <td>$${r.costo_total}</td>
                                        <td style="color: red; font-weight: bold;">$${r.saldo_pendiente}</td>
                                        <td>
                                            <a href="?action=pago&id=${r.id}" class="btn btn-success btn-small">
                                                <i class="fas fa-money-bill"></i> Pagar
                                            </a>
                                        </td>
                                    </tr>`;
                                });
                                html += '</tbody></table>';
                                resultadoDiv.innerHTML = html;
                            } else {
                                resultadoDiv.innerHTML = '<div class="alert alert-info">El cliente no tiene reparaciones con saldo pendiente.</div>';
                            }
                        });
                }
                </script>
                
            <?php elseif ($action == 'pago'): ?>
                <!-- Formulario para agregar pago -->
                <?php
                $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                
                if ($id > 0) {
                    $res_reparacion = $conn->query("
                        SELECT r.*, c.nombres, c.apellidos, c.telefono 
                        FROM reparaciones r 
                        JOIN clientes c ON r.cliente_id = c.id 
                        WHERE r.id = $id
                    ");
                    
                    if ($res_reparacion && $res_reparacion->num_rows > 0) {
                        $reparacion = $res_reparacion->fetch_assoc();
                    } else {
                        $error = "Reparación no encontrada o ID inválido.";
                        $reparacion = null;
                    }
                } else {
                    $error = "ID de reparación no especificado.";
                    $reparacion = null;
                }
                ?>
                
                <?php if ($reparacion): ?>
                <div class="form-container">
                    <h2>Agregar Pago a Reparación</h2>
                    
                    <div class="reparacion-info" style="background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                        <p><strong>Cliente:</strong> <?php echo $reparacion['nombres'] . ' ' . $reparacion['apellidos']; ?></p>
                        <p><strong>Teléfono:</strong> <?php echo $reparacion['telefono']; ?></p>
                        <p><strong>Producto:</strong> <?php echo $reparacion['producto']; ?></p>
                        <p><strong>Costo total:</strong> $<?php echo number_format($reparacion['costo_total'], 2); ?></p>
                        <p><strong>Abonado:</strong> $<?php echo number_format($reparacion['costo_total'] - $reparacion['saldo_pendiente'], 2); ?></p>
                        <p><strong>Saldo pendiente:</strong> $<?php echo number_format($reparacion['saldo_pendiente'], 2); ?></p>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="reparacion_id" value="<?php echo $id; ?>">
                        
                        <div class="form-group">
                            <label for="monto_pago">Monto a Pagar ($)</label>
                            <input type="number" id="monto_pago" name="monto_pago" step="0.01" min="0.01" max="<?php echo $reparacion['saldo_pendiente']; ?>" required class="form-control">
                        </div>
                        
                        <?php 
                        $back_url = "?action=lista";
                        if (isset($_GET['from']) && $_GET['from'] == 'pagos') {
                            $back_url = "pagos_pendientes.php";
                        }
                        ?>
                        <div class="form-group">
                            <button type="submit" name="agregar_pago" class="btn btn-success">
                                <i class="fas fa-money-bill"></i> Registrar Pago
                            </button>
                            <a href="<?php echo $back_url; ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="?action=buscar_pago" class="btn btn-primary">Volver al Buscador</a>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($action == 'ver' && isset($_GET['id'])): ?>
                <!-- Vista de detalle de reparación -->
                <?php
                $id = (int)$_GET['id'];
                $res = $conn->query("
                    SELECT r.*, c.nombres, c.apellidos, c.telefono, c.direccion 
                    FROM reparaciones r 
                    JOIN clientes c ON r.cliente_id = c.id 
                    WHERE r.id = $id
                ");
                $r = $res->fetch_assoc();
                ?>
                
                <?php if ($r): ?>
                <div class="form-container">
                    <h2>Detalle de Reparación #<?php echo $id; ?></h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                        <div class="info-card" style="background: #f9f9f9; padding: 20px; border-radius: 10px;">
                            <h3 style="border-bottom: 2px solid var(--secondary); padding-bottom: 5px; margin-bottom: 10px;">Información del Cliente</h3>
                            <p><strong>Nombre:</strong> <?php echo $r['nombres'] . ' ' . $r['apellidos']; ?></p>
                            <p><strong>Teléfono:</strong> <?php echo $r['telefono']; ?></p>
                            <p><strong>Dirección:</strong> <?php echo $r['direccion']; ?></p>
                        </div>
                        <div class="info-card" style="background: #f9f9f9; padding: 20px; border-radius: 10px;">
                            <h3 style="border-bottom: 2px solid var(--secondary); padding-bottom: 5px; margin-bottom: 10px;">Estado de Cuenta</h3>
                            <p><strong>Costo total:</strong> $<?php echo number_format($r['costo_total'], 2); ?></p>
                            <p><strong>Abono Inicial:</strong> $<?php echo number_format($r['abono_inicial'], 2); ?></p>
                            <p style="color: red; font-weight: bold; font-size: 1.2em;"><strong>Saldo pendiente:</strong> $<?php echo number_format($r['saldo_pendiente'], 2); ?></p>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; background: #fff; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
                        <h3 style="margin-bottom: 15px;">Tipo de electrodoméstico: <span style="color: red;"><?php echo $r['producto']; ?></span></h3>
                        <p><strong>Descripción del Problema:</strong></p>
                        <p style="background: #fff8eb; padding: 15px; border-left: 4px solid var(--warning); margin-top: 10px;">
                            <?php echo nl2br($r['descripcion_problema']); ?>
                        </p>
                        <p style="margin-top: 15px;"><strong>Estado Actual:</strong> <span class="badge <?php echo $r['estado']; ?>"><?php echo strtoupper($r['estado']); ?></span></p>
                        <p><strong>Fecha de Ingreso:</strong> <?php echo date('d/m/Y H:i', strtotime($r['fecha_ingreso'])); ?></p>
                    </div>
                    
                    <div style="margin-top: 30px; display: flex; gap: 10px; justify-content: center;">
                        <?php 
                        $back_url = "?action=lista";
                        if (isset($_GET['from']) && $_GET['from'] == 'pagos') {
                            $back_url = "pagos_pendientes.php";
                        }
                        ?>
                        <?php if ($r['saldo_pendiente'] > 0): ?>
                            <a href="?action=pago&id=<?php echo $id; ?>&from=<?= isset($_GET['from']) ? $_GET['from'] : '' ?>" class="btn btn-success">
                                <i class="fas fa-money-bill-wave"></i> Registrar Pago
                            </a>
                        <?php endif; ?>
                        <button onclick="generarSticker(<?php echo $id; ?>)" class="btn btn-primary">
                            <i class="fas fa-tag"></i> Generar Sticker
                        </button>
                        <a href="<?php echo $back_url; ?>" class="btn btn-secondary">Volver a la Lista</a>
                    </div>
                </div>
                <?php else: ?>
                    <div class="alert alert-error">Reparación no encontrada.</div>
                    <?php 
                    $back_url = "?action=lista";
                    if (isset($_GET['from']) && $_GET['from'] == 'pagos') {
                        $back_url = "pagos_pendientes.php";
                    }
                    ?>
                    <a href="<?php echo $back_url; ?>" class="btn btn-secondary">Volver a la Lista</a>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Lista de reparaciones -->
                <div class="search-box" style="margin-bottom: 20px;">
                    <input type="text" id="buscar-reparacion" placeholder="Buscar por cliente o producto..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <button onclick="buscarReparaciones()" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Costo total</th>
                            <th>Saldo pendiente</th>
                            <th>Estado</th>
                            <th>Fecha Ingreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT r.*, c.nombres, c.apellidos 
                                FROM reparaciones r 
                                JOIN clientes c ON r.cliente_id = c.id 
                                ORDER BY r.fecha_ingreso DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows == 0):
                        ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No hay reparaciones registradas</td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></td>
                                <td><?php echo $row['producto']; ?></td>
                                <td>$<?php echo number_format($row['costo_total'], 2); ?></td>
                                <td>$<?php echo number_format($row['saldo_pendiente'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $row['estado']; ?>">
                                        <?php echo $row['estado']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_ingreso'])); ?></td>
                                <td>
                                    <?php if ($row['saldo_pendiente'] > 0): ?>
                                        <a href="?action=pago&id=<?php echo $row['id']; ?>" class="btn-small btn-success" title="Agregar Pago">
                                            <i class="fas fa-money-bill"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="reparaciones.php?action=ver&id=<?php echo $row['id']; ?>" class="btn-small" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="generarSticker(<?php echo $row['id']; ?>)" class="btn-small" title="Generar Sticker">
                                        <i class="fas fa-tag"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    let timerCliente;
    function checkClienteExistente() {
        clearTimeout(timerCliente);
        const nombres = document.getElementById('nombres_nuevo').value;
        const apellidos = document.getElementById('apellidos_nuevo').value;
        
        if (nombres.length > 2 && apellidos.length > 2) {
            timerCliente = setTimeout(() => {
                fetch('buscar_clientes.php?q=' + encodeURIComponent(nombres + ' ' + apellidos))
                    .then(response => response.json())
                    .then(data => {
                        const suggestionsDiv = document.getElementById('suggestions-cliente');
                        if (data.length > 0) {
                            // Encontrado posible duplicado
                            let html = '<div style="background: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 10px;">';
                            html += '<strong>¡Atención!</strong> Este cliente parece que ya está registrado. ';
                            html += '¿Desea cargar sus datos?<br><br>';
                            data.forEach(cliente => {
                                html += `<button type="button" class="btn btn-small btn-primary" style="margin-right: 5px; margin-bottom: 5px;" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombres}', '${cliente.apellidos}', '${cliente.telefono}', '${cliente.direccion}')">`;
                                html += `${cliente.nombres} ${cliente.apellidos} (${cliente.telefono})</button>`;
                            });
                            html += '</div>';
                            suggestionsDiv.innerHTML = html;
                            suggestionsDiv.style.display = 'block';
                            suggestionsDiv.style.position = 'relative';
                            suggestionsDiv.style.width = '100%';
                        } else {
                            // No hay duplicados exactos, ocultar si estaba abierto por esta función
                            if (suggestionsDiv.innerHTML.includes('¡Atención!')) {
                                suggestionsDiv.style.display = 'none';
                            }
                        }
                    });
            }, 500);
        }
    }

    function buscarClientes() {
        const termino = document.getElementById('search-cliente').value;
        if (termino.length < 2) {
            alert('Escriba al menos 2 caracteres para buscar');
            return;
        }
        
        fetch('buscar_clientes.php?q=' + encodeURIComponent(termino))
            .then(response => response.json())
            .then(data => {
                const suggestionsDiv = document.getElementById('suggestions-cliente');
                if (data.length > 0) {
                    let html = '';
                    data.forEach(cliente => {
                        html += `
                            <div class="suggestion-item" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombres}', '${cliente.apellidos}', '${cliente.telefono}', '${cliente.direccion}')" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                                <strong>${cliente.nombres} ${cliente.apellidos}</strong><br>
                                Tel: ${cliente.telefono}<br>
                                <small>${cliente.direccion}</small>
                            </div>
                        `;
                    });
                    suggestionsDiv.innerHTML = html;
                    suggestionsDiv.style.display = 'block';
                } else {
                    suggestionsDiv.innerHTML = '<div style="padding: 10px;">No se encontraron clientes</div>';
                    suggestionsDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al buscar clientes');
            });
    }
    
    function seleccionarCliente(id, nombres, apellidos, telefono, direccion) {
        document.getElementById('cliente_id').value = id;
        document.getElementById('nombres_nuevo').value = nombres;
        document.getElementById('apellidos_nuevo').value = apellidos;
        document.getElementById('telefono_nuevo').value = telefono;
        document.getElementById('direccion_nuevo').value = direccion;
        
        // Bloquear campos si se seleccionó uno existente (opcional, pero mejor dejar editable por si acaso)
        // document.getElementById('nombres_nuevo').readOnly = true;
        // document.getElementById('apellidos_nuevo').readOnly = true;
        
        document.getElementById('suggestions-cliente').style.display = 'none';
        document.getElementById('search-cliente').value = '';
    }
    
    function buscarReparaciones() {
        const termino = document.getElementById('buscar-reparacion').value.toLowerCase();
        const filas = document.querySelectorAll('tbody tr');
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            if (texto.includes(termino)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
    
    function generarSticker(id) {
        window.open('generar_sticker.php?tipo=reparacion&id=' + id, '_blank', 'width=500,height=700');
    }
    
    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(event) {
        const suggestions = document.getElementById('suggestions-cliente');
        const searchInput = document.getElementById('search-cliente');
        if (suggestions && searchInput) {
            if (!searchInput.contains(event.target) && !suggestions.contains(event.target)) {
                suggestions.style.display = 'none';
            }
        }
    });
    </script>
    
    <style>
    .suggestions {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        max-height: 300px;
        overflow-y: auto;
        width: 50%;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .suggestion-item:hover {
        background: #f0f0f0;
    }
    
    .btn-secondary {
        background: #95a5a6;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-secondary:hover {
        background: #7f8c8d;
    }
    
    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: <?php echo COLOR_SECONDARY; ?>;
    }
    </style>
</body>
</html>