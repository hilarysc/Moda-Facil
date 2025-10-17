<?php
session_start();
require_once './db/conectiondb.php';

// Verificar que el usuario esté logueado
if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$error_mensaje = '';
$pago_exitoso = false;
$numero_pedido = '';

// Obtener datos del usuario
try {
    $stmt = $pdo->prepare("SELECT email, nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
} catch(PDOException $e) {
    die('Error al obtener datos del usuario');
}

// Obtener tarjetas guardadas del usuario
$tarjetas_guardadas = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, ultimos_digitos, nombre_titular, tipo_tarjeta 
        FROM tarjetas_guardadas 
        WHERE usuario_id = ? AND activa = 1
        ORDER BY fecha_creacion DESC
    ");
    $stmt->execute([$usuario_id]);
    $tarjetas_guardadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Tabla puede no existir aún
    $tarjetas_guardadas = [];
}

// Obtener carrito del usuario
try {
    $stmt = $pdo->prepare("
        SELECT c.id as carrito_id, c.cantidad,
               p.id, p.precio, p.stock, p.nombre, p.es_oferta, p.precio_oferta
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id = ? AND p.activo = 1
    ");
    $stmt->execute([$usuario_id]);
    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $items_carrito = [];
}

if(empty($items_carrito)) {
    header('Location: carrito.php');
    exit();
}

// Calcular totales
$subtotal = 0;
foreach($items_carrito as $item) {
    $precio = $item['es_oferta'] ? $item['precio_oferta'] : $item['precio'];
    $subtotal += $precio * $item['cantidad'];
}
$envio = $subtotal > 200 ? 0 : 20;
$total = $subtotal + $envio;

// Procesar el pago
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_pago'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $numero_tarjeta = preg_replace('/\s/', '', $_POST['numero_tarjeta'] ?? '');
    $nombre_tarjeta = trim($_POST['nombre_tarjeta'] ?? '');
    $fecha_expiracion = $_POST['fecha_expiracion'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $guardar_tarjeta = isset($_POST['guardar_tarjeta']) ? 1 : 0;
    $usar_tarjeta_guardada = $_POST['usar_tarjeta_guardada'] ?? '';
    
    // Validaciones básicas
    if(empty($email) || empty($password)) {
        $error_mensaje = 'Por favor ingresa tu email y contraseña';
    } else {
        // Verificar contraseña
        $stmt_user = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
        $stmt_user->execute([$usuario_id]);
        $user_data = $stmt_user->fetch();
        
        if(!password_verify($password, $user_data['password'])) {
            $error_mensaje = 'Contraseña incorrecta';
        } else if(empty($numero_tarjeta) || empty($nombre_tarjeta) || empty($fecha_expiracion) || empty($cvv)) {
            $error_mensaje = 'Por favor completa todos los datos de la tarjeta';
        } else {
            // Validar que haya stock disponible
            $stock_disponible = true;
            foreach($items_carrito as $item) {
                if($item['stock'] < $item['cantidad']) {
                    $stock_disponible = false;
                    $error_mensaje = 'Stock insuficiente para ' . $item['nombre'];
                    break;
                }
            }
            
            if($stock_disponible) {
                try {
                    // Iniciar transacción
                    $pdo->beginTransaction();
                    
                    // Generar número de pedido
                    $numero_pedido = 'MF-' . date('Y') . '-' . str_pad(mt_rand(10000, 99999), 5, '0', STR_PAD_LEFT);
                    
                    // Crear pedido
                    $stmt = $pdo->prepare("
                        INSERT INTO pedidos (numero_pedido, usuario_id, total, estado, metodo_pago, direccion_envio)
                        VALUES (?, ?, ?, 'pendiente', 'Tarjeta de Crédito', ?)
                    ");
                    $stmt->execute([$numero_pedido, $usuario_id, $total, 'Pedido procesado en línea']);
                    $pedido_id = $pdo->lastInsertId();
                    
                    // Registrar detalles del pedido y restar stock
                    foreach($items_carrito as $item) {
                        $precio_venta = $item['es_oferta'] ? $item['precio_oferta'] : $item['precio'];
                        $subtotal_item = $precio_venta * $item['cantidad'];
                        
                        // Insertar detalle del pedido
                        $stmt = $pdo->prepare("
                            INSERT INTO pedido_detalles (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$pedido_id, $item['id'], $item['cantidad'], $precio_venta, $subtotal_item]);
                        
                        // Restar del stock
                        $stmt = $pdo->prepare("
                            UPDATE productos 
                            SET stock = stock - ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$item['cantidad'], $item['id']]);
                    }
                    
                    // Guardar tarjeta si lo solicita
                    if($guardar_tarjeta) {
                        $ultimos_digitos = substr($numero_tarjeta, -4);
                        $stmt = $pdo->prepare("
                            INSERT INTO tarjetas_guardadas (usuario_id, numero_tarjeta_encriptado, ultimos_digitos, nombre_titular, tipo_tarjeta, activa)
                            VALUES (?, ?, ?, ?, 'Crédito', 1)
                        ");
                        // En producción, encriptar el número de tarjeta
                        $tarjeta_encriptada = password_hash($numero_tarjeta, PASSWORD_DEFAULT);
                        $stmt->execute([$usuario_id, $tarjeta_encriptada, $ultimos_digitos, $nombre_tarjeta]);
                    }
                    
                    // Vaciar carrito
                    $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                    $stmt->execute([$usuario_id]);
                    
                    // Registrar actividad
                    registrarActividad($pdo, 'venta', "Pedido procesado: $numero_pedido - Total: \$$total", $usuario_id, $pedido_id);
                    
                    // Confirmar transacción
                    $pdo->commit();
                    
                    // Enviar email de confirmación
                    $asunto = "Confirmación de Pedido - Moda Fácil";
                    $mensaje_html = generarEmailConfirmacion($numero_pedido, $usuario['nombre'], $items_carrito, $subtotal, $envio, $total);
                    
                    if(enviarEmail($usuario['email'], $asunto, $mensaje_html)) {
                        registrarActividad($pdo, 'venta', "Email de confirmación enviado a: " . $usuario['email'], $usuario_id, $pedido_id);
                    }
                    
                    $pago_exitoso = true;
                    $_SESSION['ultimo_pedido'] = $numero_pedido;
                    
                } catch(PDOException $e) {
                    $pdo->rollBack();
                    error_log("Error al procesar pago: " . $e->getMessage());
                    $error_mensaje = 'Error al procesar el pago. Intenta nuevamente.';
                }
            }
        }
    }
}

// Función para enviar email
function enviarEmail($destinatario, $asunto, $mensaje_html) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@modafacil.com" . "\r\n";
    
    return mail($destinatario, $asunto, $mensaje_html, $headers);
}

// Función para generar email de confirmación
function generarEmailConfirmacion($numero_pedido, $nombre_cliente, $items, $subtotal, $envio, $total) {
    $detalles_productos = '';
    foreach($items as $item) {
        $precio = $item['es_oferta'] ? $item['precio_oferta'] : $item['precio'];
        $detalles_productos .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['nombre']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['cantidad']}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>\${$precio}</td>
                <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>\$" . number_format($precio * $item['cantidad'], 2) . "</td>
            </tr>
        ";
    }
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
            .header { background: linear-gradient(135deg, #000 0%, #2c2c2c 100%); color: white; padding: 20px; text-align: center; border-radius: 5px; margin-bottom: 30px; }
            .header h1 { margin: 0; font-size: 2em; }
            .section-title { background-color: #f9f9f9; padding: 15px; margin: 20px 0 10px 0; border-left: 4px solid #c9a961; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .total-section { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .total-row { display: flex; justify-content: space-between; padding: 10px 0; }
            .total-final { font-size: 1.5em; font-weight: bold; color: #000; border-top: 2px solid #000; padding-top: 10px; }
            .footer { background-color: #f5f5f5; padding: 20px; text-align: center; margin-top: 30px; border-radius: 5px; color: #666; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Pedido Confirmado!</h1>
                <p>Tu compra ha sido procesada exitosamente</p>
            </div>
            
            <p>Hola <strong>$nombre_cliente</strong>,</p>
            <p>Gracias por tu compra en Moda Fácil. Tu pedido ha sido confirmado y está siendo preparado.</p>
            
            <div class='section-title'>Número de Orden</div>
            <p style='font-size: 1.2em; color: #c9a961;'><strong>$numero_pedido</strong></p>
            
            <div class='section-title'>Productos Pedidos</div>
            <table>
                <thead>
                    <tr style='background-color: #f9f9f9;'>
                        <th style='padding: 10px; text-align: left;'>Producto</th>
                        <th style='padding: 10px; text-align: center;'>Cantidad</th>
                        <th style='padding: 10px; text-align: right;'>Precio</th>
                        <th style='padding: 10px; text-align: right;'>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    $detalles_productos
                </tbody>
            </table>
            
            <div class='total-section'>
                <div class='total-row'>
                    <span>Subtotal:</span>
                    <span>\$" . number_format($subtotal, 2) . "</span>
                </div>
                <div class='total-row'>
                    <span>Envío:</span>
                    <span>" . ($envio == 0 ? 'GRATIS' : '\$' . number_format($envio, 2)) . "</span>
                </div>
                <div class='total-row total-final'>
                    <span>TOTAL:</span>
                    <span>\$" . number_format($total, 2) . "</span>
                </div>
            </div>
            
            <div class='section-title'>Información de Entrega</div>
            <p>
                <strong>Tiempo de entrega estimado:</strong> 2-3 días hábiles<br>
                <strong>Método de envío:</strong> Envío a domicilio<br>
                <strong>Tracking:</strong> Te enviaremos un email con el número de rastreo pronto
            </p>
            
            <div class='section-title'>¿Necesitas Ayuda?</div>
            <p>
                Si tienes alguna pregunta sobre tu pedido, no dudes en contactarnos:<br>
                <strong>Email:</strong> soporte@modafacil.com<br>
                <strong>Teléfono:</strong> +1 (809) 555-0123<br>
                <strong>Horario:</strong> Lunes a Viernes, 9:00 AM - 5:00 PM
            </p>
            
            <div class='footer'>
                <p>&copy; 2025 Moda Fácil. Todos los derechos reservados.</p>
                <p>Este es un email automático, por favor no responda directamente.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return $html;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .payment-header {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .payment-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .payment-section {
            padding: 60px 0;
            background-color: #f5f5f5;
        }
        
        .payment-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .payment-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .payment-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #000;
        }
        
        .saved-cards-section {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #4caf50;
        }
        
        .saved-cards-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #000;
        }
        
        .saved-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .saved-card:hover {
            border-color: #c9a961;
            box-shadow: 0 3px 10px rgba(201, 169, 97, 0.2);
        }
        
        .saved-card input[type="radio"] {
            margin-right: 10px;
        }
        
        .or-divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .or-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }
        
        .or-divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #999;
            font-weight: 600;
        }
        
        .payment-section-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            color: #000;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #1a237e;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background-color: #e3f2fd;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            border-left: 5px solid #1a237e;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1.1rem;
        }
        
        .summary-row.total {
            font-size: 1.8rem;
            font-weight: 700;
            padding-top: 20px;
            border-top: 2px solid #000;
            margin-top: 15px;
            color: #1a237e;
        }
        
        .btn-pay {
            width: 100%;
            padding: 20px;
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 20px rgba(26, 35, 126, 0.3);
        }
        
        .btn-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(26, 35, 126, 0.4);
        }
        
        .error-message {
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }
        
        .info-note {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 0.9rem;
            color: #1565c0;
        }
        
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .success-modal.active {
            display: flex;
        }
        
        .success-content {
            background: white;
            padding: 60px;
            border-radius: 20px;
            text-align: center;
            max-width: 550px;
            animation: zoomIn 0.5s ease;
        }
        
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .success-checkmark {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            margin: 0 auto 30px;
        }
        
        .success-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #000;
        }
        
        .success-details {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            text-align: left;
        }
        
        .success-details p {
            margin: 10px 0;
            font-size: 1rem;
            color: #333;
        }
        
        .btn-view-order-success {
            display: inline-block;
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-view-order-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(26, 35, 126, 0.4);
        }
        
        @media (max-width: 768px) {
            .payment-card {
                padding: 30px 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .success-content {
                padding: 40px 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <h1>MODA FÁCIL</h1>
                    <p class="tagline">Elegancia al alcance de todos</p>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="stock.php">Catálogo</a></li>
                        <li><a href="carrito.php">Carrito</a></li>
                        <?php if(isset($_SESSION['usuario'])): ?>
                            <li><a href="logout.php">Cerrar Sesión</a></li>
                            <li class="user-info"><?php echo htmlspecialchars($_SESSION['usuario']); ?></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Payment Header -->
    <section class="payment-header">
        <div class="container">
            <h1>Procesar Pago</h1>
            <p style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem;">Pago seguro y encriptado</p>
        </div>
    </section>

    <!-- Payment Section -->
    <section class="payment-section">
        <div class="payment-container">
            <div class="payment-card">
                <?php if($error_mensaje): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_mensaje); ?></div>
                <?php endif; ?>

                <h2>Información de Pago</h2>

                <form method="POST" action="procesar_pago.php" id="paymentForm">
                    <!-- Tarjetas Guardadas -->
                    <?php if(!empty($tarjetas_guardadas)): ?>
                    <div class="saved-cards-section">
                        <div class="saved-cards-title">💳 Tarjetas Guardadas</div>
                        <?php foreach($tarjetas_guardadas as $tarjeta): ?>
                        <div class="saved-card">
                            <input type="radio" name="usar_tarjeta_guardada" value="<?php echo $tarjeta['id']; ?>" id="tarjeta_<?php echo $tarjeta['id']; ?>">
                            <label for="tarjeta_<?php echo $tarjeta['id']; ?>" style="display: inline; margin: 0;">
                                <strong><?php echo htmlspecialchars($tarjeta['tipo_tarjeta']); ?></strong> terminada en 
                                <strong><?php echo htmlspecialchars($tarjeta['ultimos_digitos']); ?></strong> 
                                - <?php echo htmlspecialchars($tarjeta['nombre_titular']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="or-divider"><span>O</span></div>
                    <?php endif; ?>

                    <!-- Verificación de Usuario -->
                    <div class="payment-section-title">Verificación de Cuenta</div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo htmlspecialchars($usuario['email']); ?>"
                            placeholder="correo@ejemplo.com"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Contraseña de Confirmación *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                    </div>

                    <!-- Datos de Tarjeta Nueva -->
                    <div class="payment-section-title">Datos de la Tarjeta</div>

                    <div class="form-group">
                        <label for="numero_tarjeta">Número de Tarjeta *</label>
                        <input 
                            type="text" 
                            id="numero_tarjeta" 
                            name="numero_tarjeta" 
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="nombre_tarjeta">Nombre del Titular *</label>
                        <input 
                            type="text" 
                            id="nombre_tarjeta" 
                            name="nombre_tarjeta" 
                            placeholder="NOMBRE COMO APARECE EN LA TARJETA"
                            style="text-transform: uppercase;"
                            required
                        >
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_expiracion">Fecha de Expiración *</label>
                            <input 
                                type="text" 
                                id="fecha_expiracion" 
                                name="fecha_expiracion" 
                                placeholder="MM/AA"
                                maxlength="5"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="cvv">CVV *</label>
                            <input 
                                type="text" 
                                id="cvv" 
                                name="cvv" 
                                placeholder="123"
                                maxlength="4"
                                required
                            >
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="guardar_tarjeta" name="guardar_tarjeta" value="1">
                        <label for="guardar_tarjeta">Guardar esta tarjeta para próximas compras</label>
                    </div>

                    <div class="info-note">
                        <strong>Nota:</strong> Tu información de tarjeta está encriptada y protegida. Nunca compartiremos tus datos con terceros.
                    </div>

                    <!-- Resumen del Pedido -->
                    <div class="order-summary">
                        <h3 style="font-family: 'Playfair Display', serif; margin-bottom: 15px;">Resumen del Pedido</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Envío:</span>
                            <span><?php echo ($envio == 0 ? 'GRATIS' : ' . number_format($envio, 2)'); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total a Pagar:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <button type="submit" name="procesar_pago" class="btn-pay">
                        Pagar $<?php echo number_format($total, 2); ?>
                    </button>

                    <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem;">
                        Al hacer clic en "Pagar", aceptas nuestros <a href="#" style="color: #1a237e;">Términos y Condiciones</a>
                    </p>
                </form>
            </div>
        </div>
    </section>

   <!-- Success Modal -->
    <div class="success-modal <?php echo $pago_exitoso ? 'active' : ''; ?>" id="successModal">
        <div class="success-content">
            <div class="success-checkmark">✓</div>
            <h2>¡Pago Exitoso!</h2>
            <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">
                Tu pedido ha sido procesado correctamente
            </p>

            <div class="success-details">
                <p><strong>Número de Orden:</strong> <?php echo htmlspecialchars($numero_pedido); ?></p>
                <p><strong>Total Pagado:</strong> $<?php echo number_format($total, 2); ?></p>
                <p><strong>Email de Confirmación:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                <p><strong>Estado:</strong> <span style="color: #4caf50;">✓ Confirmado</span></p>
            </div>

            <p style="margin: 20px 0; color: #666;">
                <strong>Hemos enviado la confirmación a tu correo</strong><br>
                Revisa tu bandeja de entrada y carpeta de spam
            </p>

            <p style="color: #666;">
                <strong>Tiempo estimado de entrega:</strong> 2-3 días hábiles
            </p>

            <a href="clientes.php" class="btn-view-order-success">Ver Mis Pedidos</a>
            <br>
            <a href="index.php" style="display: inline-block; margin-top: 15px; color: #666; text-decoration: underline;">Volver al Inicio</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 Moda Fácil. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Verificar que los elementos existan antes de acceder a ellos
        const numeroTarjetaElement = document.getElementById('numero_tarjeta');
        const cvvElement = document.getElementById('cvv');
        const fechaExpiracionElement = document.getElementById('fecha_expiracion');
        const paymentFormElement = document.getElementById('paymentForm');

        // Formatear número de tarjeta
        if(numeroTarjetaElement) {
            numeroTarjetaElement.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s/g, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
        }

        // Solo números en CVV
        if(cvvElement) {
            cvvElement.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
        }

        // Formatear fecha de expiración
        if(fechaExpiracionElement) {
            fechaExpiracionElement.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2, 4);
                }
                e.target.value = value;
            });
        }

        // Validación del formulario
        if(paymentFormElement) {
            paymentFormElement.addEventListener('submit', function(e) {
                const numeroTarjeta = document.getElementById('numero_tarjeta').value.replace(/\s/g, '');
                const cvv = document.getElementById('cvv').value;
                const usarGuardada = document.querySelector('input[name="usar_tarjeta_guardada"]:checked');
                
                // Si no usa tarjeta guardada, validar los datos
                if(!usarGuardada) {
                    if(numeroTarjeta.length < 13 || numeroTarjeta.length > 19) {
                        e.preventDefault();
                        alert('Número de tarjeta inválido (13-19 dígitos)');
                        return false;
                    }
                    
                    if(cvv.length < 3 || cvv.length > 4) {
                        e.preventDefault();
                        alert('CVV inválido (3-4 dígitos)');
                        return false;
                    }
                }
            });
        }

        // Mostrar/ocultar campos de tarjeta si se selecciona una guardada
        const tarjetasRadios = document.querySelectorAll('input[name="usar_tarjeta_guardada"]');
        tarjetasRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if(numeroTarjetaElement) {
                    numeroTarjetaElement.style.opacity = '0.5';
                    numeroTarjetaElement.disabled = true;
                }
                if(document.getElementById('nombre_tarjeta')) {
                    document.getElementById('nombre_tarjeta').style.opacity = '0.5';
                    document.getElementById('nombre_tarjeta').disabled = true;
                }
                if(fechaExpiracionElement) {
                    fechaExpiracionElement.style.opacity = '0.5';
                    fechaExpiracionElement.disabled = true;
                }
                if(cvvElement) {
                    cvvElement.style.opacity = '0.5';
                    cvvElement.disabled = true;
                }
            });
        });

        // Si hace clic en un nuevo campo de tarjeta, habilitar campos
        if(numeroTarjetaElement) {
            numeroTarjetaElement.addEventListener('focus', function() {
                document.querySelectorAll('input[name="usar_tarjeta_guardada"]').forEach(r => r.checked = false);
                numeroTarjetaElement.style.opacity = '1';
                numeroTarjetaElement.disabled = false;
                if(document.getElementById('nombre_tarjeta')) {
                    document.getElementById('nombre_tarjeta').style.opacity = '1';
                    document.getElementById('nombre_tarjeta').disabled = false;
                }
                if(fechaExpiracionElement) {
                    fechaExpiracionElement.style.opacity = '1';
                    fechaExpiracionElement.disabled = false;
                }
                if(cvvElement) {
                    cvvElement.style.opacity = '1';
                    cvvElement.disabled = false;
                }
            });
        }
    </script>
</body>
</html>