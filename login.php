<?php
session_start();
require_once 'includes/db.php';

// Redirigir si ya está logueado
if (isset($_SESSION['cliente'])) {
    header('Location: index.php');
    exit();
}

$error = null;

// Procesar el formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validar credenciales
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE email = :email AND activo = 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente && password_verify($password, $cliente['password'])) {
        // Crear sesión de cliente
        $_SESSION['cliente'] = [
            'id' => $cliente['id'],
            'nombre' => $cliente['nombre'],
            'email' => $cliente['email']
        ];
        
        // Redirigir al carrito si venía de ahí
        $redirect = isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : 'productos.php';
        unset($_SESSION['redirect_to']);
        header("Location: $redirect");
        exit();
    } else {
        $error = "Email o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Iniciar Sesión - Yu-Gi-Oh! Store</title>
    <style>
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .auth-form input:focus {
            border-color: var(--secondary-color);
            outline: none;
        }
        
        .auth-form button {
            width: 100%;
            padding: 12px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .auth-form button:hover {
            background: #0056b3;
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
        
        .error-message {
            color: var(--danger-color);
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
        }
        
        .social-login {
            margin-top: 30px;
            text-align: center;
        }
        
        .social-login p {
            color: #666;
            margin-bottom: 15px;
            position: relative;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: "";
            display: inline-block;
            width: 30%;
            height: 1px;
            background: #ddd;
            position: absolute;
            top: 50%;
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .social-icon:hover {
            transform: translateY(-3px);
        }
        
        .facebook {
            background: #3b5998;
        }
        
        .google {
            background: #db4437;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Iniciar Sesión</h1>
                <p>Ingresa a tu cuenta para continuar</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?= $error ?></div>
            <?php endif; ?>
            
            <form class="auth-form" method="post">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit">Ingresar</button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
            </div>
            
            <div class="social-login">
                <p>O ingresa con</p>
                <div class="social-icons">
                    <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon google"><i class="fab fa-google"></i></a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Validación básica del formulario
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor completa todos los campos');
            }
        });
    </script>
</body>
</html>