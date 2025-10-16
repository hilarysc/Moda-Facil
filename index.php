<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moda Fácil - Tienda de Ropa Online</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Colección Primavera 2025</h2>
            <p>Descubre las últimas tendencias en moda</p>
            <a href="stock.php" class="btn-primary">Ver Colección</a>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Productos Destacados</h2>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500" alt="Vestido Elegante">
                        <span class="product-badge">Nuevo</span>
                    </div>
                    <div class="product-info">
                        <h3>Blusa de lana</h3>
                        <p class="product-price">$89.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500" alt="Camisa Blanca">
                    </div>
                    <div class="product-info">
                        <h3>Chaqueta Marrón</h3>
                        </h3>
                        <p class="product-price">$54.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1560243563-062bfc001d68?w=500" alt="Pantalón">
                        <span class="product-badge sale">Oferta</span>
                    </div>
                    <div class="product-info">
                        <h3>Pantalón de Vestir</h3>
                        <p class="product-price"><del>$79.99</del> $59.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=500" alt="Blazer">
                    </div>
                    <div class="product-info">
                        <h3>Blazer Premium</h3>
                        <p class="product-price">$129.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Sobre Moda Fácil</h2>
                    <p>Desde 2025, Moda Fácil se ha dedicado a ofrecer prendas de alta calidad que combinan elegancia, comodidad y estilo contemporáneo. Nuestra misión es hacer la moda accesible para todos, sin comprometer la calidad ni el diseño.</p>
                    <p>Trabajamos con los mejores diseñadores y seleccionamos cuidadosamente cada pieza de nuestra colección para garantizar que encuentres exactamente lo que buscas.</p>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=600" alt="Tienda Moda Fácil">
                </div>
            </div>
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