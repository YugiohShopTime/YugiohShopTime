<?php
session_start();
require_once 'includes/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['cliente'])) {
    $_SESSION['redirect_to'] = 'mis-pedidos.php';
    header('Location: login.php');
    exit();
}

// Obtener datos del cliente
$cliente_id = $_SESSION['cliente']['id'];
$email = $_SESSION['cliente']['email'];

// Filtros adicionales
$estado = $_GET['estado'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$inicio = ($pagina > 1) ? ($pagina * $por_pagina - $por_pagina) : 0;

// Construir consulta base
$sql = "SELECT * FROM ventas WHERE cliente_id = :cliente_id";
$params = [':cliente_id' => $cliente_id];

// Aplicar filtros
if (!empty($estado)) {
    $sql .= " AND estado = :estado";
    $params[':estado'] = $estado;
}

if (!empty($fecha_inicio)) {
    $sql .= " AND fecha >= :fecha_inicio";
    $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
}

if (!empty($fecha_fin)) {
    $sql .= " AND fecha <= :fecha_fin";
    $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
}

// Consulta para el total
$sql_count = "SELECT COUNT(*) as total FROM ventas WHERE cliente_id = :cliente_id";
$params_count = [':cliente_id' => $cliente_id];

// Aplicar mismos filtros al count
if (!empty($estado)) {
    $sql_count .= " AND estado = :estado";
    $params_count[':estado'] = $estado;
}

if (!empty($fecha_inicio)) {
    $sql_count .= " AND fecha >= :fecha_inicio";
    $params_count[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
}

if (!empty($fecha_fin)) {
    $sql_count .= " AND fecha <= :fecha_fin";
    $params_count[':fecha_fin'] = $fecha_fin . ' 23:59:59';
}

// Obtener total de pedidos para paginación
$stmt = $conn->prepare($sql_count);
$stmt->execute($params_count);
$total_pedidos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$paginas = ceil($total_pedidos / $por_pagina);

// Consulta principal con ordenación y paginación
$sql .= " ORDER BY fecha DESC LIMIT :inicio, :por_pagina";

$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindParam(':por_pagina', $por_pagina, PDO::PARAM_INT);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Mis Pedidos - Yu-Gi-Oh! Store</title>
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .orders-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .orders-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background: #f5f5f5;
            font-weight: 600;
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
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a {
            display: block;
            padding: 8px 15px;
            background: #f5f5f5;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .pagination a:hover,
        .pagination .active a {
            background: var(--secondary-color);
            color: white;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .account-links {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="orders-container">
        <div class="orders-header">
            <h1>Mis Pedidos</h1>
            <p>Historial completo de tus compras</p>
        </div>
        
        <?php if (empty($pedidos)): ?>
            <div class="no-orders">
                <p>No has realizado ningún pedido todavía.</p>
                <a href="productos.php" class="btn">Ver Productos</a>
            </div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Pedido #</th>
                        <th>Fecha</th>
                        <th>Método de Pago</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= $pedido['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                            <td><?= ucfirst($pedido['metodo_pago']) ?></td>
                            <td>$<?= number_format($pedido['total'], 2) ?></td>
                            <td>
                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                    <span class="status-pending">Pendiente</span>
                                <?php elseif ($pedido['estado'] === 'completado'): ?>
                                    <span class="status-completed">Completado</span>
                                <?php else: ?>
                                    <span class="status-cancelled">Cancelado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="factura.php?id=<?= $pedido['id'] ?>" class="btn">Ver Factura</a>
                                <?php if ($pedido['estado'] === 'pendiente'): ?>
                                    <a href="cancelar-pedido.php?id=<?= $pedido['id'] ?>" class="btn" style="background: #dc3545; margin-left: 5px;">Cancelar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if ($paginas > 1): ?>
                <ul class="pagination">
                    <?php if ($pagina > 1): ?>
                        <li><a href="mis-pedidos.php?pagina=<?= $pagina - 1 ?>">&laquo; Anterior</a></li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $paginas; $i++): ?>
                        <li class="<?= ($i === $pagina) ? 'active' : '' ?>">
                            <a href="mis-pedidos.php?pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagina < $paginas): ?>
                        <li><a href="mis-pedidos.php?pagina=<?= $pagina + 1 ?>">Siguiente &raquo;</a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="account-links">
            <a href="mi-cuenta.php" class="btn">Volver a Mi Cuenta</a>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>