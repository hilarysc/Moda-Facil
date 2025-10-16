<?php
require_once './db/conectiondb.php';

// Si ya hay sesión activa, redirigir según el rol
if(isset($_SESSION['usuario_id'])) {
    if($_SESSION['rol'] == 'administrador') {
        header('Location: administrador.php');
    } else {
        header('Location: clientes.php');
    }
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validación
    if(empty($email) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } else {
        try {
            // Buscar usuario en la base de datos
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar si existe el usuario y la contraseña es correcta
            if($usuario && password_verify($password, $usuario['password'])) {
                // Login exitoso - Guardar datos en sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario'] = $usuario['email'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                
                // Registrar actividad
                registrarActividad($pdo, 'registro', "Inicio de sesión: " . $usuario['nombre'], $usuario['id']);
                
                // Redirigir según rol
                if($usuario['rol'] == 'administrador') {
                    header('Location: administrador.php');
                } else {
                    header('Location: clientes.php');
                }
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } catch(PDOException $e) {
            $error = 'Error al iniciar sesión. Por favor intente nuevamente.';
            error_log("Error en login: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Moda Fácil</title>
    <link rel="stylesheet" href="./login.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>MODA FÁCIL</h2>
            <p class="login-subtitle">Acceso al Sistema</p>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="usuario">Usuario (Email)</label>
                    <input type="email" id="usuario" name="usuario" placeholder="correo@ejemplo.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
                
                <button type="submit" class="btn-submit">Iniciar Sesión</button>
            </form>
            
            <div class="demo-credentials">
                <h3>🔑 Credenciales de Prueba</h3>
                <p><strong>Administrador:</strong></p>
                <p>📧 admin@modafacil.com</p>
                <p>🔒 admin123</p>
                <br>
                <p><strong>Cliente:</strong></p>
                <p>📧 juan@example.com</p>
                <p>🔒 cliente123</p>
            </div>
                        
            <div class="back-home">
                <a href="index.php">← Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>