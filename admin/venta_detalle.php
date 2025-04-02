<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: ventas.php');
    exit();
}

$venta_id = $_GET['id'];

// Obtener información de la venta
$stmt = $conn->prepare("SELECT * FROM ventas WHERE id = :id");
$stmt->bindParam(':id', $venta_id);
$stmt->execute();
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    header('Location: ventas.php');
    exit();
}

// Obtener detalles de la venta
$stmt = $conn->prepare("SELECT vd.*, p.nombre as producto_nombre 
                       FROM venta_detalles vd
                       JOIN productos p ON vd.producto_id = p.id
                       WHERE vd.venta_id = :venta_id");
$stmt->bindParam(':venta_id', $venta_id);
$stmt->execute();
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado'])) {
    $nuevo_estado = $_POST['estado'];
    
    $stmt = $conn->prepare("UPDATE ventas SET estado = :estado WHERE id = :id");
    $stmt->bindParam(':estado', $nuevo_estado);
    $stmt->bindParam(':id', $venta_id);
    $stmt->execute();
    
    $_SESSION['mensaje'] = 'Estado de la venta actualizado correctamente';
    header("Location: venta_detalle.php?id=$venta_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta - Yu-Gi-Oh! Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .venta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .venta-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-item label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-item p {
            font-size: 1.1rem;
        }
        
        .detalles-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .detalles-table th, .detalles-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .detalles-table th {
            background: #f5f5f5;
        }
        
        .venta-total {
            text-align: right;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 20px 0;
        }
        
        .estado-form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .estado-form select {
            padding: 8px;
            border-radius: 5px;
        }
        
        .mensaje {
            padding: 10px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .estado-pendiente {
            color: #ffc107;
            font-weight: bold;
        }
        
        .estado-completado {
            color: #28a745;
            font-weight: bold;
        }
        
        .estado-cancelado {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <nav class="admin-nav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="productos.php">Productos</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
    
    <div class="admin-container">
        <div class="venta-header">
            <h1>Detalle de Venta #<?= $venta['id'] ?></h1>
            <a href="ventas.php" class="btn">Volver a Ventas</a>
        </div>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <div class="venta-info">
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <label>Cliente</label>
                        <p><?= htmlspecialchars($venta['nombre_cliente']) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Email</label>
                        <p><?= htmlspecialchars($venta['email']) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Dirección</label>
                        <p><?= htmlspecialchars($venta['direccion']) ?></p>
                    </div>
                </div>
                
                <div>
                    <div class="info-item">
                        <label>Fecha</label>
                        <p><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Método de Pago</label>
                        <p><?= ucfirst($venta['metodo_pago']) ?></p>
                    </div>
                    
                    <div class="info-item">
                        <label>Estado</label>
                        <p>
                            <?php if ($venta['estado'] === 'pendiente'): ?>
                                <span class="estado-pendiente">Pendiente</span>
                            <?php elseif ($venta['estado'] === 'completado'): ?>
                                <span class="estado-completado">Completado</span>
                            <?php else: ?>
                                <span class="estado-cancelado">Cancelado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <form class="estado-form" method="post">
                <label for="estado">Cambiar estado:</label>
                <select id="estado" name="estado">
                    <option value="pendiente" <?= $venta['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="completado" <?= $venta['estado'] === 'completado' ? 'selected' : '' ?>>Completado</option>
                    <option value="cancelado" <?= $venta['estado'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </form>
        </div>
        
        <h2>Productos</h2>
        <table class="detalles-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>