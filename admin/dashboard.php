<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Obtener estadísticas
$stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
$totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM ventas");
$totalVentas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT SUM(total) as total FROM ventas WHERE DATE(fecha) = CURDATE()");
$ventasHoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Obtener estadísticas por estado
$stmt = $conn->query("SELECT COUNT(*) as total FROM ventas WHERE estado = 'pendiente'");
$ventasPendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM ventas WHERE estado = 'completado'");
$ventasCompletadas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM ventas WHERE estado = 'cancelado'");
$ventasCanceladas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener últimas ventas con su estado
$stmt = $conn->query("SELECT * FROM ventas ORDER BY fecha DESC LIMIT 5");
$ultimasVentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Yu-Gi-Oh! Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
           .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
        }
        
        .recent-sales {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .recent-sales h2 {
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
        
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .sales-table th, .sales-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .sales-table th {
            background: #f5f5f5;
        }
        
        .admin-nav {
            background: var(--dark-color);
            padding: 15px 0;
        }
        
        .admin-nav ul {
            display: flex;
            list-style: none;
            justify-content: center;
        }
        
        .admin-nav li {
            margin: 0 15px;
        }
        
        .admin-nav a {
            color: white;
            font-weight: 500;
        }
        
        .admin-nav a:hover {
            color: var(--primary-color);
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        .status-completed {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-cancelled {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    
    
    <nav class="admin-nav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="productos.php">Productos</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?= $_SESSION['admin']['nombre'] ?></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Productos</h3>
                <p><?= $totalProductos ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Ventas Totales</h3>
                <p><?= $totalVentas ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Ventas Hoy</h3>
                <p>$<?= number_format($ventasHoy, 2) ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Pendientes</h3>
                <p><?= $ventasPendientes ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Completadas</h3>
                <p><?= $ventasCompletadas ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Canceladas</h3>
                <p><?= $ventasCanceladas ?></p>
            </div>
        </div>
        
        <div class="recent-sales">
            <h2>Últimas Ventas</h2>
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimasVentas as $venta): ?>
                        <tr>
                            <td><?= $venta['id'] ?></td>
                            <td><?= htmlspecialchars($venta['nombre_cliente']) ?></td>
                            <td>$<?= number_format($venta['total'], 2) ?></td>
                            <td><?= ucfirst($venta['metodo_pago']) ?></td>
                            <td>
                                <?php 
                                $statusClass = '';
                                switch($venta['estado']) {
                                    case 'pendiente':
                                        $statusClass = 'status-pending';
                                        break;
                                    case 'completado':
                                        $statusClass = 'status-completed';
                                        break;
                                    case 'cancelado':
                                        $statusClass = 'status-cancelled';
                                        break;
                                }
                                ?>
                                <span class="<?= $statusClass ?>">
                                    <?= ucfirst($venta['estado']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    
</body>
</html>