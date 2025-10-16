<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ofertas - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .offers-hero {
            background: linear-gradient(rgba(201, 169, 97, 0.9), rgba(201, 169, 97, 0.9)), 
                        url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600') center/cover;
            padding: 120px 0;
            text-align: center;
            color: white;
        }
        
        .offers-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 40px;
        }
        
        .countdown-item {
            background: rgba(0,0,0,0.3);
            padding: 20px 30px;
            border-radius: 10px;
        }
        
        .countdown-number {
            font-size: 3rem;
            font-weight: 700;
        }
        
        .countdown-label {
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .offers-section {
            padding: 80px 0;
        }
        
        .discount-banner {
            background: linear-gradient(135deg, #000 0%, #2c2c2c 100%);
            color: white;
            padding: 50px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 60px;
        }
        
        .discount-banner h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .discount-percentage {
            font-size: 5rem;
            font-weight: 700;
            color: #c9a961;
            line-height: 1;
        }
    </style>
</head>
<body>
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

    <section class="offers-hero">
        <div class="container">
            <h1> OFERTAS ESPECIALES</h1>
            <p style="font-size: 1.5rem;">Aprovecha descuentos de hasta 50%</p>
            <div class="countdown">
                <div class="countdown-item">
                    <div class="countdown-number">02</div>
                    <div class="countdown-label">Días</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number">14</div>
                    <div class="countdown-label">Horas</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number">32</div>
                    <div class="countdown-label">Minutos</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number">45</div>
                    <div class="countdown-label">Segundos</div>
                </div>
            </div>
        </div>
    </section>

    <section class="offers-section">
        <div class="container">
            <div class="discount-banner">
                <h2>MEGA DESCUENTO</h2>
                <p class="discount-percentage">50%</p>
                <p style="font-size: 1.2rem;">En toda la colección seleccionada</p>
            </div>

            <h2 class="section-title">Productos en Oferta</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=500" alt="Vestido">
                        <span class="product-badge sale">-50%</span>
                    </div>
                    <div class="product-info">
                        <h3>Conjunto</h3>
                        <p class="product-price"><del>$159.99</del> $79.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1539533018447-63fcce2678e3?w=500" alt="Chaqueta">
                        <span class="product-badge sale">-40%</span>
                    </div>
                    <div class="product-info">
                        <h3>Abrigo de cuero</h3>
                        <p class="product-price"><del>$249.99</del> $149.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500" alt="Jeans">
                        <span class="product-badge sale">-30%</span>
                    </div>
                    <div class="product-info">
                        <h3>Chaqueta de Cuero</h3>
                        <p class="product-price"><del>$89.99</del> $62.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=500" alt="Abrigo">
                        <span class="product-badge sale">-45%</span>
                    </div>
                    <div class="product-info">
                        <h3>Poloche casual</h3>
                        <p class="product-price"><del>$299.99</del> $164.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1617922001439-4a2e6562f328?w=500" alt="Blusa">
                        <span class="product-badge sale">-35%</span>
                    </div>
                    <div class="product-info">
                        <h3>Vestido Elegante</h3>
                        <p class="product-price"><del>$69.99</del> $45.49</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1509631179647-0177331693ae?w=500" alt="Zapatos">
                        <span class="product-badge sale">-50%</span>
                    </div>
                    <div class="product-info">
                        <h3>Pantalon feo</h3>
                        <p class="product-price"><del>$119.99</del> $59.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="login.php">Login</a></li>
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