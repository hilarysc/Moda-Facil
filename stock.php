<?php
session_start();
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
        
        .catalog-filters {
            padding: 40px 0;
            background-color: #f5f5f5;
        }
        
        .filters-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        
        .filter-group select {
            padding: 10px 20px;
            border: 2px solid #000;
            border-radius: 5px;
            font-weight: 600;
            background: white;
            cursor: pointer;
        }
        
        .catalog-section {
            padding: 80px 0;
        }
        
        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
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
                            <li><a href="reportes.php">Reportes</a></li>
                            <li><a href="ventas.php">Ventas</a></li>
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

    <section class="catalog-hero">
        <div class="container">
            <h1>Nuestro Catálogo</h1>
            <p style="font-size: 1.3rem;">Descubre nuestra colección completa de moda elegante</p>
        </div>
    </section>

            </div>
        </div>
    </section>

    <section class="catalog-section">
        <div class="container">
            <h2 class="section-title">Toda Nuestra Colección</h2>
            <div class="catalog-grid">
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500" alt="Vestido">
                        <span class="product-badge">Nuevo</span>
                    </div>
                    <div class="product-info">
                        <h3>Vestido Midi Floral</h3>
                        <p class="product-price">$94.99</p>
                        <form method="POST" action="carrito.php">
                            <input type="hidden" name="id_producto" value="1">
                            <input type="hidden" name="nombre" value="Vestido Midi Floral">
                            <input type="hidden" name="precio" value="94.99">
                            <input type="hidden" name="imagen" value="https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" name="agregar_carrito" class="btn-add-cart">Agregar al Carrito</button>
                        </form>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=500" alt="Camisa">
                    </div>
                    <div class="product-info">
                        <h3>Camisa de Lino</h3>
                        <p class="product-price">$64.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=500" alt="Pantalón">
                    </div>
                    <div class="product-info">
                        <h3>Pantalón Wide Leg</h3>
                        <p class="product-price">$79.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1591369822096-ffd140ec948f?w=500" alt="Blazer">
                        <span class="product-badge">Nuevo</span>
                    </div>
                    <div class="product-info">
                        <h3>Blazer Oversize</h3>
                        <p class="product-price">$149.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500" alt="Falda">
                    </div>
                    <div class="product-info">
                        <h3>Falda Plisada</h3>
                        <p class="product-price">$54.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=500" alt="Vestido">
                    </div>
                    <div class="product-info">
                        <h3>Vestido Elegante Negro</h3>
                        <p class="product-price">$89.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=500" alt="Suéter">
                    </div>
                    <div class="product-info">
                        <h3>Suéter Cashmere</h3>
                        <p class="product-price">$119.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1551028719-00167b16eac5?w=500" alt="Jeans">
                        <span class="product-badge sale">Oferta</span>
                    </div>
                    <div class="product-info">
                        <h3>Jeans Skinny</h3>
                        <p class="product-price"><del>$79.99</del> $59.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500" alt="Camisa">
                    </div>
                    <div class="product-info">
                        <h3>Camisa Clásica Blanca</h3>
                        <p class="product-price">$54.99</p>
                        <button class="btn-add-cart">Agregar al Carrito</button>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1617922001439-4a2e6562f328?w=500" alt="Blusa">
                    </div>
                    <div class="product-info">
                        <h3>Blusa de Seda</h3>
                        <p class="product-price">$69.99</p>
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
                
                <div class="product-card">
                    <div class="product-image">
                        <img src="https://images.unsplash.com/photo-1560243563-062bfc001d68?w=500" alt="Pantalón">
                    </div>
                    <div class="product-info">
                        <h3>Pantalón de Vestir</h3>
                        <p class="product-price">$74.99</p>
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