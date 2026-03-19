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

// Procesar formulario de nuevo producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_producto'])) {
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $precio = $conn->real_escape_string($_POST['precio']);
    $stock = $conn->real_escape_string($_POST['stock']);
    $stock_minimo = $conn->real_escape_string($_POST['stock_minimo']);
    
    // Verificar si el código ya existe
    $check = $conn->query("SELECT id FROM productos WHERE codigo = '$codigo'");
    if ($check->num_rows > 0) {
        $error = "El código de producto ya existe";
    } else {
        $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, stock_minimo) 
                VALUES ('$codigo', '$nombre', '$descripcion', '$precio', '$stock', '$stock_minimo')";
        
        if ($conn->query($sql)) {
            $mensaje = "Producto registrado correctamente";
        } else {
            $error = "Error al guardar el producto: " . $conn->error;
        }
    }
}

// Procesar actualización de producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_producto'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $codigo = $conn->real_escape_string($_POST['codigo']);
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $precio = $conn->real_escape_string($_POST['precio']);
    $stock = $conn->real_escape_string($_POST['stock']);
    $stock_minimo = $conn->real_escape_string($_POST['stock_minimo']);
    
    $sql = "UPDATE productos SET 
            codigo = '$codigo',
            nombre = '$nombre',
            descripcion = '$descripcion',
            precio = '$precio',
            stock = '$stock',
            stock_minimo = '$stock_minimo'
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        $mensaje = "Producto actualizado correctamente";
        $action = 'lista';
    } else {
        $error = "Error al actualizar: " . $conn->error;
    }
}

// Eliminar producto
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Verificar si tiene ventas
    $check = $conn->query("SELECT id FROM detalle_venta WHERE producto_id = $id LIMIT 1");
    if ($check->num_rows > 0) {
        $error = "No se puede eliminar el producto porque tiene ventas asociadas";
    } else {
        $conn->query("DELETE FROM productos WHERE id = $id");
        $mensaje = "Producto eliminado correctamente";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - El Barón</title>
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
                <h1>Gestión de Productos y Repuestos</h1>
                <a href="?action=nuevo" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'nuevo'): ?>
                <!-- Formulario de nuevo producto -->
                <div class="form-container">
                    <h2>Registrar Nuevo Producto/Repuesto</h2>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="codigo">Código del Producto</label>
                                <input type="text" id="codigo" name="codigo" required 
                                       placeholder="Ej: MOT001" maxlength="50">
                                <small>Código único para identificar el producto</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="nombre">Nombre del Producto</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       placeholder="Ej: Motor para licuadora">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3" 
                                      placeholder="Características, modelo, compatibilidad..."></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="precio">Precio de Venta ($)</label>
                                <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock">Stock Inicial</label>
                                <input type="number" id="stock" name="stock" min="0" value="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_minimo">Stock Mínimo</label>
                                <input type="number" id="stock_minimo" name="stock_minimo" min="0" value="5" required>
                                <small>Cantidad mínima para alertar</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="guardar_producto" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Producto
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php elseif ($action == 'editar' && isset($_GET['id'])): ?>
                <?php
                $id = (int)$_GET['id'];
                $producto = $conn->query("SELECT * FROM productos WHERE id = $id")->fetch_assoc();
                ?>
                
                <div class="form-container">
                    <h2>Editar Producto</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="codigo">Código del Producto</label>
                                <input type="text" id="codigo" name="codigo" required 
                                       value="<?php echo $producto['codigo']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="nombre">Nombre del Producto</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       value="<?php echo $producto['nombre']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3"><?php echo $producto['descripcion']; ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="precio">Precio de Venta ($)</label>
                                <input type="number" id="precio" name="precio" step="0.01" min="0" 
                                       value="<?php echo $producto['precio']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock">Stock Actual</label>
                                <input type="number" id="stock" name="stock" min="0" 
                                       value="<?php echo $producto['stock']; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="stock_minimo">Stock Mínimo</label>
                                <input type="number" id="stock_minimo" name="stock_minimo" min="0" 
                                       value="<?php echo $producto['stock_minimo']; ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="actualizar_producto" class="btn btn-success">
                                <i class="fas fa-save"></i> Actualizar Producto
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Lista de productos -->
                <div class="search-box">
                    <input type="text" id="buscar-producto" placeholder="Buscar por código, nombre o descripción...">
                    <button onclick="buscarProductos()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <!-- Alertas de stock bajo -->
                <?php
                $stock_bajo = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock <= stock_minimo")->fetch_assoc();
                if ($stock_bajo['total'] > 0):
                ?>
                <div class="alert alert-warning" style="background: #fff3cd; color: #856404; border-color: #ffeeba;">
                    <i class="fas fa-exclamation-triangle"></i>
                    Hay <?php echo $stock_bajo['total']; ?> producto(s) con stock bajo. ¡Revisa el inventario!
                </div>
                <?php endif; ?>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Stock Mínimo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM productos ORDER BY nombre ASC";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows == 0):
                        ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No hay productos registrados</td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['codigo']; ?></td>
                                <td><?php echo $row['nombre']; ?></td>
                                <td><?php echo $row['descripcion']; ?></td>
                                <td>$<?php echo number_format($row['precio'], 2); ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td><?php echo $row['stock_minimo']; ?></td>
                                <td>
                                    <?php if ($row['stock'] <= $row['stock_minimo']): ?>
                                        <span class="badge" style="background: var(--danger);">Stock Bajo</span>
                                    <?php elseif ($row['stock'] == 0): ?>
                                        <span class="badge" style="background: #999;">Agotado</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--accent);">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?action=editar&id=<?php echo $row['id']; ?>" class="btn-small" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-small btn-danger" 
                                       onclick="return confirm('¿Estás seguro de eliminar este producto?')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <button onclick="verStock(<?php echo $row['id']; ?>)" class="btn-small" title="Ver historial">
                                        <i class="fas fa-history"></i>
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
    
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
    <script>
    function buscarProductos() {
        const termino = document.getElementById('buscar-producto').value.toLowerCase();
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
    
    function verStock(id) {
        // Implementar vista de historial de stock
        alert('Función en desarrollo: Ver historial de stock');
    }
    </script>
</body>
</html>