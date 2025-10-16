<?php
session_start();

// Si ya hay sesión activa, redirigir según el rol
if(isset($_SESSION['usuario'])) {
    if($_SESSION['rol'] == 'administrador') {
        header('Location: administrador.php');
    } else {
        header('Location: clientes.php');
    }
    exit();
}
// klklklkllkklkl

$error = '';

// Usuarios de ejemplo (en producción esto debería estar en una base de datos)
$usuarios = [
    'admin@gmail.com' => ['password' => 'admin123', 'rol' => 'administrador', 'nombre' => 'Administrador'],
    'hilarypere3@gmail.com' => ['password' => 'rosa@121', 'rol' => 'cliente', 'nombre' => 'Cliente']
];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validación
    if(empty($usuario) || empty($password)) {
        $error = 'Por favor complete todos los campos';
    } elseif(isset($usuarios[$usuario]) && $usuarios[$usuario]['password'] == $password) {
        // Login exitoso
        $_SESSION['usuario'] = $usuario;
        $_SESSION['rol'] = $usuarios[$usuario]['rol'];
        $_SESSION['nombre'] = $usuarios[$usuario]['nombre'];
        
        // Redirigir según rol
        if($_SESSION['rol'] == 'administrador') {
            header('Location: administrador.php');
        } else {
            header('Location: clientes.php');
        }
        exit();
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #000000 0%, #2c2c2c 100%);
            padding: 20px;
        }
        
        .login-box {
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 450px;
        }
        
        .login-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 10px;
            color: #000;
        }
        
        .login-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #c9a961;
            box-shadow: 0 0 0 3px rgba(201, 169, 97, 0.1);
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background-color: #c9a961;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .error-message {
            background-color: #ff4444;
            color: white;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .demo-credentials {
            margin-top: 30px;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
            border-left: 4px solid #c9a961;
        }
        
        .demo-credentials h3 {
            font-size: 1rem;
            margin-bottom: 15px;
            color: #000;
            font-weight: 600;
        }
        
        .demo-credentials p {
            font-size: 0.9rem;
            margin: 8px 0;
            color: #666;
        }
        
        .demo-credentials strong {
            color: #000;
        }
        
        .back-home {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-home a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .back-home a:hover {
            color: #c9a961;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>MODA FÁCIL</h2>
            <p class="login-subtitle">Acceso al Sistema</p>
            
            <?php if($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-submit">Iniciar Sesión</button>
            </form>
                        
            <div class="back-home">
                <a href="index.php">← Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>