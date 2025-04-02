<?php
session_start();
require_once 'includes/db.php';

$error = null;
$success = null;
$token_valido = false;

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE token_reset = :token AND token_expiracion > NOW()");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        $token_valido = true;
        $user_id = $usuario['id'];
        
        // Procesar cambio de contraseña
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            
            if (strlen($password) < 8) {
                $error = "La contraseña debe tener al menos 8 caracteres";
            } elseif ($password !== $confirm_password) {
                $error = "Las contraseñas no coinciden";
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                
                $stmt = $conn->prepare("UPDATE clientes SET password = :password, token_reset = NULL, token_expiracion = NULL WHERE id = :id");
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':id', $user_id);
                $stmt->execute();
                
                $success = "¡Contraseña actualizada correctamente! Ahora puedes <a href='login.php'>iniciar sesión</a>.";
                $token_valido = false; // Para ocultar el formulario
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Restablecer Contraseña - Yu-Gi-Oh! Store</title>
    <style>
        /* Estilos similares a forgot-password.php */
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .password-strength {
            height: 5px;
            background: #eee;
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            background: #dc3545;
            transition: all 0.3s;
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
                <h1>Restablecer Contraseña</h1>
                <p>Crea una nueva contraseña para tu cuenta</p>
            </div>
            
            <?php if (!$token_valido && !$success): ?>
                <div class="alert alert-error">
                    El enlace de recuperación no es válido o ha expirado. Por favor <a href="forgot-password.php">solicita uno nuevo</a>.
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php elseif ($token_valido): ?>
                <form class="auth-form" method="post">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <div class="password-strength">
                            <div class="strength-bar" id="strength-bar"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <button type="submit">Guardar Nueva Contraseña</button>
                </form>
            <?php endif; ?>
            
            <div class="auth-footer">
                <p><a href="login.php">Volver a Iniciar Sesión</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Validación de fortaleza de contraseña
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strength-bar');
            let strength = 0;
            
            // Validar longitud
            if (password.length > 7) strength += 1;
            if (password.length > 11) strength += 1;
            
            // Validar caracteres especiales
            if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) strength += 1;
            
            // Validar números
            if (password.match(/[0-9]/)) strength += 1;
            
            // Validar mayúsculas y minúsculas
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
            
            // Actualizar barra
            switch(strength) {
                case 0: case 1:
                    strengthBar.style.width = '20%';
                    strengthBar.style.background = '#dc3545';
                    break;
                case 2:
                    strengthBar.style.width = '40%';
                    strengthBar.style.background = '#fd7e14';
                    break;
                case 3:
                    strengthBar.style.width = '70%';
                    strengthBar.style.background = '#ffc107';
                    break;
                case 4: case 5:
                    strengthBar.style.width = '100%';
                    strengthBar.style.background = '#28a745';
                    break;
            }
        });
    </script>
</body>
</html>