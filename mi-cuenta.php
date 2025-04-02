<?php
session_start();
require_once 'includes/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['cliente'])) {
    header('Location: login.php');
    exit();
}

$cliente_id = $_SESSION['cliente']['id'];
$email = $_SESSION['cliente']['email'];

// Obtener información del cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = :id");
$stmt->bindParam(':id', $cliente_id);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener direcciones del cliente
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE cliente_id = :cliente_id ORDER BY principal DESC, id DESC");
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener compras pendientes
$stmt = $conn->prepare("SELECT * FROM ventas 
                       WHERE cliente_id = :cliente_id AND estado = 'pendiente'
                       ORDER BY fecha DESC");
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$compras_pendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formularios de direcciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar_direccion'])) {
        // Procesar nueva dirección
        $alias = trim($_POST['alias']);
        $direccion = trim($_POST['direccion']);
        $ciudad = trim($_POST['ciudad']);
        $estado = trim($_POST['estado']);
        $codigo_postal = trim($_POST['codigo_postal']);
        $principal = isset($_POST['principal']) ? 1 : 0;
        
        // Si se marca como principal, quitar principal de las demás
        if ($principal) {
            $stmt = $conn->prepare("UPDATE direcciones SET principal = 0 WHERE cliente_id = :cliente_id");
            $stmt->bindParam(':cliente_id', $cliente_id);
            $stmt->execute();
        }
        
        // Insertar nueva dirección
        $stmt = $conn->prepare("INSERT INTO direcciones (cliente_id, alias, direccion, ciudad, estado, codigo_postal, principal) 
                              VALUES (:cliente_id, :alias, :direccion, :ciudad, :estado, :codigo_postal, :principal)");
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':alias', $alias);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':ciudad', $ciudad);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':codigo_postal', $codigo_postal);
        $stmt->bindParam(':principal', $principal);
        $stmt->execute();
        
        header('Location: mi-cuenta.php#direcciones');
        exit();
    } elseif (isset($_POST['eliminar_direccion'])) {
        // Eliminar dirección
        $direccion_id = $_POST['direccion_id'];
        
        $stmt = $conn->prepare("DELETE FROM direcciones WHERE id = :id AND cliente_id = :cliente_id");
        $stmt->bindParam(':id', $direccion_id);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        
        header('Location: mi-cuenta.php#direcciones');
        exit();
    } elseif (isset($_POST['establecer_principal'])) {
        // Establecer dirección como principal
        $direccion_id = $_POST['direccion_id'];
        
        // Primero quitar principal de todas
        $stmt = $conn->prepare("UPDATE direcciones SET principal = 0 WHERE cliente_id = :cliente_id");
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        
        // Luego establecer la nueva principal
        $stmt = $conn->prepare("UPDATE direcciones SET principal = 1 WHERE id = :id AND cliente_id = :cliente_id");
        $stmt->bindParam(':id', $direccion_id);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        
        header('Location: mi-cuenta.php#direcciones');
        exit();
    } elseif (isset($_POST['cancelar_pedido'])) {
        // Cancelar pedido pendiente
        $pedido_id = $_POST['pedido_id'];
        
        $stmt = $conn->prepare("UPDATE ventas SET estado = 'cancelado' WHERE id = :id AND cliente_id = :cliente_id AND estado = 'pendiente'");
        $stmt->bindParam(':id', $pedido_id);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        
        header('Location: mi-cuenta.php#compras-pendientes');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Mi Cuenta - Yu-Gi-Oh! Store</title>
    <style>
        .account-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .account-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .account-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .account-sections {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }
        
        .account-nav {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .account-navs {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .account-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .account-navs ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .account-nav li {
            margin-bottom: 15px;
        }
        .account-navs li {
            margin-bottom: 15px;
        }
        .account-nav a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
         .account-navs a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .account-nav a:hover,
        .account-nav a.active {
            background: var(--secondary-color);
            color: white;
        }
        .account-navs a:hover,
        .account-navs a.active {
            background: darkred;
            color: white;
        }
        .account-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .section-title {
            color: var(--secondary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        /* Estilos para la sección de direcciones */
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .address-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .address-card.principal {
            border-color: var(--secondary-color);
            background: #f8f9fa;
        }
        
        .address-card h3 {
            margin-top: 0;
            color: var(--secondary-color);
        }
        
        .address-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .address-form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .principal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--secondary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        /* Estilos para el formulario */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .form-group textarea {
            min-height: 100px;
        }
        
        .btn-submit {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        /* Estilos para compras pendientes */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .orders-table th, 
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background: #f5f5f5;
        }
        
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        
        /* Estilos generales */
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .info-card {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .account-sections {
                grid-template-columns: 1fr;
            }
            
            .address-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="account-container">
        <div class="account-header">
            <h1>Mi Cuenta</h1>
            <h2>Bienvenido, <?= htmlspecialchars($cliente['nombre']) ?></h2>
        </div>
        
        <div class="account-sections">
            <nav class="account-nav">

                <ul>
                    <li><a href="#resumen" class="active">Resumen</a></li>
                    <li><a href="#direcciones">Direcciones</a></li>
                    <li><a href="#compras-pendientes">Compras Pendientes</a></li>
                    <nav class="account-navs">
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
            
            <div class="account-content">
                <!-- Sección Resumen -->
                <section id="resumen">
                    <h2 class="section-title">Resumen de Cuenta</h2>
                    
                    <div class="info-card">
                        <h3>Información Personal</h3>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['nombre']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($cliente['email']) ?></p>
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($cliente['telefono'] ?? 'No registrado') ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Estadísticas</h3>
                        <p><strong>Compras pendientes:</strong> <?= count($compras_pendientes) ?></p>
                        <p><strong>Direcciones registradas:</strong> <?= count($direcciones) ?></p>
                        <p><strong>Miembro desde:</strong> <?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?></p>
                    </div>
                </section>
                
                <!-- Sección Direcciones -->
                <section id="direcciones" style="display: none;">
                    <h2 class="section-title">Mis Direcciones</h2>
                    
                    <div class="address-grid">
                        <div>
                            <h3>Mis Direcciones Registradas</h3>
                            
                            <?php if (empty($direcciones)): ?>
                                <p>No tienes direcciones registradas.</p>
                            <?php else: ?>
                                <?php foreach ($direcciones as $direccion): ?>
                                    <div class="address-card <?= $direccion['principal'] ? 'principal' : '' ?>">
                                        <?php if ($direccion['principal']): ?>
                                            <span class="principal-badge">Principal</span>
                                        <?php endif; ?>
                                        
                                        <h3><?= htmlspecialchars($direccion['alias']) ?></h3>
                                        <p><?= htmlspecialchars($direccion['direccion']) ?></p>
                                        <p><?= htmlspecialchars($direccion['ciudad']) ?>, <?= htmlspecialchars($direccion['estado']) ?></p>
                                        <p>C.P. <?= htmlspecialchars($direccion['codigo_postal']) ?></p>
                                        
                                        <div class="address-actions">
                                            <?php if (!$direccion['principal']): ?>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="direccion_id" value="<?= $direccion['id'] ?>">
                                                    <button type="submit" name="establecer_principal" class="btn btn-primary">Establecer como Principal</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="direccion_id" value="<?= $direccion['id'] ?>">
                                                <button type="submit" name="eliminar_direccion" class="btn btn-danger" onclick="return confirm('¿Eliminar esta dirección?')">Eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="address-form">
                            <h3>Agregar Nueva Dirección</h3>
                            
                            <form method="post">
                                <div class="form-group">
                                    <label for="alias">Alias (Ej: Casa, Oficina)</label>
                                    <input type="text" id="alias" name="alias" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="direccion">Dirección Completa</label>
                                    <textarea id="direccion" name="direccion" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="ciudad">Ciudad</label>
                                    <input type="text" id="ciudad" name="ciudad" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <input type="text" id="estado" name="estado" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="codigo_postal">Código Postal</label>
                                    <input type="text" id="codigo_postal" name="codigo_postal" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="principal"> Establecer como dirección principal
                                    </label>
                                </div>
                                
                                <button type="submit" name="agregar_direccion" class="btn-submit">Guardar Dirección</button>
                            </form>
                        </div>
                    </div>
                </section>
                
                <!-- Sección Compras Pendientes -->
                <section id="compras-pendientes" style="display: none;">
                    <h2 class="section-title">Mis Compras Pendientes</h2>
                    
                    <?php if (empty($compras_pendientes)): ?>
                        <p>No tienes compras pendientes en este momento.</p>
                    <?php else: ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Pedido #</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compras_pendientes as $pedido): ?>
                                    <tr>
                                        <td><?= $pedido['id'] ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                                        <td>$<?= number_format($pedido['total'], 2) ?></td>
                                        <td class="status-pending">Pendiente</td>
                                        <td>
                                            <a href="factura.php?id=<?= $pedido['id'] ?>" class="btn btn-primary">Ver Factura</a>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                                <button type="submit" name="cancelar_pedido" class="btn btn-danger" onclick="return confirm('¿Cancelar este pedido?')">Cancelar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Mostrar/ocultar secciones
        document.querySelectorAll('.account-nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === 'logout.php') return;
                
                e.preventDefault();
                
                // Ocultar todas las secciones
                document.querySelectorAll('.account-content section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Mostrar la sección seleccionada
                const target = this.getAttribute('href');
                document.querySelector(target).style.display = 'block';
                
                // Actualizar enlace activo
                document.querySelectorAll('.account-nav a').forEach(a => {
                    a.classList.remove('active');
                });

            });
        });
        
        // Mostrar la sección de resumen por defecto
        document.querySelector('#resumen').style.display = 'block';
        
        // Manejar hash de URL al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);
                const navLink = document.querySelector(`.account-nav a[href="${window.location.hash}"]`);
                
                if (target && navLink) {
                    document.querySelectorAll('.account-content section').forEach(section => {
                        section.style.display = 'none';
                    });
                    
                    target.style.display = 'block';
                    
                    document.querySelectorAll('.account-nav a').forEach(a => {
                        a.classList.remove('active');

                    });
                    navLink.classList.add('active');
                }
            }
        });
    </script>

</body>
</html>