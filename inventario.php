<?php
session_start();

if(!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'administrador') {
    header('Location: login.php');
    exit();
}

// Datos de ejemplo del inventario
$productos = [
    ['id' => 1, 'nombre' => 'Blusa de lana', 'categoria' => 'Vestidos', 'precio' => 89.99, 'stock' => 45, 'imagen' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=200'],
    ['id' => 2, 'nombre' => 'Chaqueta marrón', 'categoria' => 'Camisas', 'precio' => 54.99, 'stock' => 23, 'imagen' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=200'],
    ['id' => 3, 'nombre' => 'Pantalón de Vestir', 'categoria' => 'Pantalones', 'precio' => 59.99, 'stock' => 67, 'imagen' => 'https://images.unsplash.com/photo-1560243563-062bfc001d68?w=200'],
    ['id' => 4, 'nombre' => 'Blazer Premium', 'categoria' => 'Blazers', 'precio' => 129.99, 'stock' => 34, 'imagen' => 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=200'],
    ['id' => 5, 'nombre' => 'Falda Plisada', 'categoria' => 'Faldas', 'precio' => 45.99, 'stock' => 12, 'imagen' => 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=200'],
    ['id' => 6, 'nombre' => 'Poloche gato', 'categoria' => 'Suéteres', 'precio' => 89.99, 'stock' => 56, 'imagen' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=200'],
];

// Manejar el registro de nuevos productos
$mensaje = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_producto'])) {
    $mensaje = 'Producto agregado exitosamente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .inventory-header {
            background: linear-gradient(135deg, #000 0%, #2c2c2c 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .inventory-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .inventory-controls {
            padding: 40px 0;
            background-color: #f5f5f5;
        }
        
        .controls-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .btn-new-product {
            background-color: #c9a961;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-new-product:hover {
            background-color: #000;
            transform: translateY(-2px);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #000;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background-color: #000;
            color: white;
        }
        
        .inventory-table-section {
            padding: 60px 0;
        }
        
        .inventory-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .inventory-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .inventory-table th {
            background-color: #000;
            color: white;
            padding: 20px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }
        
        .inventory-table td {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        
        .inventory-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .stock-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .stock-high {
            background-color: #4caf50;
            color: white;
        }
        
        .stock-medium {
            background-color: #ff9800;
            color: white;
        }
        
        .stock-low {
            background-color: #f44336;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background-color: #2196f3;
            color: white;
        }
        
        .btn-edit:hover {
            background-color: #1976d2;
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 40px;
            width: 90%;
            max-width: 600px;
            border-radius: 10px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }
        
        .close-modal:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background-color: #c9a961;
        }
        
        .success-message {
            background-color: #4caf50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .inventory-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .stat-box h3 {
            font-size: 2rem;
            color: #000;
            margin-bottom: 5px;
        }
        
        .stat-box p {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
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
                    <p class="tagline">Gestión de Inventario</p>
                </div>
                <nav class="main-nav">
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="administrador.php">Panel Admin</a></li>
                        <li><a href="inventario.php">Inventario</a></li>
                        <li><a href="logout.php">Cerrar Sesión</a></li>
                        <li class="user-info"> <?php echo $_SESSION['nombre']; ?></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Inventory Header -->
    <section class="inventory-header">
        <div class="container">
            <h1> Gestión de Inventario</h1>
            <p>Control completo de productos y stock</p>
        </div>
    </section>

    <!-- Inventory Controls -->
    <section class="inventory-controls">
        <div class="container">
            <?php if($mensaje): ?>
                <div class="success-message"><?php echo $mensaje; ?></div>
            <?php endif; ?>
            
            <div class="controls-container">
                <div class="search-box">
                    <input type="text" placeholder=" Buscar productos..." id="searchInput">
                </div>
                
                <a href="#" class="btn-new-product" onclick="openModal()"> Nuevo Producto</a>
            </div>
        </div>
    </section>

    <!-- Inventory Stats -->
    <section class="inventory-table-section">
        <div class="container">
            <div class="inventory-stats">
                <div class="stat-box">
                    <h3>237</h3>
                    <p>Total Productos</p>
                </div>
                <div class="stat-box">
                    <h3>$67,890</h3>
                    <p>Valor Inventario</p>
                </div>
                <div class="stat-box">
                    <h3>15</h3>
                    <p>Stock Bajo</p>
                </div>
                <div class="stat-box">
                    <h3>45</h3>
                    <p>Categorías</p>
                </div>
            </div>

         
            <div class="inventory-table">
                <table>
                    <tbody>
                        <?php foreach($productos as $producto): ?>
                        <tr>
                            <td>#<?php echo str_pad($producto['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><img src="<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-img"></td>
                            <td><strong><?php echo $producto['nombre']; ?></strong></td>
                            <td><?php echo $producto['categoria']; ?></td>
                            <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td><?php echo $producto['stock']; ?> unidades</td>
                            <td>
                                <?php 
                                if($producto['stock'] > 50) {
                                    echo '<span class="stock-badge stock-high">Alto</span>';
                                } elseif($producto['stock'] > 20) {
                                    echo '<span class="stock-badge stock-medium">Medio</span>';
                                } else {
                                    echo '<span class="stock-badge stock-low">Bajo</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">

                                    <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $producto['id']; ?>)">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal Nuevo Producto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;">Registrar Nuevo Producto</h2>
            
            <form method="POST" action="inventario.php">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                
                <div class="form-group">
                    <label for="precio">Precio ($)</label>
                    <input type="number" id="precio" name="precio" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Inicial</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"></textarea>
                </div>
                
                <button type="submit" name="agregar_producto" class="btn-submit">Registrar Producto</button>
            </form>
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
        function openModal() {
            document.getElementById('productModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        function editProduct(id) {
            alert('Editando producto ID: ' + id);
        }
        
        function deleteProduct(id) {
            if(confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                alert('Producto ' + id + ' eliminado');
            }
        }
        
        function filterCategory(category) {
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            alert('Filtrando por: ' + category);
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.inventory-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>