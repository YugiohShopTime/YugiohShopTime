<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                if (isset($_POST['id_producto'])) {
                    $id = $_POST['id_producto'];
                    $producto = obtenerProductoPorId($conn, $id);
                    
                    if ($producto && $producto['stock'] > 0) {
                        // Verificar si el producto ya está en el carrito
                        $enCarrito = false;
                        foreach ($_SESSION['carrito'] as &$item) {
                            if ($item['id'] == $id) {
                                // Verificar que no supere el stock al agregar
                                if ($item['cantidad'] < $producto['stock']) {
                                    $item['cantidad']++;
                                    $enCarrito = true;
                                }
                                break;
                            }
                        }
                        
                        if (!$enCarrito) {
                            $_SESSION['carrito'][$id] = [
                                'id' => $producto['id'],
                                'nombre' => $producto['nombre'],
                                'precio' => $producto['precio'],
                                'cantidad' => 1,
                                'imagen' => $producto['imagen'],
                                'stock' => $producto['stock'] // Guardamos el stock disponible
                            ];
                        }
                        
                        $_SESSION['mensaje'] = 'Producto añadido al carrito';
                    }
                }
                break;
                
            case 'eliminar':
                if (isset($_POST['id_producto']) && isset($_SESSION['carrito'][$_POST['id_producto']])) {
                    unset($_SESSION['carrito'][$_POST['id_producto']]);
                    $_SESSION['mensaje'] = 'Producto eliminado del carrito';
                }
                break;
                
            case 'actualizar':
                if (isset($_POST['cantidades'])) {
                    foreach ($_POST['cantidades'] as $id => $cantidad) {
                        if (isset($_SESSION['carrito'][$id])) {
                            $cantidad = max(1, intval($cantidad));
                            // No permitir superar el stock disponible
                            $cantidad = min($cantidad, $_SESSION['carrito'][$id]['stock']);
                            $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
                        }
                    }
                    $_SESSION['mensaje'] = 'Carrito actualizado';
                }
                break;
                
            case 'vaciar':
                unset($_SESSION['carrito']);
                $_SESSION['mensaje'] = 'Carrito vaciado';
                break;
        }
        header('Location: carrito.php');
        exit();
    }
}

