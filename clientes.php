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

// Procesar formulario de nuevo cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_cliente'])) {
    $nombres = $conn->real_escape_string($_POST['nombres']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Verificar si el email ya existe (si se proporcionó)
    if (!empty($email)) {
        $check = $conn->query("SELECT id FROM clientes WHERE email = '$email'");
        if ($check->num_rows > 0) {
            $error = "El email ya está registrado para otro cliente";
        }
    }
    
    if (empty($error)) {
        $sql = "INSERT INTO clientes (nombres, apellidos, telefono, direccion, email) 
                VALUES ('$nombres', '$apellidos', '$telefono', '$direccion', " . ($email ? "'$email'" : "NULL") . ")";
        
        if ($conn->query($sql)) {
            $mensaje = "Cliente registrado correctamente";
        } else {
            $error = "Error al guardar el cliente: " . $conn->error;
        }
    }
}

// Procesar actualización de cliente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_cliente'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $nombres = $conn->real_escape_string($_POST['nombres']);
    $apellidos = $conn->real_escape_string($_POST['apellidos']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $email = $conn->real_escape_string($_POST['email']);
    
    $sql = "UPDATE clientes SET 
            nombres = '$nombres',
            apellidos = '$apellidos',
            telefono = '$telefono',
            direccion = '$direccion',
            email = " . ($email ? "'$email'" : "NULL") . "
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        $mensaje = "Cliente actualizado correctamente";
        $action = 'lista';
    } else {
        $error = "Error al actualizar: " . $conn->error;
    }
}

