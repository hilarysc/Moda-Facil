<?php
require_once './db/conectiondb.php';
verificarAdmin();

// Obtener productos con sus categorías desde la base de datos
$stmt = $pdo->query("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    WHERE p.activo = 1
    ORDER BY p.id DESC
");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el formulario
$categorias = $pdo->query("SELECT * FROM categorias WHERE activo = 1")->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas desde la base de datos
$total_productos = $pdo->query("SELECT COUNT(*) FROM productos WHERE activo = 1")->fetchColumn();
$valor_inventario = $pdo->query("SELECT SUM(precio * stock) FROM productos WHERE activo = 1")->fetchColumn();
$stock_bajo = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock < 20 AND activo = 1")->fetchColumn();
$total_categorias = $pdo->query("SELECT COUNT(*) FROM categorias WHERE activo = 1")->fetchColumn();

$mensaje = '';
$error = '';

// AGREGAR NUEVO PRODUCTO
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_producto'])) {
    $nombre = limpiarDato($_POST['nombre'] ?? '');
    $descripcion = limpiarDato($_POST['descripcion'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? null;
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $imagen_url = limpiarDato($_POST['imagen_url'] ?? 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=500');
    
    if($nombre && $precio && $stock) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO productos (nombre, descripcion, categoria_id, precio, stock, imagen_url) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $descripcion, $categoria_id, $precio, $stock, $imagen_url]);
            
            registrarActividad($pdo, 'actualizacion', "Producto agregado: $nombre", $_SESSION['usuario_id']);
            
            header('Location: inventario.php?success=agregado');
            exit();
        } catch(PDOException $e) {
            $error = 'Error al agregar producto: ' . $e->getMessage();
        }
    } else {
        $error = 'Por favor complete todos los campos obligatorios';
    }
}

// ELIMINAR PRODUCTO (Marcar como inactivo)
if(isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    try {
        $stmt = $pdo->prepare("SELECT nombre FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        
        if($producto) {
            $stmt = $pdo->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            registrarActividad($pdo, 'actualizacion', "Producto eliminado: " . $producto['nombre'], $_SESSION['usuario_id']);
            
            header('Location: inventario.php?success=eliminado');
            exit();
        }
    } catch(PDOException $e) {
        $error = 'Error al eliminar producto';
    }
}

// EDITAR PRODUCTO
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_producto'])) {
    $id = $_POST['id'] ?? 0;
    $nombre = limpiarDato($_POST['nombre'] ?? '');
    $descripcion = limpiarDato($_POST['descripcion'] ?? '');
    $categoria_id = $_POST['categoria_id'] ?? null;
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $imagen_url = limpiarDato($_POST['imagen_url'] ?? '');
    
    if($id && $nombre && $precio) {
        try {
            $stmt = $pdo->prepare("
                UPDATE productos 
                SET nombre = ?, descripcion = ?, categoria_id = ?, precio = ?, stock = ?, imagen_url = ?
                WHERE id = ?
            ");
            $stmt->execute([$nombre, $descripcion, $categoria_id, $precio, $stock, $imagen_url, $id]);
            
            registrarActividad($pdo, 'actualizacion', "Producto editado: $nombre", $_SESSION['usuario_id']);
            
            header('Location: inventario.php?success=editado');
            exit();
        } catch(PDOException $e) {
            $error = 'Error al editar producto';
        }
    }
}

// Mensajes de éxito
if(isset($_GET['success'])) {
    switch($_GET['success']) {
        case 'agregado':
            $mensaje = 'Producto agregado exitosamente';
            break;
        case 'eliminado':
            $mensaje = 'Producto eliminado exitosamente';
            break;
        case 'editado':
            $mensaje = 'Producto actualizado exitosamente';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Moda Fácil</title>
    <link rel="stylesheet" href="./inventario.css">
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


    <section class="inventory-header">
        <div class="container">
            <h1>📦 Gestión de Inventario</h1>
            <p>Control completo de productos y stock</p>
        </div>
    </section>

    <section class="inventory-controls">
        <div class="container">
            <?php if($mensaje): ?>
                <div class="success-message">✓ <?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error-message">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="controls-container">
                <div class="search-box">
                    <input type="text" placeholder="🔍 Buscar productos..." id="searchInput">
                </div>
                
                <button class="btn-new-product" onclick="openModal()">➕ Nuevo Producto</button>
            </div>
        </div>
    </section>

    <section class="inventory-table-section">
        <div class="container">
            <div class="inventory-stats">
                <div class="stat-box">
                    <h3><?php echo $total_productos; ?></h3>
                    <p>Total Productos</p>
                </div>
                <div class="stat-box">
                    <h3>$<?php echo number_format($valor_inventario, 2); ?></h3>
                    <p>Valor Inventario</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $stock_bajo; ?></h3>
                    <p>Stock Bajo</p>
                </div>
                <div class="stat-box">
                    <h3><?php echo $total_categorias; ?></h3>
                    <p>Categorías</p>
                </div>
            </div>

            <div class="inventory-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos as $producto): ?>
                        <tr>
                            <td>#<?php echo str_pad($producto['id'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="product-img"></td>
                            <td><strong><?php echo htmlspecialchars($producto['nombre']); ?></strong></td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
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
                                    <button class="btn-action btn-edit" onclick='editProduct(<?php echo json_encode($producto); ?>)'>Editar</button>
                                    <a href="inventario.php?eliminar=<?php echo $producto['id']; ?>" 
                                       class="btn-action btn-delete" 
                                       onclick="return confirm('¿Está seguro de eliminar este producto?')">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($productos)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                No hay productos en el inventario
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Modal Nuevo Producto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;" id="modalTitle">Registrar Nuevo Producto</h2>
            
            <form method="POST" action="inventario.php" id="productForm">
                <input type="hidden" name="id" id="producto_id">
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="categoria_id">Categoría</label>
                    <select id="categoria_id" name="categoria_id">
                        <option value="">Sin categoría</option>
                        <?php foreach($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio ($) *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="imagen_url">URL de Imagen</label>
                    <input type="text" id="imagen_url" name="imagen_url" placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"></textarea>
                </div>
                
                <button type="submit" name="agregar_producto" class="btn-submit" id="submitBtn">Registrar Producto</button>
            </form>
        </div>
    </div>

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
            document.getElementById('modalTitle').textContent = 'Registrar Nuevo Producto';
            document.getElementById('productForm').reset();
            document.getElementById('producto_id').value = '';
            document.getElementById('submitBtn').name = 'agregar_producto';
            document.getElementById('submitBtn').textContent = 'Registrar Producto';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        function editProduct(producto) {
            document.getElementById('productModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Editar Producto';
            
            document.getElementById('producto_id').value = producto.id;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('categoria_id').value = producto.categoria_id || '';
            document.getElementById('precio').value = producto.precio;
            document.getElementById('stock').value = producto.stock;
            document.getElementById('imagen_url').value = producto.imagen_url || '';
            document.getElementById('descripcion').value = producto.descripcion || '';
            
            document.getElementById('submitBtn').name = 'editar_producto';
            document.getElementById('submitBtn').textContent = 'Actualizar Producto';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeModal();
            }
        }
        
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