// Calcular totales
$total = 0;
if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Carrito de Compras - Yu-Gi-Oh! Store</title>
    <style>
        .carrito-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .carrito-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .carrito-items {
            margin-bottom: 30px;
        }
        
        .carrito-item {
            display: flex;
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .carrito-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
        }
        
        .item-info {
            flex-grow: 1;
        }
        
        .carrito-item h3 {
            margin: 0 0 10px;
            color: var(--secondary-color);
        }
        
        .item-precio {
            font-weight: bold;
            margin: 10px 0;
        }
        
        .item-precio .precio-unitario {
            color: #666;
            font-size: 0.9rem;
        }
        
        .cantidad-control {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .cantidad-control input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 0 10px;
        }
        
        .eliminar-item {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .carrito-resumen {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .resumen-linea {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .resumen-total {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--secondary-color);
        }
        
        .carrito-acciones {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .mensaje {
            padding: 15px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .carrito-vacio {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .carrito-item {
                flex-direction: column;
            }
            
            .carrito-item img {
                width: 100%;
                height: auto;
                margin-bottom: 15px;
            }
            
            .carrito-acciones {
                flex-direction: column;
                gap: 10px;
            }
            
            .carrito-acciones .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="carrito-container">
        <div class="carrito-header">
            <h1>Tu Carrito de Compras</h1>
            <p>Revisa y modifica tus productos</p>
        </div>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <?php if (empty($_SESSION['carrito'])): ?>
            <div class="carrito-vacio">
                <p>Tu carrito está vacío</p>
                <a href="productos.php" class="btn">Ver Productos</a>
            </div>
        <?php else: ?>
            <form id="carrito-form" method="post">
                <input type="hidden" name="accion" value="actualizar">
                
                <div class="carrito-items">
                    <?php foreach ($_SESSION['carrito'] as $id => $item): ?>
                        <div class="carrito-item" data-id="<?= $id ?>">
                            <img src="<?= $item['imagen'] ?>" alt="<?= $item['nombre'] ?>">
                            
                            <div class="item-info">
                                <h3><?= htmlspecialchars($item['nombre']) ?></h3>
                                
                                <div class="item-precio">
                                    <span class="precio" data-precio-unitario="<?= $item['precio'] ?>">
                                        $<?= number_format($item['precio'] * $item['cantidad'], 2) ?>
                                    </span>
                                    <span class="precio-unitario">
                                        $<?= number_format($item['precio'], 2) ?> c/u
                                    </span>
                                </div>
                                
                                <div class="cantidad-control">
                                    <button type="button" class="btn-cantidad" data-action="decrementar">-</button>
                                    <input type="number" name="cantidades[<?= $id ?>]" 
                                           value="<?= $item['cantidad'] ?>" 
                                           min="1" 
                                           max="<?= $item['stock'] ?>"
                                           class="cantidad-input">
                                    <button type="button" class="btn-cantidad" data-action="incrementar">+</button>
                                </div>
                            </div>
                            
                            <button type="button" class="eliminar-item" data-id="<?= $id ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="carrito-resumen">
                    <div class="resumen-linea">
                        <span>Subtotal:</span>
                        <span id="subtotal">$<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="resumen-linea">
                        <span>Envío:</span>
                        <span id="envio">$0.00</span>
                    </div>
                    <div class="resumen-linea resumen-total">
                        <span>Total:</span>
                        <span id="total-carrito">$<?= number_format($total, 2) ?></span>
                    </div>
                </div>
                
                <div class="carrito-acciones">
                    <button type="button" id="vaciar-carrito" class="btn" style="background: #dc3545;">Vaciar Carrito</button>
                    <a href="checkout.php" class="btn btn-primary">Proceder al Pago</a>
                </div>
            </form>
            
            <!-- Formulario oculto para eliminar items -->
            <form id="eliminar-form" method="post" style="display: none;">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" name="id_producto" id="id-producto-eliminar">
            </form>
            
            <!-- Formulario oculto para vaciar carrito -->
            <form id="vaciar-form" method="post" style="display: none;">
                <input type="hidden" name="accion" value="vaciar">
            </form>
        <?php endif; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para actualizar los totales
            function actualizarTotales() {
                let subtotal = 0;
                
                // Calcular subtotal
                document.querySelectorAll('.carrito-item').forEach(item => {
                    const precioUnitario = parseFloat(item.querySelector('.precio').getAttribute('data-precio-unitario'));
                    const cantidad = parseInt(item.querySelector('.cantidad-input').value);
                    const precioItem = precioUnitario * cantidad;
                    
                    // Actualizar precio del item
                    item.querySelector('.precio').textContent = '$' + precioItem.toFixed(2);
                    
                    subtotal += precioItem;
                });
                
                // Calcular envío (ejemplo: gratis para compras > $500)
                const envio = subtotal > 500 ? 0 : 10;
                const total = subtotal + envio;
                
                // Actualizar valores en el DOM
                document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
                document.getElementById('envio').textContent = '$' + envio.toFixed(2);
                document.getElementById('total-carrito').textContent = '$' + total.toFixed(2);
            }
            
            // Eventos para botones de incrementar/decrementar
            document.querySelectorAll('.btn-cantidad').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.parentElement.querySelector('.cantidad-input');
                    let value = parseInt(input.value);
                    const max = parseInt(input.getAttribute('max'));
                    
                    if (this.getAttribute('data-action') === 'incrementar') {
                        if (value < max) {
                            input.value = value + 1;
                        } else {
                            alert('No hay suficiente stock disponible');
                        }
                    } else {
                        input.value = value > 1 ? value - 1 : 1;
                    }
                    
                    // Disparar evento de cambio para actualizar totales
                    input.dispatchEvent(new Event('change'));
                });
            });
            
            // Evento para cambios en los inputs de cantidad
            document.querySelectorAll('.cantidad-input').forEach(input => {
                input.addEventListener('change', function() {
                    const max = parseInt(this.getAttribute('max'));
                    let value = parseInt(this.value);
                    
                    // Validar que sea un número válido
                    if (isNaN(value) || value < 1) {
                        this.value = 1;
                    } else if (value > max) {
                        this.value = max;
                        alert('No hay suficiente stock disponible');
                    }
                    
                    actualizarTotales();
                });
            });
            
            // Evento para eliminar items
            document.querySelectorAll('.eliminar-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('¿Estás seguro de eliminar este producto del carrito?')) {
                        const id = this.getAttribute('data-id');
                        document.getElementById('id-producto-eliminar').value = id;
                        document.getElementById('eliminar-form').submit();
                    }
                });
            });
            
            // Evento para vaciar carrito
            document.getElementById('vaciar-carrito').addEventListener('click', function() {
                if (confirm('¿Estás seguro de vaciar completamente tu carrito?')) {
                    document.getElementById('vaciar-form').submit();
                }
            });
            
            // Actualizar totales al cargar la página
            actualizarTotales();
        });
    </script>
</body>
</html>