// Eliminar cliente
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si tiene reparaciones o ventas
    $check_reparaciones = $conn->query("SELECT id FROM reparaciones WHERE cliente_id = $id LIMIT 1");
    $check_ventas = $conn->query("SELECT id FROM ventas WHERE cliente_id = $id LIMIT 1");
    
    if ($check_reparaciones->num_rows > 0 || $check_ventas->num_rows > 0) {
        $error = "No se puede eliminar el cliente porque tiene reparaciones o ventas asociadas";
    } else {
        $conn->query("DELETE FROM clientes WHERE id = $id");
        $mensaje = "Cliente eliminado correctamente";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - El Barón</title>
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
                <h1>Gestión de Clientes</h1>
                <a href="?action=nuevo" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </a>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'nuevo'): ?>
                <!-- Formulario de nuevo cliente -->
                <div class="form-container">
                    <h2>Registrar Nuevo Cliente</h2>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombres">Nombres *</label>
                                <input type="text" id="nombres" name="nombres" required 
                                       placeholder="Ej: Juan Carlos" oninput="this.value = this.value.toUpperCase()">
                            </div>
                            
                            <div class="form-group">
                                <label for="apellidos">Apellidos *</label>
                                <input type="text" id="apellidos" name="apellidos" required 
                                       placeholder="Ej: Pérez Gómez" oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono *</label>
                                <input type="text" id="telefono" name="telefono" required 
                                       placeholder="Ej: 0998765432">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       placeholder="correo@ejemplo.com">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección *</label>
                            <textarea id="direccion" name="direccion" rows="2" required 
                                      placeholder="Dirección completa del cliente"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <small>* Campos obligatorios</small>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="guardar_cliente" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Cliente
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php elseif ($action == 'editar' && isset($_GET['id'])): ?>
                <?php
                $id = (int)$_GET['id'];
                $cliente = $conn->query("SELECT * FROM clientes WHERE id = $id")->fetch_assoc();
                ?>
                
                <div class="form-container">
                    <h2>Editar Cliente</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombres">Nombres</label>
                                <input type="text" id="nombres" name="nombres" required 
                                       value="<?php echo $cliente['nombres']; ?>" oninput="this.value = this.value.toUpperCase()">
                            </div>
                            
                            <div class="form-group">
                                <label for="apellidos">Apellidos</label>
                                <input type="text" id="apellidos" name="apellidos" required 
                                       value="<?php echo $cliente['apellidos']; ?>" oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" required 
                                       value="<?php echo $cliente['telefono']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo $cliente['email']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion">Dirección</label>
                            <textarea id="direccion" name="direccion" rows="2" required><?php echo $cliente['direccion']; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="actualizar_cliente" class="btn btn-success">
                                <i class="fas fa-save"></i> Actualizar Cliente
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php elseif ($action == 'ver' && isset($_GET['id'])): ?>
                <?php
                $id = (int)$_GET['id'];
                $cliente = $conn->query("SELECT * FROM clientes WHERE id = $id")->fetch_assoc();
                
                // Obtener reparaciones del cliente
                $reparaciones = $conn->query("
                    SELECT * FROM reparaciones 
                    WHERE cliente_id = $id 
                    ORDER BY fecha_ingreso DESC
                ");
                
                // Obtener ventas del cliente
                $ventas = $conn->query("
                    SELECT v.*, COUNT(dv.id) as productos 
                    FROM ventas v
                    LEFT JOIN detalle_venta dv ON v.id = dv.venta_id
                    WHERE v.cliente_id = $id 
                    GROUP BY v.id
                    ORDER BY v.fecha_venta DESC
                ");
                ?>
                
                <div class="form-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2>Detalle del Cliente</h2>
                        <a href="?action=editar&id=<?php echo $id; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </div>
                    
                    <div class="stats-grid" style="margin-bottom: 20px;">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: <?php echo COLOR_SECONDARY; ?>">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Cliente</h3>
                                <p><?php echo $cliente['nombres'] . ' ' . $cliente['apellidos']; ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: <?php echo COLOR_ACCENT; ?>">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Teléfono</h3>
                                <p><?php echo $cliente['telefono']; ?></p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: <?php echo COLOR_PRIMARY; ?>">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="stat-info">
                                <h3>Email</h3>
                                <p><?php echo $cliente['email'] ?? 'No registrado'; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <h3>Dirección</h3>
                        <p><?php echo $cliente['direccion']; ?></p>
                    </div>
                    
                    <h3>Historial de Reparaciones</h3>
                    <table class="data-table" style="margin-bottom: 20px;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reparaciones->num_rows == 0): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No hay reparaciones registradas</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = $reparaciones->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
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
                                        <a href="reparaciones.php?action=ver&id=<?php echo $row['id']; ?>" class="btn-small">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <h3>Historial de Compras</h3>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Venta</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ventas->num_rows == 0): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No hay compras registradas</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = $ventas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['productos']; ?> producto(s)</td>
                                    <td>$<?php echo number_format($row['total'], 2); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['fecha_venta'])); ?></td>
                                    <td>
                                        <a href="ventas.php?action=ver&id=<?php echo $row['id']; ?>" class="btn-small">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 20px;">
                        <a href="?action=lista" class="btn">
                            <i class="fas fa-arrow-left"></i> Volver a la lista
                        </a>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Lista de clientes -->
                <div class="search-box">
                    <input type="text" id="buscar-cliente" placeholder="Buscar por nombres, apellidos, teléfono o email...">
                    <button onclick="buscarClientes()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM clientes ORDER BY fecha_registro DESC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows == 0):
                        ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No hay clientes registrados</td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['nombres']; ?></td>
                                <td><?php echo $row['apellidos']; ?></td>
                                <td><?php echo $row['telefono']; ?></td>
                                <td><?php echo $row['email'] ?? '-'; ?></td>
                                <td><?php echo substr($row['direccion'], 0, 30) . '...'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                                <td>
                                    <a href="?action=ver&id=<?php echo $row['id']; ?>" class="btn-small" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="?action=editar&id=<?php echo $row['id']; ?>" class="btn-small" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-small btn-danger" 
                                       onclick="return confirm('¿Estás seguro de eliminar este cliente?')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
    <script>
    function buscarClientes() {
        const termino = document.getElementById('buscar-cliente').value.toLowerCase();
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
    </script>
</body>
</html>