<?php
session_start();

// Verificar que el usuario esté logueado
if(!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar que haya productos en el carrito
if(empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit();
}

// Calcular totales
$subtotal = 0;
foreach($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}
$envio = 20.00;
$total = $subtotal + $envio;

// Guardar en sesión
$_SESSION['total_pedido'] = $total;
$_SESSION['subtotal_pedido'] = $subtotal;
$_SESSION['envio_pedido'] = $envio;

// Procesar el pedido
$pedido_completado = false;
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalizar_pedido'])) {
    // Aquí irá la lógica para guardar el pedido en la base de datos
    // Por ahora solo simulamos el proceso
    
    $numero_pedido = 'MF-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    $_SESSION['ultimo_pedido'] = $numero_pedido;
    
    // Vaciar el carrito
    $_SESSION['carrito'] = [];
    
    $pedido_completado = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido - Moda Fácil</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .checkout-header {
            background: linear-gradient(135deg, #c9a961 0%, #8b7139 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        
        .checkout-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .checkout-section {
            padding: 60px 0;
            background-color: #f5f5f5;
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
        }
        
        .checkout-form {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .checkout-form h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c9a961;
        }
        
        .form-section {
            margin-bottom: 40px;
        }
        
        .form-section h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #000;
            font-weight: 600;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #c9a961;
            box-shadow: 0 0 0 4px rgba(201, 169, 97, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .payment-method {
            border: 2px solid #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .payment-method:hover {
            border-color: #c9a961;
            background-color: #fff9f0;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        
        .payment-method.selected {
            border-color: #c9a961;
            background-color: #fff9f0;
        }
        
        .payment-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .order-summary-checkout {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .order-summary-checkout h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #c9a961;
        }
        
        .summary-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .summary-item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .summary-item-details {
            flex: 1;
        }
        
        .summary-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .summary-item-quantity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .summary-item-price {
            font-weight: 700;
            color: #c9a961;
        }
        
        .summary-totals {
            margin-top: 25px;
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
        
        .btn-place-order {
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
            margin-top: 25px;
        }
        
        .btn-place-order:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(201, 169, 97, 0.4);
        }
        
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .security-badge {
            text-align: center;
            color: #666;
            font-size: 0.85rem;
        }
        
        .security-badge-icon {
            font-size: 2rem;
            margin-bottom: 5px;
        }