<?php
session_start();
require_once 'includes/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['cliente'])) {
    $_SESSION['redirect_to'] = 'mis-direcciones.php';
    header('Location: login.php');
    exit();
}

$cliente_id = $_SESSION['cliente']['id'];

// Obtener direcciones del cliente
$stmt = $conn->prepare("SELECT * FROM direcciones WHERE cliente_id = :cliente_id ORDER BY principal DESC, id DESC");
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formularios
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
        
        header('Location: mis-direcciones.php');
        exit();
    } elseif (isset($_POST['eliminar_direccion'])) {
        // Eliminar dirección
        $direccion_id = $_POST['direccion_id'];
        
        $stmt = $conn->prepare("DELETE FROM direcciones WHERE id = :id AND cliente_id = :cliente_id");
        $stmt->bindParam(':id', $direccion_id);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->execute();
        
        header('Location: mis-direcciones.php');
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
        
        header('Location: mis-direcciones.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Mis Direcciones - Yu-Gi-Oh! Store</title>
    <style>
        .address-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .address-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .address-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .address-list {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            padding: 30px;
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
        
        .btn-submit:hover {
            background: #0056b3;
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
        
        .no-addresses {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .address-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="address-container">
        <div class="address-header">
            <h1>Mis Direcciones</h1>
            <p>Administra tus direcciones de envío</p>
        </div>
        
        <div class="address-grid">
            <div class="address-list">
                <h2>Mis Direcciones Registradas</h2>
                
                <?php if (empty($direcciones)): ?>
                    <div class="no-addresses">
                        <p>No tienes direcciones registradas.</p>
                    </div>
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
                                        <button type="submit" name="establecer_principal" class="btn">Establecer como Principal</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="direccion_id" value="<?= $direccion['id'] ?>">
                                    <button type="submit" name="eliminar_direccion" class="btn" style="background: #dc3545;">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="address-form">
                <h2>Agregar Nueva Dirección</h2>
                
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
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="mi-cuenta.php" class="btn">Volver a Mi Cuenta</a>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>