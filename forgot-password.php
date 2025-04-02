<?php
session_start();
require_once 'includes/db.php';

$error = null;
$success = null;

// Procesar solicitud de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Verificar si el email existe
    $stmt = $conn->prepare("SELECT id, nombre FROM clientes WHERE email = :email AND activo = 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        // Generar token seguro
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en la base de datos
        $stmt = $conn->prepare("UPDATE clientes SET token_reset = :token, token_expiracion = :expiracion WHERE id = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiracion', $expiracion);
        $stmt->bindParam(':id', $usuario['id']);
        $stmt->execute();
        
        // Enviar email (simulado en este ejemplo)
        $resetLink = "https://tudominio.com/reset-password.php?token=$token";
        
        // En un entorno real, aquí enviarías el email
        $success = "Hemos enviado un enlace de recuperación a tu email. <strong>En desarrollo: </strong> <a href='$resetLink'>$resetLink</a>";
        
    } else {
        $error = "No existe una cuenta con ese email";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Recuperar Contraseña - Yu-Gi-Oh! Store</title>
    <style>
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .auth-header h1 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .auth-header p {
            color: #666;
        }
        
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        
        .auth-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        .auth-form input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .auth-form input:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }
        
        .auth-form button {
            width: 100%;
            padding: 15px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .auth-form button:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .auth-footer a {
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .logo-recovery {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-recovery img {
            height: 60px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <div class="auth-container">
            <div class="logo-recovery">
                <img src="images/logo.png" alt="Yu-Gi-Oh! Store">
            </div>
            
            <div class="auth-header">
                <h1>Recuperar Contraseña</h1>
                <p>Ingresa tu email para recibir instrucciones</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php else: ?>
                <form class="auth-form" method="post">
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <button type="submit">Enviar Enlace de Recuperación</button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p>¿Recordaste tu contraseña? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>