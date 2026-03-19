<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Verificar si ya hay sesión iniciada
if (isLoggedIn()) {
    redirect('/dashboard.php');
}

// Verificar si existe el archivo de autoload de Composer
if (!file_exists('vendor/autoload.php')) {
    die('Error: No se encuentra vendor/autoload.php. Ejecute "composer require google/apiclient:"^2.0"" en la terminal');
}

require_once 'vendor/autoload.php';

// 🔑 TUS CREDENCIALES DE GOOGLE (ACTUALIZADAS)
$client_id = GOOGLE_CLIENT_ID;
$client_secret = GOOGLE_CLIENT_SECRET;
$redirect_uri = SITE_URL . '/login_google.php';

// Crear cliente de Google
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("email");
$client->addScope("profile");
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Si viene con código de autorización de Google
if (isset($_GET['code'])) {
    try {
        // Intercambiar código por token de acceso
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Verificar si hay error en el token
        if (isset($token['error'])) {
            throw new Exception($token['error_description'] ?? 'Error al obtener token');
        }
        
        $client->setAccessToken($token);
        
        // Obtener información del usuario
        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();
        
        // Verificar si el usuario ya existe en la base de datos
        $email = $conn->real_escape_string($userInfo->email);
        $google_id = $conn->real_escape_string($userInfo->id);
        $nombre = $conn->real_escape_string($userInfo->name);
        $avatar = $conn->real_escape_string($userInfo->picture);
        
        // Buscar usuario por email o google_id
        $sql = "SELECT * FROM usuarios WHERE email = '$email' OR google_id = '$google_id'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            // Usuario existe - actualizar google_id si es necesario
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            
            if (empty($user['google_id'])) {
                $conn->query("UPDATE usuarios SET google_id = '$google_id', avatar = '$avatar' WHERE id = $user_id");
            }
        } else {
            // Nuevo usuario - crear cuenta
            $sql = "INSERT INTO usuarios (nombre, email, google_id, avatar) 
                    VALUES ('$nombre', '$email', '$google_id', '$avatar')";
            if ($conn->query($sql)) {
                $user_id = $conn->insert_id;
            } else {
                throw new Exception("Error al crear usuario: " . $conn->error);
            }
        }
        
        // Iniciar sesión
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_avatar'] = $avatar;
        
        // Redirigir al dashboard
        header("Location: dashboard.php");
        exit;
        
    } catch (Exception $e) {
        $error = "Error al autenticar con Google: " . $e->getMessage();
        $_SESSION['login_error'] = $error;
        header("Location: login.php");
        exit;
    }
} else {
    // No hay código, redirigir a Google para autenticación
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit;
}
?>