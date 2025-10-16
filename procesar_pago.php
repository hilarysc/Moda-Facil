<?php
session_start();

// Verificar que el usuario esté logueado
if(!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Obtener datos del pedido desde la sesión
$total = $_SESSION['total_pedido'] ?? 0;
$subtotal = $_SESSION['subtotal_pedido'] ?? 0;
$envio = $_SESSION['envio_pedido'] ?? 20.00;

// Procesar el pago
$pago_exitoso = false;
$numero_pedido = '';
$error_mensaje = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['procesar_pago'])) {
    // Validar datos del formulario
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $numero_tarjeta = $_POST['numero_tarjeta'] ?? '';
    $nombre_tarjeta = $_POST['nombre_tarjeta'] ?? '';
    $fecha_expiracion = $_POST['fecha_expiracion'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    
    // Validaciones básicas
    if(empty($email) || empty($password)) {
        $error_mensaje = 'Por favor ingresa tu email y contraseña';
    } elseif(empty($numero_tarjeta) || empty($nombre_tarjeta) || empty($fecha_expiracion) || empty($cvv)) {
        $error_mensaje = 'Por favor completa todos los datos de la tarjeta';
    } else {
        // Aquí iría la validación con la base de datos y procesamiento real del pago
        // Por ahora simulamos un pago exitoso
        
        $numero_pedido = 'MF-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $_SESSION['ultimo_pedido'] = $numero_pedido;
        $_SESSION['email_notificacion'] = $email;
        
        // Aquí se enviaría el email con la información del pedido
        // mail($email, "Confirmación de Pedido", "Tu pedido ha sido procesado...");
        
        $pago_exitoso = true;
        
        // Limpiar carrito
        $_SESSION['carrito'] = [];
    }
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
            max-width: 800px;
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
            text-align: center;
            color: #000;
        }
        
        .security-banner {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 40px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .security-icon {
            font-size: 2rem;
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
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #1a237e;
            box-shadow: 0 0 0 4px rgba(26, 35, 126, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        
        .card-visual {
            background: linear-gradient(135deg, #1a237e 0%, #0d47a1 100%);
            padding: 30px;
            border-radius: 15px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            min-height: 200px;
            position: relative;
            overflow: hidden;
        }
        
        .card-visual::before {
            content: '💳';
            position: absolute;
            font-size: 8rem;
            opacity: 0.1;
            right: -20px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .card-chip {
            width: 50px;
            height: 40px;
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card-number {
            font-size: 1.5rem;
            letter-spacing: 4px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }
        
        .card-info {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .card-holder {
            font-size: 0.9rem;
            opacity: 0.8;
            text-transform: uppercase;
        }
        
        .card-holder-name {
            font-size: 1.1rem;
            margin-top: 5px;
            font-weight: 600;
        }
        
        .card-expiry {
            text-align: right;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
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
        
        .btn-pay:active {
            transform: translateY(-1px);
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
        
        .payment-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            opacity: 0.6;
        }
        
        .payment-icon {
            font-size: 2.5rem;
        }
        
        .info-note {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #1565c0;
        }
        
        /* Modal de Éxito */
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
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
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
            animation: bounce 1s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
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
        
        .success-details strong {
            color: #000;
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
                        <?php if(isset($_SESSION['usuario']) && $_SESSION['rol'] == 'administrador'): ?>
                            <li><a href="administrador.php">Administrador</a></li>
                        <?php elseif(isset($_SESSION['usuario']) && $_SESSION['rol'] == 'cliente'): ?>
                            <li><a href="clientes.php">Mi Cuenta</a></li>
                            <li><a href="stock.php">Catálogo</a></li>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['usuario'])): ?>
                            <li><a href="logout.php">Cerrar Sesión</a></li>
                            <li class="user-info">👤 <?php echo $_SESSION['usuario']; ?></li>
                        <?php else: ?>
                            <li><a href="login.php" class="btn-login">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Payment Header -->
    <section class="payment-header">
        <div class="container">
            <h1>💳 Procesar Pago</h1>
            <p style="font-family: 'Montserrat', sans-serif; font-size: 1.1rem;">Pago seguro y encriptado</p>
        </div>
    </section>

    <!-- Payment Section -->
    <section class="payment-section">
        <div class="payment-container">
            <div class="payment-card">
                <div class="security-banner">
                    <span class="security-icon">🔒</span>
                    <div>
                        <strong>Pago 100% Seguro</strong><br>
                        <small>Tu información está protegida con encriptación SSL</small>
                    </div>
                </div>

                <?php if($error_mensaje): ?>
                    <div class="error-message">
                        ⚠️ <?php echo $error_mensaje; ?>
                    </div>
                <?php endif; ?>

                <h2>Información de Pago</h2>

                <form method="POST" action="procesar_pago.php" id="paymentForm">
                    <!-- Verificación de Usuario -->
                    <div class="payment-section-title">
                        📧 Verificación de Cuenta
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
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

                    <div class="info-note">
                        💡 <strong>Nota:</strong> Enviaremos la confirmación de tu pedido al correo electrónico proporcionado. Asegúrate de que sea correcto.
                    </div>

                    <!-- Información de Tarjeta -->
                    <div class="payment-section-title">
                        💳 Datos de la Tarjeta
                    </div>

                    <!-- Visualización de Tarjeta -->
                    <div class="card-visual">
                        <div class="card-chip"></div>
                        <div class="card-number" id="cardDisplay">•••• •••• •••• ••••</div>
                        <div class="card-info">
                            <div>
                                <div class="card-holder">TITULAR</div>
                                <div class="card-holder-name" id="nameDisplay">NOMBRE APELLIDO</div>
                            </div>
                            <div class="card-expiry">
                                <div class="card-holder">VALIDA HASTA</div>
                                <div class="card-holder-name" id="expiryDisplay">MM/AA</div>
                            </div>
                        </div>
                    </div>

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

                    <div class="payment-icons">
                        <span class="payment-icon">💳</span>
                        <span class="payment-icon">🏦</span>
                        <span class="payment-icon">✓</span>
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
                            <span>$<?php echo number_format($envio, 2); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total a Pagar:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>

                    <button type="submit" name="procesar_pago" class="btn-pay">
                        🔒 Pagar $<?php echo number_format($total, 2); ?>
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
                <p><strong>Número de Orden:</strong> <?php echo $numero_pedido; ?></p>
                <p><strong>Total Pagado:</strong> $<?php echo number_format($total, 2); ?></p>
                <p><strong>Método de Pago:</strong> Tarjeta de Crédito</p>
                <p><strong>Estado:</strong> <span style="color: #4caf50;">Confirmado</span></p>
            </div>

            <p style="margin: 20px 0; color: #666;">
                📧 Hemos enviado la confirmación a:<br>
                <strong><?php echo isset($_SESSION['email_notificacion']) ? $_SESSION['email_notificacion'] : ''; ?></strong>
            </p>

            <p style="color: #666;">
                🚚 Tiempo estimado de entrega: <strong>2-3 días hábiles</strong>
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
                <p>&copy; 2025 Moda Fácil. Todos los derechos reservados. Desarrollado en 2025.</p>
            </div>
        </div>
    </footer>

    <script>
        // Formatear número de tarjeta
        document.getElementById('numero_tarjeta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Actualizar visualización
            document.getElementById('cardDisplay').textContent = formattedValue || '•••• •••• •••• ••••';
        });

        // Actualizar nombre en tarjeta
        document.getElementById('nombre_tarjeta').addEventListener('input', function(e) {
            document.getElementById('nameDisplay').textContent = e.target.value.toUpperCase() || 'NOMBRE APELLIDO';
        });

        // Formatear fecha de expiración
        document.getElementById('fecha_expiracion').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
            document.getElementById('expiryDisplay').textContent = value || 'MM/AA';
        });

        // Solo números en CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Validación del formulario
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const numeroTarjeta = document.getElementById('numero_tarjeta').value.replace(/\s/g, '');
            const cvv = document.getElementById('cvv').value;
            
            if(numeroTarjeta.length < 13 || numeroTarjeta.length > 19) {
                e.preventDefault();
                alert('Número de tarjeta inválido');
                return false;
            }
            
            if(cvv.length < 3 || cvv.length > 4) {
                e.preventDefault();
                alert('CVV inválido');
                return false;
            }
        });
    </script>
</body>
</html>