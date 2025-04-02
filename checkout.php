<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

if (empty($_SESSION['carrito'])) {
    header('Location: productos.php');
    exit();
}

// Procesar el pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pago = $_POST['metodo_pago'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    
    // Validar datos
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es requerido";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Email inválido";
    if (empty($direccion)) $errores[] = "La dirección es requerida";
    
    if (empty($errores)) {
        // Calcular total
        $total = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        
        // Crear venta en la base de datos
        try {
            $conn->beginTransaction();
            
            // Insertar venta
            $stmt = $conn->prepare("INSERT INTO ventas (nombre_cliente, email, direccion, total, metodo_pago, fecha) 
                                  VALUES (:nombre, :email, :direccion, :total, :metodo, NOW())");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':direccion', $direccion);
            $stmt->bindParam(':total', $total);
            $stmt->bindParam(':metodo', $metodo_pago);
            $stmt->execute();
            $venta_id = $conn->lastInsertId();
            
            // Insertar detalles de venta y actualizar stock
            foreach ($_SESSION['carrito'] as $item) {
                $stmt = $conn->prepare("INSERT INTO venta_detalles (venta_id, producto_id, cantidad, precio_unitario) 
                                      VALUES (:venta_id, :producto_id, :cantidad, :precio)");
                $stmt->bindParam(':venta_id', $venta_id);
                $stmt->bindParam(':producto_id', $item['id']);
                $stmt->bindParam(':cantidad', $item['cantidad']);
                $stmt->bindParam(':precio', $item['precio']);
                $stmt->execute();
                
                // Actualizar stock
                $stmt = $conn->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id");
                $stmt->bindParam(':cantidad', $item['cantidad']);
                $stmt->bindParam(':producto_id', $item['id']);
                $stmt->execute();
            }
            
            $conn->commit();
            
            // Limpiar carrito y redirigir a factura
            $_SESSION['venta_id'] = $venta_id;
            unset($_SESSION['carrito']);
            header('Location: factura.php');
            exit();
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $errores[] = "Error al procesar el pago: " . $e->getMessage();
        }
    }
}

// Calcular total
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Checkout - Finalizar Compra</title>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <h1>Finalizar Compra</h1>
        
        <?php if (!empty($errores)): ?>
            <div class="errores">
                <?php foreach ($errores as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="info-envio">
                <h2>Información de Envío</h2>
                <form action="checkout.php" method="post">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion">Dirección de Envío</label>
                        <textarea id="direccion" name="direccion" required><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodo_pago">Método de Pago</label>
                        <select id="metodo_pago" name="metodo_pago" required>
                            <option value="paypal">PayPal</option>
                            <option value="oxxo">Depósito OXXO</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="Envíos">Envios</option>
                        </select>
                    </div>
                    
                    <br>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Realizar Pago</button>
                    </div>
                </form>
            </div>
            
            <div class="resumen-compra">
                <h2>Resumen de Compra</h2>
                <div class="resumen-items">
                    <?php foreach ($_SESSION['carrito'] as $item): ?>
                        <div class="resumen-item">
                            <span><?= $item['nombre'] ?> x<?= $item['cantidad'] ?></span>
                            <span>$<?= number_format($item['precio'] * $item['cantidad'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="resumen-total">
                    <span>Total:</span>
                    <span>$<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="js/checkout.js"></script>
</body>
</html>