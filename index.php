<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Barón - Servicio Técnico</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .landing-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .landing-container {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }
        
        .landing-info {
            color: white;
        }
        
        .landing-info h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .landing-info p {
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .landing-features {
            list-style: none;
            margin-bottom: 40px;
        }
        
        .landing-features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .landing-features i {
            font-size: 24px;
            color: var(--accent);
        }
        
        .landing-buttons {
            display: flex;
            gap: 20px;
        }
        
        .btn-landing {
            padding: 15px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s;
        }
        
        .btn-landing:hover {
            transform: translateY(-3px);
        }
        
        .btn-landing-primary {
            background: var(--accent);
            color: white;
        }
        
        .btn-landing-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .landing-image {
            text-align: center;
        }
        
        .landing-image img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .versiculo-destacado {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            font-style: italic;
            border-left: 4px solid var(--accent);
        }
        
        @media (max-width: 768px) {
            .landing-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .landing-features li {
                justify-content: center;
            }
            
            .landing-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="landing-page">
        <div class="landing-container">
            <div class="landing-info">
                <h1>El Barón</h1>
                <p>Sistema de Gestión para Servicio Técnico Especializado</p>
                
                <ul class="landing-features">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Control de reparaciones y mantenimientos
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Gestión de inventario de repuestos
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Stickers y comprobantes personalizados
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Reportes detallados de ganancias
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        Compartir por WhatsApp
                    </li>
                </ul>
                
                <div class="landing-buttons">
                    <a href="login.php" class="btn-landing btn-landing-primary">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </a>
                    <a href="register.php" class="btn-landing btn-landing-secondary">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                </div>
                
                <div class="versiculo-destacado">
                    <i class="fas fa-bible"></i>
                    "Filipenses 4:13 - Todo lo puedo en Cristo que me fortalece"
                </div>
            </div>
            
            <div class="landing-image">
                <img src="assets/img/logo.png" alt="Logo El Barón" style="max-width: 300px;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%232c3e50%22/><text x=%2220%22 y=%2265%22 fill=%22%23e67e22%22 font-size=%2250%22 font-weight=%22bold%22>EB</text></svg>'">
                <div style="margin-top: 30px; color: white;">
                    <h3>¡Bienvenido a tu sistema de gestión!</h3>
                    <p>Organiza todas tus reparaciones y ventas en un solo lugar</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>