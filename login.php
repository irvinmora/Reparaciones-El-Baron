<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$error = '';

// 👇 MANEJO DE ERRORES DE GOOGLE
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
// 👆

if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error = "Sesión expirada por inactividad. Por favor, vuelva a iniciar sesión.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['rol'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - El Barón</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-body {
            background: linear-gradient(135deg, #2c3e50, #e67e22);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        .login-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header .logo {
            width: 100px;
            height: auto;
            margin-bottom: 15px;
        }
        .login-header h1 {
            color: #2c3e50;
            margin: 0 0 5px 0;
            font-size: 28px;
        }
        .login-header p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        .form-group i {
            margin-right: 8px;
            color: #e67e22;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.1);
        }
        .btn {
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            width: 100%;
        }
        .btn-primary {
            background: #2c3e50;
            color: white;
        }
        .btn-primary:hover {
            background: #1e2b37;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            color: #555;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .btn-google:hover {
            background: #f8f9fa;
            border-color: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .divider {
            text-align: center;
            margin: 30px 0;
            border-top: 1px solid #eee;
            position: relative;
        }
        .divider span {
            position: absolute;
            top: -10px;
            background: white;
            padding: 0 15px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 13px;
            color: #888;
        }
        .login-footer {
            margin-top: 25px;
        }
        .login-footer p {
            text-align: center;
            margin: 10px 0;
        }
        .login-footer a {
            text-decoration: none;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="assets/img/logo.png" alt="Logo El Barón" class="logo" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%232c3e50%22/><text x=%2220%22 y=%2265%22 fill=%22%23e67e22%22 font-size=%2250%22 font-weight=%22bold%22>EB</text></svg>'">
                <h1>El Barón</h1>
                <p>Servicio Técnico Especializado</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form" autocomplete="off">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com" class="form-control" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña" autocomplete="new-password">
                        <span class="toggle-password" title="Mostrar/Ocultar"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="login-footer">
                <div class="divider">
                    <span>O</span>
                </div>
                
                <a href="login_google.php" class="btn-google">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20" height="20"> 
                    Continuar con Google
                </a>
                
                <p>¿No tienes cuenta? <a href="register.php" style="color: #e67e22; font-weight: 600;">Regístrate</a></p>
                <p>
                    <a href="index.php" style="color: #999; font-size: 14px;"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
                </p>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>