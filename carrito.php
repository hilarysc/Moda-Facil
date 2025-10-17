<?php
session_start();
require_once './db/conectiondb.php';

// Verificar que el usuario esté logueado
if(!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?redirect=carrito.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$error = '';
$mensaje = '';

// Actualizar cantidad de producto en carrito
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_cantidad'])) {
    $carrito_id = $_POST['carrito_id'] ?? 0;
    $nueva_cantidad = $_POST['cantidad'] ?? 1;
    
    if($nueva_cantidad < 1) $nueva_cantidad = 1;
    
    try {
        // Verificar que el producto tenga stock suficiente
        $stmt = $pdo->prepare("
            SELECT c.id, p.stock 
            FROM carrito c
            JOIN productos p ON c.producto_id = p.id
            WHERE c.id = ? AND c.usuario_id = ?
        ");
        $stmt->execute([$carrito_id, $usuario_id]);
        $item = $stmt->fetch();
        
        if($item && $nueva_cantidad <= $item['stock']) {
            $stmt = $pdo->prepare("
                UPDATE carrito 
                SET cantidad = ? 
                WHERE id = ? AND usuario_id = ?
            ");
            $stmt->execute([$nueva_cantidad, $carrito_id, $usuario_id]);
            $mensaje = 'Cantidad actualizada';
        } else {
            $error = 'Stock insuficiente para esta cantidad';
        }
        header('Location: carrito.php');
        exit();
    } catch(PDOException $e) {
        $error = 'Error al actualizar cantidad';
    }
}

// Eliminar producto del carrito
if(isset($_GET['eliminar'])) {
    $carrito_id = $_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("
            DELETE FROM carrito 
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->execute([$carrito_id, $usuario_id]);
        header('Location: carrito.php');
        exit();
    } catch(PDOException $e) {
        $error = 'Error al eliminar producto';
    }
}

// Vaciar carrito completo
if(isset($_GET['vaciar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        header('Location: carrito.php');
        exit();
    } catch(PDOException $e) {
        $error = 'Error al vaciar carrito';
    }
}

// Obtener productos del carrito
try {
    $stmt = $pdo->prepare("
        SELECT c.id as carrito_id, c.cantidad, c.fecha_agregado,
               p.id, p.nombre, p.precio, p.imagen_url, p.stock,
               p.es_oferta, p.precio_oferta
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id = ? AND p.activo = 1
        ORDER BY c.fecha_agregado DESC
    ");
    $stmt->execute([$usuario_id]);
    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $items_carrito = [];
    $error = 'Error al cargar el carrito';
}

// Calcular totales
$subtotal = 0;
foreach($items_carrito as $item) {
    $precio = $item['es_oferta'] ? $item['precio_oferta'] : $item['precio'];
    $subtotal += $precio * $item['cantidad'];
}
$envio = $subtotal > 200 ? 0 : 20;
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
            background-color: #f9f9f9;
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
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #000;
        }
        
        .cart-item-price {
            font-size: 1rem;
            color: #c9a961;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .quantity-control input {
            width: 60px;
            padding: 6px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }
        
        .quantity-control button {
            padding: 6px 12px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .quantity-control button:hover {
            background: #c9a961;
        }
        
        .cart-item-actions {
            text-align: right;
        }
        
        .cart-item-subtotal {
            font-size: 1.2rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 10px;
        }
        
        .btn-remove {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
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
        
        .empty-cart h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #666;
        }
        
        .empty-cart p {
            color: #999;
            margin-bottom: 30px;
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
            font-size: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 700;
            padding-top: 15px;
            border-top: 2px solid #000;
            border-bottom: none;
            margin-top: 15px;
            color: #000;
        }
        
        .shipping-info {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4caf50;
            font-size: 0.9rem;
        }
        
        .shipping-info p {
            margin: 5px 0;
            color: #2e7d32;
        }
        
        .btn-checkout {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #c9a961 0%, #8b7139 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201, 169, 97, 0.4);
        }
        
        .btn-continue {
            width: 100%;
            padding: 12px;
            background: white;
            color: #000;
            border: 2px solid #000;
            border-radius: 5px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .btn-continue:hover {
            background: #000;
            color: white;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn-clear-cart {
            color: #f44336;
            text-decoration: underline;
            cursor: pointer;
            font-size: 0.9rem;
            background: none;
            border: none;
        }
        
        .btn-clear-cart:hover {
            color: #d32f2f;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 600;
        }

        .alert.error {
            background-color: #f44336;
            color: white;
        }

        .alert.success {
            background-color: #4caf50;
            color: white;
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
                        <?php else: ?>
                            <li><a href="ofertas.php">Ofertas</a></li>
                        <?php endif; ?>
                        
                        <li><a href="stock.php">Catálogo</a></li>
                        <li><a href="carrito.php">Carrito</a></li>
                        
                        <?php if(isset($_SESSION['usuario'])): ?>
                            <li><a href="logout.php">Cerrar Sesión</a></li>
                            <li class="user-info"><?php echo htmlspecialchars($_SESSION['usuario']); ?></li>
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
            <h1>Carrito de Compras</h1>
            <p>Revisa tus productos antes de finalizar la compra</p>
        </div>
    </section>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="container">
            <?php if($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if($mensaje): ?>
                <div class="alert success"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <?php if(empty($items_carrito)): ?>
                <!-- Carrito Vacío -->
                <div class="cart-items">
                    <div class="empty-cart">
                        <h3>Tu carrito está vacío</h3>
                        <p>¡Agrega productos para comenzar tu compra!</p>
                        <a href="stock.php" class="btn-checkout">Ir al Catálogo</a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Carrito con Productos -->
                <div class="cart-container">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <div class="header-actions">
                            <h2>Productos en tu Carrito (<?php echo count($items_carrito); ?>)</h2>
                            <button class="btn-clear-cart" onclick="if(confirm('¿Vaciar el carrito?')) window.location.href='carrito.php?vaciar=1'">
                                Vaciar Carrito
                            </button>
                        </div>
                        
                        <?php foreach($items_carrito as $item): ?>
                        <?php $precio = $item['es_oferta'] ? $item['precio_oferta'] : $item['precio']; ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['imagen_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['nombre']); ?>" 
                                 class="cart-item-image">
                            
                            <div class="cart-item-details">
                                <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                <div class="cart-item-price">$<?php echo number_format($precio, 2); ?> c/u</div>
                                
                                <form method="POST" class="quantity-control">
                                    <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                                    <label>Cantidad:</label>
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" 
                                           min="1" max="<?php echo $item['stock']; ?>">
                                    <button type="submit" name="actualizar_cantidad">Actualizar</button>
                                </form>
                            </div>
                            
                            <div class="cart-item-actions">
                                <div class="cart-item-subtotal">
                                    $<?php echo number_format($precio * $item['cantidad'], 2); ?>
                                </div>
                                <a href="carrito.php?eliminar=<?php echo $item['carrito_id']; ?>" 
                                   class="btn-remove" 
                                   onclick="return confirm('¿Eliminar este producto?')">
                                    Eliminar
                                </a>
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
                            <span>
                                <?php if($envio == 0): ?>
                                    <span style="color: #4caf50; font-weight: 700;">GRATIS</span>
                                <?php else: ?>
                                    $<?php echo number_format($envio, 2); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="shipping-info">
                            <p><strong>Envío a domicilio</strong></p>
                            <p>Entrega estimada: 2-3 días hábiles</p>
                            <p>Envío gratis en compras mayores a $200</p>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <a href="procesar_pago.php" class="btn-checkout">Proceder al Pago</a>
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
                </div>
                
                <div class="footer-section">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="stock.php">Catálogo</a></li>
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
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Moda Fácil. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>