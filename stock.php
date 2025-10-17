<?php
session_start();
require_once './db/conectiondb.php';

// Obtener productos activos de la base de datos
try {
    $stmt = $pdo->query("
        SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.activo = 1
        ORDER BY p.id DESC
    ");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $productos = [];
    $error = 'Error al cargar productos';
}

// Agregar producto al carrito
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_carrito'])) {
    // Verificar si el usuario está logueado
    if(!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?redirect=stock.php');
        exit();
    }
    
    $usuario_id = $_SESSION['usuario_id'];
    $producto_id = $_POST['id_producto'] ?? 0;
    $cantidad = $_POST['cantidad'] ?? 1;
    
    if($cantidad < 1) $cantidad = 1;
    
    try {
        // Verificar que el producto existe y tiene stock
        $stmt = $pdo->prepare("
            SELECT id, stock, nombre 
            FROM productos 
            WHERE id = ? AND activo = 1
        ");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch();
        
        if(!$producto) {
            header('Location: stock.php?error=producto_no_existe');
            exit();
        }
        
        if($producto['stock'] < $cantidad) {
            header('Location: stock.php?error=stock_insuficiente');
            exit();
        }
        
        // Verificar si el producto ya existe en el carrito
        $stmt = $pdo->prepare("
            SELECT id, cantidad 
            FROM carrito 
            WHERE usuario_id = ? AND producto_id = ?
        ");
        $stmt->execute([$usuario_id, $producto_id]);
        $item_carrito = $stmt->fetch();
        
        if($item_carrito) {
            // Actualizar cantidad si ya existe
            $nueva_cantidad = $item_carrito['cantidad'] + $cantidad;
            
            if($nueva_cantidad > $producto['stock']) {
                header('Location: stock.php?error=stock_insuficiente');
                exit();
            }
            
            $stmt = $pdo->prepare("
                UPDATE carrito 
                SET cantidad = ? 
                WHERE usuario_id = ? AND producto_id = ?
            ");
            $stmt->execute([$nueva_cantidad, $usuario_id, $producto_id]);
        } else {
            // Insertar nuevo producto en carrito
            $stmt = $pdo->prepare("
                INSERT INTO carrito (usuario_id, producto_id, cantidad, fecha_agregado) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$usuario_id, $producto_id, $cantidad]);
        }
        
        registrarActividad($pdo, 'venta', "Producto agregado al carrito: " . $producto['nombre'], $usuario_id);
        header('Location: stock.php?success=agregado');
        exit();
        
    } catch(PDOException $e) {
        error_log("Error al agregar carrito: " . $e->getMessage());
        header('Location: stock.php?error=bd');
        exit();
    }
}

// Contar items en carrito del usuario
$carrito_count = 0;
if(isset($_SESSION['usuario_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total 
            FROM carrito 
            WHERE usuario_id = ?
        ");
        $stmt->execute([$_SESSION['usuario_id']]);
        $result = $stmt->fetch();
        $carrito_count = $result['total'] ?? 0;
    } catch(PDOException $e) {
        $carrito_count = 0;
    }
}

// Mensajes
$mensaje = '';
$error_msg = '';
if(isset($_GET['success']) && $_GET['success'] == 'agregado') {
    $mensaje = '✓ Producto agregado al carrito exitosamente';
}
if(isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'stock_insuficiente':
            $error_msg = '❌ Stock insuficiente para esta cantidad';
            break;
        case 'producto_no_existe':
            $error_msg = '❌ El producto no existe';
            break;
        case 'bd':
            $error_msg = '❌ Error al agregar al carrito';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .catalog-hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                        url('https://images.unsplash.com/photo-1445205170230-053b83016050?w=1600') center/cover;
            padding: 100px 0;
            text-align: center;
            color: white;
        }
        
        .catalog-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin-bottom: 15px;
        }
        
        .catalog-section {
            padding: 80px 0;
        }
        
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
        }

        .alert.success {
            background-color: #4caf50;
            color: white;
        }

        .alert.error {
            background-color: #f44336;
            color: white;
        }

        .product-card {
            background: white;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            height: 300px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #c9a961;
            color: white;
            padding: 8px 15px;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 5px;
        }

        .product-badge.low-stock {
            background-color: #f44336;
        }

        .product-info {
            padding: 25px;
        }

        .product-info h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .product-description {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #c9a961;
            margin: 15px 0;
        }

        .product-stock {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 15px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .quantity-selector input {
            width: 70px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
        }

        .btn-add-cart {
            width: 100%;
            background-color: #000;
            color: white;
            border: none;
            padding: 12px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-add-cart:hover {
            background-color: #c9a961;
        }

        .btn-add-cart:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .empty-catalog {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-catalog p {
            font-size: 1.2rem;
            color: #999;
        }

        @media (max-width: 768px) {
            .catalog-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }

            .catalog-hero h1 {
                font-size: 2.5rem;
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
                        
                        <li><a href="carrito.php">Carrito (<?php echo $carrito_count; ?>)</a></li>
                        
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

    <section class="catalog-hero">
        <div class="container">
            <h1>Nuestro Catálogo</h1>
            <p style="font-size: 1.3rem;">Descubre nuestra colección completa de moda elegante</p>
        </div>
    </section>

    <section class="catalog-section">
        <div class="container">
            <?php if($mensaje): ?>
                <div class="alert success"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div class="alert error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <h2 class="section-title">Toda Nuestra Colección</h2>
            
            <?php if(empty($productos)): ?>
                <div class="empty-catalog">
                    <p>No hay productos disponibles en este momento</p>
                </div>
            <?php else: ?>
                <div class="catalog-grid">
                    <?php foreach($productos as $producto): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <?php if($producto['stock'] <= 20): ?>
                                <span class="product-badge low-stock">Stock Bajo</span>
                            <?php elseif($producto['es_oferta']): ?>
                                <span class="product-badge">Oferta</span>
                            <?php else: ?>
                                <span class="product-badge">Disponible</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                            <p class="product-description">
                                <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 60)); ?>...
                            </p>
                            
                            <?php if($producto['es_oferta']): ?>
                                <p class="product-price">
                                    <del>$<?php echo number_format($producto['precio'], 2); ?></del> 
                                    $<?php echo number_format($producto['precio_oferta'], 2); ?>
                                </p>
                            <?php else: ?>
                                <p class="product-price">$<?php echo number_format($producto['precio'], 2); ?></p>
                            <?php endif; ?>
                            
                            <p class="product-stock">Stock: <?php echo $producto['stock']; ?> unidades</p>
                            
                            <?php if($producto['stock'] > 0): ?>
                                <form method="POST" action="stock.php">
                                    <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                    <div class="quantity-selector">
                                        <label>Cantidad:</label>
                                        <input type="number" name="cantidad" value="1" min="1" 
                                               max="<?php echo $producto['stock']; ?>" required>
                                    </div>
                                    <button type="submit" name="agregar_carrito" class="btn-add-cart">
                                        Agregar al Carrito
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn-add-cart" disabled>Sin Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Moda Fácil</h3>
                    <p>Tu destino para la moda elegante y accesible</p>
                    <p>Santo Domingo, República Dominicana</p>
                </div>
                <div class="footer-section">
                    <h3>Enlaces</h3>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="stock.php">Catálogo</a></li>
                        <li><a href="carrito.php">Carrito</a></li>
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