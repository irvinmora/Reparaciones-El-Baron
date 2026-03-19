<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($password != $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido";
    } else {
        // Verificar si el email ya existe
        $check = $conn->query("SELECT id FROM usuarios WHERE email = '$email'");
        if ($check && $check->num_rows > 0) {
            $error = "El email ya está registrado";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre, email, password) VALUES ('$nombre', '$email', '$password_hash')";
            
            if ($conn->query($sql)) {
                $success = "Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $error = "Error al registrar: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - El Barón</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="assets/img/logo.png" alt="Logo El Barón" class="logo" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%232c3e50%22/><text x=%2220%22 y=%2265%22 fill=%22%23e67e22%22 font-size=%2250%22 font-weight=%22bold%22>EB</text></svg>'">
                <h1>Crear Cuenta</h1>
                <p>Regístrate en El Barón</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" class="btn-login">Ir a Iniciar Sesión</a>
                </div>
            <?php else: ?>
            
            <form method="POST" action="" class="login-form" autocomplete="off">
                <div class="form-group">
                    <label for="nombre"><i class="fas fa-user"></i> Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ingresa tu nombre" value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" oninput="this.value = this.value.toUpperCase()">
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                        <span class="toggle-password" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Confirmar Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repite tu contraseña" autocomplete="new-password">
                        <span class="toggle-password" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-user-plus"></i> Registrarse
                </button>
            </form>
            
            <div class="login-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia Sesión aquí</a></p>
                <div class="divider"><span>o</span></div>
                <a href="login_google.php" class="btn-google">
                    <i class="fab fa-google"></i> Registrarse con Google
                </a>
                <p style="margin-top: 15px;">
                    <a href="index.php"><i class="fas fa-home"></i> Volver al inicio</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html> 