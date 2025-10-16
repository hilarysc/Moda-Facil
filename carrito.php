<?php
session_start();

// Inicializar carrito si no existe
if(!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar producto al carrito
if(isset($_POST['agregar_carrito'])) {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $imagen = $_POST['imagen'];
    $cantidad = $_POST['cantidad'] ?? 1;
    
    // Verificar si el producto ya está en el carrito
    $encontrado = false;
    foreach($_SESSION['carrito'] as &$item) {
        if($item['id'] == $id_producto) {
            $item['cantidad'] += $cantidad;
            $encontrado = true;
            break;
        }
    }
    
    if(!$encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $id_producto,
            'nombre' => $nombre,
            'precio' => $precio,
            'imagen' => $imagen,
            'cantidad' => $cantidad
        ];
    }
    
    header('Location: carrito.php');
    exit();
}

// Actualizar cantidad
if(isset($_POST['actualizar_cantidad'])) {
    $id_producto = $_POST['id_producto'];
    $nueva_cantidad = $_POST['cantidad'];
    
    foreach($_SESSION['carrito'] as &$item) {
        if($item['id'] == $id_producto) {
            $item['cantidad'] = max(1, $nueva_cantidad);
            break;
        }
    }
    
    header('Location: carrito.php');
    exit();
}

// Eliminar producto del carrito
if(isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $_SESSION['carrito'] = array_filter($_SESSION['carrito'], function($item) use ($id_eliminar) {
        return $item['id'] != $id_eliminar;
    });
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    
    header('Location: carrito.php');
    exit();
}

// Vaciar carrito
if(isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = [];
    header('Location: carrito.php');
    exit();
}

