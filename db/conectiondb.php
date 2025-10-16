<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constanste para la conexion
define('DB_HOST', 'localhost');
define('DB_NAME', 'tienda_juan');
define('DB_USER', 'root');
define('DB_PASS', '');

// try catch para la conexion
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Error de Conexión</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                }
                .error-container {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
                    max-width: 600px;
                    border-left: 5px solid #dc3545;
                }
                h1 {
                    color: #dc3545;
                    margin-bottom: 20px;
                }
                .error-message {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                    color: #721c24;
                }
                ul {
                    line-height: 2;
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>❌ Error de Conexión a la Base de Datos</h1>
                <div class='error-message'>
                    <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
                </div>
                <h3>Verifica lo siguiente:</h3>
                <ul>
                    <li>✅ El servidor MySQL está corriendo (XAMPP/WAMP)</li>
                    <li>✅ La base de datos <strong>'tienda_juan'</strong> existe</li>
                    <li>✅ Las credenciales de conexión son correctas</li>
                    <li>✅ El usuario 'root' tiene permisos</li>
                </ul>
                <p><strong>Base de datos esperada:</strong> tienda_juan</p>
                <p><strong>Host:</strong> localhost</p>
                <p><strong>Usuario:</strong> root</p>
            </div>
        </body>
        </html>
    ");
}

// Función para verificar si el usuario está logueado
function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Función para verificar si el usuario es administrador
function verificarAdmin() {
    verificarSesion();
    if ($_SESSION['rol'] !== 'administrador') {
        header('Location: index.php');
        exit();
    }
}

// Función para verificar si el usuario es cliente
function verificarCliente() {
    verificarSesion();
    if ($_SESSION['rol'] !== 'cliente') {
        header('Location: index.php');
        exit();
    }
}

// Función para registrar actividad
function registrarActividad($pdo, $tipo, $descripcion, $usuario_id = null, $referencia_id = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO actividad (tipo, descripcion, usuario_id, referencia_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$tipo, $descripcion, $usuario_id, $referencia_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

// Función para formatear fecha
function formatearFecha($fecha) {
    $timestamp = strtotime($fecha);
    return date('d/m/Y H:i', $timestamp);
}

// Función para formatear precio
function formatearPrecio($precio) {
    return '$' . number_format($precio, 2);
}

// Función para calcular tiempo transcurrido
function tiempoTranscurrido($fecha) {
    $tiempo = time() - strtotime($fecha);
    if($tiempo < 60) return "Hace " . $tiempo . " segundos";
    elseif($tiempo < 3600) return "Hace " . floor($tiempo/60) . " minutos";
    elseif($tiempo < 86400) return "Hace " . floor($tiempo/3600) . " horas";
    else return "Hace " . floor($tiempo/86400) . " días";
}

// Función para limpiar entrada de datos
function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}
?>