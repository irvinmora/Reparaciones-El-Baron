<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verificar login y rol de administrador
if (!isLoggedIn()) {
    redirect('/login.php');
}

if (!isAdmin()) {
    redirect('/dashboard.php');
}

require_once 'includes/db.php';

$mensaje = '';
$error = '';

// Obtener o inicializar la configuración
$config = getConfigEmpresa(); // wait I shouldn't leave broken tags

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_config'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $ruc = $conn->real_escape_string($_POST['ruc'] ?? '');
    
    $sql = "UPDATE config_empresa SET nombre = '$nombre', telefono = '$telefono', direccion = '$direccion', ruc = '$ruc'";
    
    if ($conn->query($sql)) {
        $mensaje = "Configuración actualizada correctamente";
        // Recargar datos actualizados
        $config['nombre'] = $nombre;
        $config['telefono'] = $telefono;
        $config['direccion'] = $direccion;
        $config['ruc'] = $ruc;
    } else {
        $error = "Error al actualizar la configuración: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Empresa - El Barón</title>
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
                <h1>Configuración de la Empresa</h1>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <h2>Datos del Local</h2>
                <p style="margin-bottom: 20px; color: var(--text-light);">Esta información se mostrará en los comprobantes y etiquetas generadas por el sistema.</p>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nombre">Nombre de la Empresa o Local *</label>
                        <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($config['nombre']); ?>" class="form-control">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ruc">RUC / Cédula (Opcional)</label>
                            <input type="text" id="ruc" name="ruc" value="<?php echo htmlspecialchars($config['ruc'] ?? ''); ?>" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono / WhatsApp (Opcional)</label>
                            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($config['telefono']); ?>" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección del Local *</label>
                        <textarea id="direccion" name="direccion" required class="form-control"><?php echo htmlspecialchars($config['direccion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="guardar_config" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
</body>
</html>
