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

// Procesar venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['realizar_venta'])) {
    $cliente_id = $conn->real_escape_string($_POST['cliente_id']);
    $productos = json_decode($_POST['productos'], true);
    $total = $conn->real_escape_string($_POST['total']);
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Registrar venta
        $sql_venta = "INSERT INTO ventas (cliente_id, total) VALUES ('$cliente_id', '$total')";
        $conn->query($sql_venta);
        $venta_id = $conn->insert_id;
        
        // Registrar detalles y actualizar stock
        foreach ($productos as $producto) {
            $producto_id = $producto['id'];
            $cantidad = $producto['cantidad'];
            $precio = $producto['precio'];
            $subtotal = $producto['subtotal'];
            
            $sql_detalle = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                           VALUES ('$venta_id', '$producto_id', '$cantidad', '$precio', '$subtotal')";
            $conn->query($sql_detalle);
            
            // Actualizar stock
            actualizarStock($producto_id, $cantidad, 'restar');
        }
        
        $conn->commit();
        
        // Redirigir para mostrar el sticker
        header("Location: generar_sticker.php?tipo=venta&id=$venta_id");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error al procesar la venta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - El Barón</title>
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
                <h1>Gestión de Ventas</h1>
                <a href="?action=nueva" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Venta
                </a>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'nueva'): ?>
                <!-- Formulario de nueva venta -->
                <div class="form-container">
                    <h2>Registrar Nueva Venta</h2>
                    
                    <form method="POST" action="" id="form-venta">
                        <!-- Datos del Cliente -->
                        <h3>Datos del Cliente</h3>
                        <div class="form-group">
                            <label for="search-cliente">Buscar Cliente</label>
                            <div class="search-box">
                                <input type="text" id="search-cliente" placeholder="Escriba nombre del cliente..." oninput="this.value = this.value.toUpperCase()">
                                <button type="button" onclick="buscarClientes(document.getElementById('search-cliente').value)">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="suggestions-cliente" class="suggestions"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cliente_id">ID Cliente</label>
                                <input type="text" id="cliente_id" name="cliente_id" readonly required>
                            </div>
                            <div class="form-group">
                                <label for="nombre_cliente">Nombre Completo</label>
                                <input type="text" id="nombre_cliente" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" readonly>
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" readonly>
                            </div>
                        </div>
                        
                        <!-- Productos -->
                        <h3>Productos</h3>
                        <div class="form-group">
                            <label for="search-producto">Buscar Producto</label>
                            <div class="search-box">
                                <input type="text" id="search-producto" placeholder="Escriba nombre del producto...">
                                <button type="button" onclick="buscarProductos(document.getElementById('search-producto').value)">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="suggestions-producto" class="suggestions"></div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="producto_id">ID Producto</label>
                                <input type="text" id="producto_id" readonly>
                            </div>
                            <div class="form-group">
                                <label for="nombre_producto">Nombre Producto</label>
                                <input type="text" id="nombre_producto" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="stock_disponible">Stock Disponible</label>
                                <input type="text" id="stock_disponible" readonly>
                            </div>
                            <div class="form-group">
                                <label for="precio">Precio ($)</label>
                                <input type="text" id="precio" readonly>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cantidad">Cantidad</label>
                                <input type="number" id="cantidad" min="1" value="1">
                            </div>
                            <div class="form-group">
                                <label for="subtotal">Subtotal</label>
                                <input type="text" id="subtotal" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" onclick="agregarProductoVenta()">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                        
                        <!-- Tabla de productos agregados -->
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="productos-venta">
                                <!-- Se llena con JavaScript -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" style="text-align: right;">Total:</th>
                                    <th>
                                        <input type="text" id="total-venta" name="total" readonly value="0.00">
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="form-group">
                            <button type="submit" name="realizar_venta" class="btn btn-success">
                                <i class="fas fa-check"></i> Realizar Venta y Generar Sticker
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <!-- Lista de ventas -->
                <div class="search-box">
                    <input type="text" id="buscar-venta" placeholder="Buscar por cliente o fecha...">
                    <button onclick="buscarVentas()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Venta</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT v.*, c.nombres, c.apellidos 
                                FROM ventas v 
                                JOIN clientes c ON v.cliente_id = c.id 
                                ORDER BY v.fecha_venta DESC";
                        $result = $conn->query($sql);
                        
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nombres'] . ' ' . $row['apellidos']; ?></td>
                            <td>$<?php echo number_format($row['total'], 2); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_venta'])); ?></td>
                            <td>
                                <a href="ver_venta.php?id=<?php echo $row['id']; ?>" class="btn-small" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="generarStickerVenta(<?php echo $row['id']; ?>)" class="btn-small" title="Generar Sticker">
                                    <i class="fas fa-tag"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    let productosCarrito = [];

    function buscarClientes(termino) {
        if (termino.length < 1) return;
        fetch('buscar_clientes.php?q=' + encodeURIComponent(termino))
            .then(response => response.json())
            .then(data => {
                const suggestionsDiv = document.getElementById('suggestions-cliente');
                if (data.length > 0) {
                    let html = '';
                    data.forEach(cliente => {
                        html += `
                            <div class="suggestion-item" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombres}', '${cliente.apellidos}', '${cliente.telefono}', '${cliente.direccion}')">
                                <strong>${cliente.nombres} ${cliente.apellidos}</strong><br>
                                <small>${cliente.telefono}</small>
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

    function seleccionarCliente(id, nombres, apellidos, telefono, direccion) {
        document.getElementById('cliente_id').value = id;
        document.getElementById('nombre_cliente').value = nombres + ' ' + apellidos;
        document.getElementById('telefono').value = telefono;
        document.getElementById('direccion').value = direccion;
        document.getElementById('suggestions-cliente').style.display = 'none';
    }

    function buscarProductos(termino) {
        if (termino.length < 1) return;
        fetch('buscar_productos.php?q=' + encodeURIComponent(termino))
            .then(response => response.json())
            .then(data => {
                const suggestionsDiv = document.getElementById('suggestions-producto');
                if (data.length > 0) {
                    let html = '';
                    data.forEach(producto => {
                        html += `
                            <div class="suggestion-item" onclick="seleccionarProducto(${producto.id}, '${producto.nombre}', ${producto.precio}, ${producto.stock})">
                                <strong>${producto.nombre}</strong> <span class="price">$${producto.precio}</span><br>
                                <small>Stock: ${producto.stock}</small>
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

    function seleccionarProducto(id, nombre, precio, stock) {
        document.getElementById('producto_id').value = id;
        document.getElementById('nombre_producto').value = nombre;
        document.getElementById('precio').value = precio;
        document.getElementById('stock_disponible').value = stock;
        document.getElementById('cantidad').max = stock;
        document.getElementById('cantidad').value = 1;
        calcularSubtotal();
        document.getElementById('suggestions-producto').style.display = 'none';
    }

    function calcularSubtotal() {
        const precio = parseFloat(document.getElementById('precio').value) || 0;
        const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
        document.getElementById('subtotal').value = (precio * cantidad).toFixed(2);
    }

    document.getElementById('cantidad').addEventListener('input', calcularSubtotal);

    function agregarProductoVenta() {
        const id = document.getElementById('producto_id').value;
        const nombre = document.getElementById('nombre_producto').value;
        const precio = parseFloat(document.getElementById('precio').value);
        const cantidad = parseInt(document.getElementById('cantidad').value);
        const stock = parseInt(document.getElementById('stock_disponible').value);
        const subtotal = parseFloat(document.getElementById('subtotal').value);

        if (!id) return alert('Seleccione un producto');
        if (cantidad > stock) return alert('Stock insuficiente');

        const existe = productosCarrito.find(p => p.id === id);
        if (existe) {
            existe.cantidad += cantidad;
            existe.subtotal = existe.cantidad * existe.precio;
        } else {
            productosCarrito.push({ id, nombre, precio, cantidad, subtotal });
        }

        renderCarrito();
        limpiarProducto();
    }

    function renderCarrito() {
        const tbody = document.getElementById('productos-venta');
        tbody.innerHTML = '';
        let total = 0;

        productosCarrito.forEach((p, i) => {
            total += p.subtotal;
            tbody.innerHTML += `
                <tr>
                    <td>${p.nombre}</td>
                    <td>${p.cantidad}</td>
                    <td>$${p.precio.toFixed(2)}</td>
                    <td>$${p.subtotal.toFixed(2)}</td>
                    <td><button type="button" class="btn-small btn-danger" onclick="quitar(${i})"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
        });

        document.getElementById('total-venta').value = total.toFixed(2);
        
        let hidden = document.getElementById('productos-json');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = 'productos-json';
            hidden.name = 'productos';
            document.getElementById('form-venta').appendChild(hidden);
        }
        hidden.value = JSON.stringify(productosCarrito);
    }

    function quitar(i) {
        productosCarrito.splice(i, 1);
        renderCarrito();
    }

    function limpiarProducto() {
        document.getElementById('producto_id').value = '';
        document.getElementById('nombre_producto').value = '';
        document.getElementById('precio').value = '';
        document.getElementById('stock_disponible').value = '';
        document.getElementById('cantidad').value = 1;
        document.getElementById('subtotal').value = '';
    }
    function generarStickerVenta(id) {
        window.open('generar_sticker.php?tipo=venta&id=' + id, '_blank');
    }

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-box') && !e.target.closest('.suggestions')) {
            document.querySelectorAll('.suggestions').forEach(s => s.style.display = 'none');
        }
    });

    // Búsqueda en vivo mientras se escribe
    document.getElementById('search-cliente').addEventListener('input', (e) => {
        buscarClientes(e.target.value);
    });
    document.getElementById('search-producto').addEventListener('input', (e) => {
        buscarProductos(e.target.value);
    });
    </script>
</body>
</html>