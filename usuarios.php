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

$action = $_GET['action'] ?? 'lista';
$mensaje = '';
$error = '';

// Procesar formulario de nuevo usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar_usuario'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    
    // Validar email
    $check = $conn->query("SELECT id FROM usuarios WHERE email = '$email'");
    if ($check->num_rows > 0) {
        $error = "El email ya está registrado";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$hashed_password', '$rol')";
        
        if ($conn->query($sql)) {
            $mensaje = "Usuario registrado correctamente";
            $action = 'lista';
        } else {
            $error = "Error al registrar el usuario: " . $conn->error;
        }
    }
}

// Procesar actualización de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_usuario'])) {
    $id = (int)$_POST['id'];
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $rol = $_POST['rol'];
    $password = $_POST['password'];
    
    // Verificar duplicado de email
    $check = $conn->query("SELECT id FROM usuarios WHERE email = '$email' AND id != $id");
    if ($check->num_rows > 0) {
        $error = "El email ya está registrado para otro usuario";
    } else {
        $sql = "UPDATE usuarios SET nombre = '$nombre', email = '$email', rol = '$rol' WHERE id = $id";
        
        if ($conn->query($sql)) {
            // Si se proporcionó una nueva contraseña, actualizarla
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $conn->query("UPDATE usuarios SET password = '$hashed_password' WHERE id = $id");
            }
            $mensaje = "Usuario actualizado correctamente";
            $action = 'lista';
        } else {
            $error = "Error al actualizar: " . $conn->error;
        }
    }
}

// Eliminar usuario
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // No permitir que el usuario se elimine a sí mismo
    if ($id == $_SESSION['user_id']) {
        $error = "No puedes eliminar tu propio usuario";
    } else {
        if ($conn->query("DELETE FROM usuarios WHERE id = $id")) {
            $mensaje = "Usuario eliminado correctamente";
        } else {
            $error = "Error al eliminar";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - El Barón</title>
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
                <h1>Gestión de Usuarios</h1>
                <?php if ($action == 'lista'): ?>
                <a href="?action=nuevo" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </a>
                <?php endif; ?>
            </header>
            
            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($action == 'nuevo'): ?>
                <div class="form-container">
                    <h2>Registrar Nuevo Usuario</h2>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo *</label>
                                <input type="text" id="nombre" name="nombre" required placeholder="Ej: Carlos Técnico">
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Contraseña *</label>
                                <div class="password-field">
                                    <input type="password" id="password" name="password" required minlength="6">
                                    <span class="toggle-password" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rol">Rol del Usuario *</label>
                                <select id="rol" name="rol" required>
                                    <option value="tecnico">Técnico</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="guardar_usuario" class="btn btn-success">
                                <i class="fas fa-save"></i> Guardar Usuario
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php elseif ($action == 'editar' && isset($_GET['id'])): ?>
                <?php
                $id = (int)$_GET['id'];
                $u = $conn->query("SELECT * FROM usuarios WHERE id = $id")->fetch_assoc();
                ?>
                <div class="form-container">
                    <h2>Editar Usuario</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" required value="<?php echo $u['nombre']; ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required value="<?php echo $u['email']; ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Nueva Contraseña (dejar en blanco para mantener actual)</label>
                                <div class="password-field">
                                    <input type="password" id="password" name="password" minlength="6">
                                    <span class="toggle-password" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="rol">Rol del Usuario</label>
                                <select id="rol" name="rol" required>
                                    <option value="tecnico" <?php echo $u['rol'] == 'tecnico' ? 'selected' : ''; ?>>Técnico</option>
                                    <option value="administrador" <?php echo $u['rol'] == 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="actualizar_usuario" class="btn btn-success">
                                <i class="fas fa-save"></i> Actualizar Usuario
                            </button>
                            <a href="?action=lista" class="btn">Cancelar</a>
                        </div>
                    </form>
                </div>
                
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $usuarios = $conn->query("SELECT * FROM usuarios ORDER BY id ASC");
                        while ($row = $usuarios->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nombre']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <span class="badge <?php echo $row['rol'] == 'administrador' ? 'completado' : 'pendiente'; ?>">
                                    <?php echo ucfirst($row['rol']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha_registro'])); ?></td>
                            <td>
                                <a href="?action=editar&id=<?php echo $row['id']; ?>" class="btn-small" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                <a href="?delete=<?php echo $row['id']; ?>" class="btn-small btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <div class="sidebar-overlay"></div>
    <script src="assets/js/main.js"></script>
</body>
</html>
