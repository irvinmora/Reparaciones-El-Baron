<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('/login.php');
}

require_once 'includes/db.php';

$mensaje = '';
$error = '';

// Procesar nuevo gasto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_gasto'])) {
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $monto = $conn->real_escape_string($_POST['monto']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $fecha = $_POST['fecha'] ?: date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO gastos (descripcion, monto, categoria, fecha) VALUES ('$descripcion', '$monto', '$categoria', '$fecha')";
    
    if ($conn->query($sql)) {
        $mensaje = "Gasto registrado correctamente";
    } else {
        $error = "Error al registrar gasto: " . $conn->error;
        // Registro de error para depuración
        error_log("Error INSERT GASTO: " . $conn->error . " SQL: " . $sql);
    }
}

// Eliminar gasto
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM gastos WHERE id = $id");
    $mensaje = "Gasto eliminado correctamente";
}

// Consultar gastos
$sql = "SELECT * FROM gastos ORDER BY fecha DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gastos del Local - El Barón</title>
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
                <h1>Gastos del Local</h1>
                <div class="date"><?php echo date('d/m/Y'); ?></div>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Formulario de nuevo gasto -->
            <div class="form-container" style="margin-bottom: 30px;">
                <h2>Registrar Nuevo Gasto</h2>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="descripcion">Descripción del Gasto</label>
                            <input type="text" id="descripcion" name="descripcion" required placeholder="Ej: Pago de luz, Alquiler, Repuestos">
                        </div>
                        <div class="form-group">
                            <label for="monto">Monto ($)</label>
                            <input type="number" id="monto" name="monto" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria">Categoría</label>
                            <select id="categoria" name="categoria">
                                <option value="General">General</option>
                                <option value="Servicios">Servicios (Luz, Agua, Internet)</option>
                                <option value="Alquiler">Alquiler</option>
                                <option value="Repuestos">Compra de Repuestos</option>
                                <option value="Publicidad">Publicidad</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <button type="submit" name="guardar_gasto" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Gasto
                    </button>
                </form>
            </div>
            
            <!-- Listado de gastos -->
            <div class="recent-section">
                <h2>Historial de Gastos</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Categoría</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows == 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay gastos registrados aún.</td>
                        </tr>
                        <?php else: ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                                <td><?php echo $row['descripcion']; ?></td>
                                <td><span class="badge"><?php echo $row['categoria']; ?></span></td>
                                <td style="font-weight: bold; color: var(--danger);">
                                    -$<?php echo number_format($row['monto'], 2); ?>
                                </td>
                                <td>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-small btn-danger" onclick="return confirm('¿Estás seguro de eliminar este gasto?')">
                                        <i class="fas fa-trash"></i>
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
</body>
</html>