// Calcular totales
$subtotal = 0;
foreach($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = $subtotal > 0 ? 20.00 : 0;
$total = $subtotal + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .cart-header {
            background: linear-gradient(135deg, #000 0%, #2c2c2c 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .cart-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .cart-section {
            padding: 60px 0;
        }
        
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        .cart-items {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 30px;
        }
        
        .cart-items h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c9a961;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        
        .cart-item-details h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #000;
        }
        
        .cart-item-price {
            font-size: 1.1rem;
            color: #c9a961;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-control button {
            width: 35px;
            height: 35px;
            border: 2px solid #000;
            background: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .quantity-control button:hover {
            background: #000;
            color: white;
        }
        
        .quantity-control input {
            width: 60px;
            text-align: center;
            border: 2px solid #e0e0e0;
            padding: 8px;
            font-weight: 600;
        }
        
        .cart-item-actions {
            text-align: right;
        }
        
        .cart-item-subtotal {
            font-size: 1.3rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 15px;
        }
        
        .btn-remove {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-remove:hover {
            background-color: #d32f2f;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-cart h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #666;
        }
        
        .cart-summary {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .cart-summary h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c9a961;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 1.1rem;
        }
        
        .summary-row.total {
            font-size: 1.5rem;
            font-weight: 700;
            padding-top: 20px;
            border-top: 2px solid #000;
            margin-top: 15px;
            color: #000;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #c9a961 0%, #8b7139 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(201, 169, 97, 0.4);
        }
        
        .btn-continue {
            width: 100%;
            padding: 15px;
            background: white;
            color: #000;
            border: 2px solid #000;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-continue:hover {
            background: #000;
            color: white;
        }
        
        .btn-clear-cart {
            color: #f44336;
            text-decoration: underline;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 15px;
            display: inline-block;
        }
        
        .shipping-info {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
        }
        
        .shipping-info p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: #2e7d32;
        }
        
        @media (max-width: 968px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: relative;
                top: 0;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
            }
            
            .cart-item-actions {
                grid-column: 1 / -1;
                text-align: left;
                margin-top: 15px;
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
                            <li><a href="inventario.php">Inventario</a></li>
                           
                                               <?php elseif(isset($_SESSION['usuario']) && $_SESSION['rol'] == 'cliente'): ?>
                            <li><a href="clientes.php">Mi Cuenta</a></li>
                            <li><a href="ofertas.php">Ofertas</a></li>
                            <li><a href="stock.php">Catálogo</a></li>
                        <?php else: ?>
                            <li><a href="ofertas.php">Ofertas</a></li>
                            <li><a href="stock.php">Catálogo</a></li>
                        <?php endif; ?>
                        
                        <li><a href="carrito.php"> Carrito (<?php echo count($_SESSION['carrito']); ?>)</a></li>
                        
                        <?php if(isset($_SESSION['usuario'])): ?>
                            <li><a href="logout.php">Cerrar Sesión</a></li>
                            <li class="user-info"> <?php echo $_SESSION['usuario']; ?></li>
                        <?php else: ?>
                            <li><a href="login.php" class="btn-login">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Cart Header -->
    <section class="cart-header">
        <div class="container">
            <h1> Carrito de Compras</h1>
            <p style="font-family: 'Montserrat', sans-serif;">Revisa tus productos antes de finalizar la compra</p>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <?php if(empty($_SESSION['carrito'])): ?>
                <div class="cart-items">
                    <div class="empty-cart">
                        <div class="empty-cart-icon"> </div>
                        <h3>Tu carrito está vacío</h3>
                        <p style="color: #666; margin-bottom: 30px;">¡Agrega productos para comenzar tu compra!</p>
                        <a href="stock.php" class="btn-checkout">Ir al Catálogo</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2>Productos en tu Carrito</h2>
                            <a href="carrito.php?vaciar=1" class="btn-clear-cart" onclick="return confirm('¿Estás seguro de vaciar el carrito?')">Vaciar Carrito</a>
                        </div>
                        
                        <?php foreach($_SESSION['carrito'] as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['imagen']; ?>" alt="<?php echo $item['nombre']; ?>" class="cart-item-image">
                            
                            <div class="cart-item-details">
                                <h3><?php echo $item['nombre']; ?></h3>
                                <div class="cart-item-price">$<?php echo number_format($item['precio'], 2); ?> c/u</div>
                                
                                <form method="POST" class="quantity-control">
                                    <input type="hidden" name="id_producto" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="actualizar_cantidad" onclick="this.form.cantidad.value = Math.max(1, parseInt(this.form.cantidad.value) - 1)">-</button>
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1" readonly>
                                    <button type="submit" name="actualizar_cantidad" onclick="this.form.cantidad.value = parseInt(this.form.cantidad.value) + 1">+</button>
                                </form>
                            </div>
                            
                            <div class="cart-item-actions">
                                <div class="cart-item-subtotal">
                                    $<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?>
                                </div>
                                <a href="carrito.php?eliminar=<?php echo $item['id']; ?>" class="btn-remove" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h2>Resumen del Pedido</h2>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Envío:</span>
                            <span>$<?php echo number_format($envio, 2); ?></span>
                        </div>
                        
                        <div class="shipping-info">
                            <p><strong> Envío a domicilio</strong></p>
                            <p>Entrega estimada: 2-3 días hábiles</p>
                            <p>Envío gratis en compras mayores a $200</p>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <?php if(isset($_SESSION['usuario'])): ?>
                            <a href="procesar_pago.php" class="btn-checkout">Proceder al Pago</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-checkout">Inicia Sesión para Comprar</a>
                        <?php endif; ?>
                        
                        <a href="stock.php" class="btn-continue">Continuar Comprando</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Moda Fácil</h3>
                    <p>Tu destino para la moda elegante y accesible</p>
                    <p>Santo Domingo, República Dominicana</p>
                    <p>info@modafacil.com</p>
                    <p>+1 (809) 555-0123</p>
                </div>
                
                <div class="footer-section">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="stock.php">Catálogo</a></li>
                        <li><a href="ofertas.php">Ofertas</a></li>
                        <li><a href="carrito.php">Carrito</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Atención al Cliente</h3>
                    <ul>
                        <li><a href="#">Preguntas Frecuentes</a></li>
                        <li><a href="#">Envíos y Devoluciones</a></li>
                        <li><a href="#">Términos y Condiciones</a></li>
                        <li><a href="#">Política de Privacidad</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Síguenos</h3>
                    <div class="social-links">
                        <a href="#">Facebook</a>
                        <a href="#">Instagram</a>
                        <a href="#">Twitter</a>
                        <a href="#">Pinterest</a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Moda Fácil. Todos los derechos reservados. Desarrollado en 2025.</p>
            </div>
        </div>
    </footer>
</body>
</html>