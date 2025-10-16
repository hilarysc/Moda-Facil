<?php
session_start();

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'administrador') {
    header('Location: login.php');
    exit();
}

$nombre_admin = $_SESSION['nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .admin-hero {
            background: linear-gradient(135deg, #000 0%, #2c2c2c 100%);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
        }
        
        .admin-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .admin-stats {
            padding: 60px 0;
            background-color: #f5f5f5;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 35px;
            text-align: center;
            border-left: 5px solid #c9a961;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .admin-modules {
            padding: 60px 0;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .module-card {
            background: white;
            border: 2px solid #e0e0e0;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .module-card:hover {
            border-color: #c9a961;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }
        
        .module-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .module-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #000;
        }
        
        .module-card p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .module-card a {
            display: inline-block;
            background-color: #000;
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .module-card a:hover {
            background-color: #c9a961;
        }
        
        .quick-actions {
            background-color: #f5f5f5;
            padding: 60px 0;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .action-btn {
            background: white;
            padding: 30px 20px;
            text-align: center;
            border: 2px solid #e0e0e0;
            text-decoration: none;
            color: #000;
            transition: all 0.3s ease;
            display: block;
        }
        
        .action-btn:hover {
            background-color: #000;
            color: white;
            border-color: #000;
        }
        
        .action-btn-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .recent-activity {
            padding: 60px 0;
        }
        
        .activity-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-top: 40px;
        }
        
        .activity-item {
            padding: 25px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            background-color: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #000;
        }
        
        .activity-time {
            color: #999;
            font-size: 0.9rem;
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
                    <p class="tagline">Panel de Administración</p>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="administrador.php">Panel Admin</a></li>
                        <li><a href="inventario.php">Inventario</a></li>
        
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                        <li class="user-info"> <?php echo $nombre_admin; ?></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Admin Hero -->
    <section class="admin-hero">
        <div class="container">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?php echo $nombre_admin; ?> | Control total de tu tienda online</p>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="admin-stats">
        <div class="container">
            <h2 class="section-title">Estadísticas del Día</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-number">$12,458</div>
                    <div class="stat-label">Ventas Hoy</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-number">47</div>
                    <div class="stat-label">Pedidos Nuevos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-number">1,234</div>
                    <div class="stat-label">Clientes Activos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon"></div>
                    <div class="stat-number">856</div>
                    <div class="stat-label">Productos en Stock</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admin Modules -->
    <section class="admin-modules">
        <div class="container">
            <h2 class="section-title">Módulos de Gestión</h2>
            <div class="modules-grid">
                <div class="module-card">
                    <div class="module-icon"></div>
                    <h3>Inventario</h3>
                    <p>Gestiona productos, categorías, stock y precios de toda tu tienda</p>
                    <a href="inventario.php">Acceder a Inventario</a>
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
                    <p>Panel de Administración</p>
                    <p> Santo Domingo, República Dominicana</p>
                    <p> admin@modafacil.com</p>
                </div>
                
                <div class="footer-section">
                    <h3>Soporte Técnico</h3>
                    <ul>
                        <li><a href="#">Documentación</a></li>
                        <li><a href="#">Centro de Ayuda</a></li>
                        <li><a href="#">Contactar Soporte</a>