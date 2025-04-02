<?php
session_start();
require_once 'includes/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['cliente'])) {
    $_SESSION['redirect_to'] = 'mis-deseos.php';
    header('Location: login.php');
    exit();
}

$cliente_id = $_SESSION['cliente']['id'];

// Obtener lista de deseos del cliente
$stmt = $conn->prepare("SELECT p.* FROM deseos d 
                       JOIN productos p ON d.producto_id = p.id 
                       WHERE d.cliente_id = :cliente_id");
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$productos_deseados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación de producto de la lista
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_producto'])) {
    $producto_id = $_POST['producto_id'];
    
    $stmt = $conn->prepare("DELETE FROM deseos WHERE cliente_id = :cliente_id AND producto_id = :producto_id");
    $stmt->bindParam(':cliente_id', $cliente_id);
    $stmt->bindParam(':producto_id', $producto_id);
    $stmt->execute();
    
    header('Location: mis-deseos.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Mi Lista de Deseos - Yu-Gi-Oh! Store</title>
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .wishlist-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .wishlist-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .wishlist-item {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 15px;
            position: relative;
            transition: transform 0.3s;
        }
        
        .wishlist-item:hover {
            transform: translateY(-5px);
        }
        
        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .wishlist-item h3 {
            margin: 0 0 10px;
            font-size: 1.1rem;
        }
        
        .wishlist-item .price {
            color: var(--secondary-color);
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .wishlist-actions {
            display: flex;
            justify-content: space-between;
        }
        
        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .no-items {
            text-align: center;
            padding: 40px 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            grid-column: 1 / -1;
        }
        
        .account-links {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="wishlist-container">
        <div class="wishlist-header">
            <h1>Mi Lista de Deseos</h1>
            <p>Tus productos favoritos guardados</p>
        </div>
        
        <?php if (empty($productos_deseados)): ?>
            <div class="no-items">
                <p>Tu lista de deseos está vacía.</p>
                <a href="productos.php" class="btn">Explorar Productos</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($productos_deseados as $producto): ?>
                    <div class="wishlist-item">
                        <form method="post" style="position: absolute; top: 10px; right: 10px;">
                            <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                            <button type="submit" name="eliminar_producto" class="remove-btn" title="Eliminar">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                        
                        <img src="<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>">
                        <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                        <div class="price">$<?= number_format($producto['precio'], 2) ?></div>
                        
                        <div class="wishlist-actions">
                            <a href="producto.php?id=<?= $producto['id'] ?>" class="btn">Ver Detalles</a>
                            <a href="carrito.php?add=<?= $producto['id'] ?>" class="btn" style="background: var(--primary-color);">Añadir al Carrito</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="account-links">
            <a href="mi-cuenta.php" class="btn">Volver a Mi Cuenta</a>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>