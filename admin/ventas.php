<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Obtener parámetros de filtrado
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$estado = $_GET['estado'] ?? '';

// Construir consulta
$sql = "SELECT * FROM ventas WHERE fecha BETWEEN :fecha_inicio AND :fecha_fin";
$params = [
    ':fecha_inicio' => $fecha_inicio . ' 00:00:00',
    ':fecha_fin' => $fecha_fin . ' 23:59:59'
];

if (!empty($estado)) {
    $sql .= " AND estado = :estado";
    $params[':estado'] = $estado;
}

$sql .= " ORDER BY fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total de ventas filtradas
$totalVentas = 0;
foreach ($ventas as $venta) {
    $totalVentas += $venta['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Ventas - Yu-Gi-Oh! Store</title>
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
        
        .filtros {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filtros-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: flex-end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .total-ventas {
            text-align: right;
            font-size: 1.2rem;
            margin: 20px 0;
        }
        
        .total-ventas span {
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .ventas-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .ventas-table th, .ventas-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .ventas-table th {
            background: #f5f5f5;
        }
        
        .btn {
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .btn-view {
            background: var(--secondary-color);
            color: white;
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
            <h1>Administrar Ventas</h1>
        </div>
        
        <div class="filtros">
            <form class="filtros-form" method="get">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                </div>
                
                <div class="form-group">
                    <label for="fecha_fin">Fecha Fin</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" value="<?= $fecha_fin ?>">
                </div>
                
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="completado" <?= $estado === 'completado' ? 'selected' : '' ?>>Completados</option>
                        <option value="cancelado" <?= $estado === 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="ventas.php" class="btn">Limpiar</a>
                </div>
            </form>
        </div>
        
        <div class="total-ventas">
            <p>Total de ventas: <span>$<?= number_format($totalVentas, 2) ?></span></p>
        </div>
        
        <table class="ventas-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Método</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?= $venta['id'] ?></td>
                        <td><?= htmlspecialchars($venta['nombre_cliente']) ?></td>
                        <td>$<?= number_format($venta['total'], 2) ?></td>
                        <td><?= ucfirst($venta['metodo_pago']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
                        <td>
                            <?php if ($venta['estado'] === 'pendiente'): ?>
                                <span class="estado-pendiente">Pendiente</span>
                            <?php elseif ($venta['estado'] === 'completado'): ?>
                                <span class="estado-completado">Completado</span>
                            <?php else: ?>
                                <span class="estado-cancelado">Cancelado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="venta_detalle.php?id=<?= $venta['id'] ?>" class="btn btn-view">Ver Detalle</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if (empty($ventas)): ?>
            <p class="no-resultados">No se encontraron ventas con los filtros seleccionados.</p>
        <?php endif; ?>
    </div>
    
</body>
</html>