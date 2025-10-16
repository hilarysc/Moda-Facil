<?php
session_start();

// Verificar que el usuario esté logueado y sea cliente
if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'cliente') {
    header('Location: login.php');
    exit();
}

$nombre_cliente = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .video-welcome {
            position: relative;
            height: 70vh;
            overflow: hidden;
        }
        
        .video-background {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            z-index: 0;
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.7));
            z-index: 1;
        }
        
        .welcome-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 20px;
        }
        
        .welcome-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            margin-bottom: 20px;
            animation: fadeInUp 1s ease;
        }
        
        .welcome-content p {
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 300;
            animation: fadeInUp 1.2s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dashboard-section {
            padding: 60px 0;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .dashboard-card {
            background: white;
            padding: 40px;
            border: 2px solid #e0e0e0;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #c9a961;
        }
        
        .dashboard-card-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .dashboard-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .dashboard-card p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .dashboard-card a {
            display: inline-block;
            background-color: #000;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .dashboard-card a:hover {
            background-color: #c9a961;
        }
        
        .recent-orders {
            background-color: #f5f5f5;
            padding: 60px 0;
        }
        
        .order-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .order-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-table th {
            background-color: #000;
            color: white;
            padding: 20px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }
        
        .order-table td {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-completed {
            background-color: #4caf50;
            color: white;
        }
        
        .status-pending {
            background-color: #ff9800;
            color: white;
        }
        
        .status-processing {
            background-color: #2196f3;
            color: white;
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
                        <li><a href="clientes.php">Mi Cuenta</a></li>
                        <li><a href="ofertas.php">Ofertas</a></li>
                        <li><a href="stock.php">Catálogo</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                        <li><a href="carrito.php">Carrito</a></li>
                        <li class="user-info"> <?php echo $_SESSION['usuario']; ?></li>
                            
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Video Welcome Section -->
    <section class="video-welcome">
        <video class="video-background" autoplay muted loop playsinline>
            <source src="https://player.vimeo.com/external/426295723.sd.mp4?s=4e1b4d63e6c5c4f5d5f5d5f5d5f5d5f5d5f5d5f5&profile_id=164&oauth2_token_id=57447761" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
        <div class="welcome-content">
             <h1> Bienvenido Cliente</h1>
            <p>Descubre las últimas tendencias en moda</p>
            <a href="stock.php" class="btn-primary">Explorar Colección</a>
        </div>
    </section>

    <!-- Dashboard Section -->
    <section class="dashboard-section">
        <div class="container">
            <h2 class="section-title">Tu Panel de Cliente</h2>
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="dashboard-card-icon"></div>
                    <h3>Mis Pedidos</h3>
                    <p>Revisa el estado de tus compras y pedidos anteriores</p>
                    <a href="carrito.php">Ver Pedidos</a>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-icon"></div>
                    <h3>Ofertas Especiales</h3>
                    <p>Descuentos y promociones exclusivas para ti</p>
                    <a href="ofertas.php">Ver Ofertas</a>
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
                    <p>   Santo Domingo, República Dominicana</p>
                    <p>   info@modafacil.com</p>
                    <p>   +1 (809) 555-0123</p>
                </div>
                
                <div class="footer-section">
                    <h3>Enlaces Rápidos</h3>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="stock.php">Catálogo</a></li>
                        <li><a href="ofertas.php">Ofertas</a></li>
                        <li><a href="clientes.php">Mi Cuenta</a></li>
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