<?php
session_start();
require_once 'includes/db.php';

// Redirigir si ya está logueado
if (isset($_SESSION['cliente'])) {
    header('Location: index.php');
    exit();
}

$errores = [];
$valores = [
    'nombre' => '',
    'email' => '',
    'direccion' => '',
    'telefono' => ''
];

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar datos
    $valores['nombre'] = trim($_POST['nombre']);
    $valores['email'] = trim($_POST['email']);
    $valores['direccion'] = trim($_POST['direccion']);
    $valores['telefono'] = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validaciones
    if (empty($valores['nombre'])) {
        $errores['nombre'] = 'El nombre es obligatorio';
    }

    if (empty($valores['email'])) {
        $errores['email'] = 'El email es obligatorio';
    } elseif (!filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido';
    } else {
        // Verificar si el email ya existe
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = :email");
        $stmt->bindParam(':email', $valores['email']);
        $stmt->execute();
        if ($stmt->fetch()) {
            $errores['email'] = 'Este email ya está registrado';
        }
    }

    if (empty($password)) {
        $errores['password'] = 'La contraseña es obligatoria';
    } elseif (strlen($password) < 8) {
        $errores['password'] = 'La contraseña debe tener al menos 8 caracteres';
    }

    if ($password !== $confirm_password) {
        $errores['confirm_password'] = 'Las contraseñas no coinciden';
    }

    // Si no hay errores, registrar al cliente
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $conn->prepare("INSERT INTO clientes (nombre, email, password, direccion, telefono) 
                                  VALUES (:nombre, :email, :password, :direccion, :telefono)");
            $stmt->bindParam(':nombre', $valores['nombre']);
            $stmt->bindParam(':email', $valores['email']);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':direccion', $valores['direccion']);
            $stmt->bindParam(':telefono', $valores['telefono']);
            $stmt->execute();

            // Obtener el ID del nuevo cliente
            $cliente_id = $conn->lastInsertId();

            // Iniciar sesión automáticamente
            $_SESSION['cliente'] = [
                'id' => $cliente_id,
                'nombre' => $valores['nombre'],
                'email' => $valores['email']
            ];

            // Redirigir al dashboard o página principal
            $_SESSION['registro_exitoso'] = true;
            header('Location: mi-cuenta.php');
            exit();
        } catch (PDOException $e) {
            $errores['general'] = 'Error al registrar: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Registro - Yu-Gi-Oh! Store</title>
    <style>
        .auth-container {
            max-width: 600px;
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
        
        .auth-form input, 
        .auth-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        .auth-form input:focus,
        .auth-form textarea:focus {
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
            margin-top: 5px;
            font-size: 0.9rem;
        }
        
        .input-error {
            border-color: var(--danger-color) !important;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .password-strength {
            margin-top: 5px;
            height: 5px;
            background: #eee;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Crear Cuenta</h1>
                <p>Regístrate para disfrutar de todos los beneficios</p>
            </div>
            
            <?php if (!empty($errores['general'])): ?>
                <div class="error-message" style="margin-bottom: 20px; text-align: center;">
                    <?= $errores['general'] ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo*</label>
                        <input type="text" id="nombre" name="nombre" 
                               value="<?= htmlspecialchars($valores['nombre']) ?>"
                               class="<?= isset($errores['nombre']) ? 'input-error' : '' ?>"
                               required>
                        <?php if (isset($errores['nombre'])): ?>
                            <div class="error-message"><?= $errores['nombre'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico*</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($valores['email']) ?>"
                           class="<?= isset($errores['email']) ? 'input-error' : '' ?>"
                           required>
                    <?php if (isset($errores['email'])): ?>
                        <div class="error-message"><?= $errores['email'] ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña*</label>
                        <input type="password" id="password" name="password" 
                               class="<?= isset($errores['password']) ? 'input-error' : '' ?>"
                               required>
                        <div class="password-strength">
                            <div class="strength-bar" id="strength-bar"></div>
                        </div>
                        <?php if (isset($errores['password'])): ?>
                            <div class="error-message"><?= $errores['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña*</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="<?= isset($errores['confirm_password']) ? 'input-error' : '' ?>"
                               required>
                        <?php if (isset($errores['confirm_password'])): ?>
                            <div class="error-message"><?= $errores['confirm_password'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <textarea id="direccion" name="direccion" rows="2"><?= htmlspecialchars($valores['direccion']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" 
                           value="<?= htmlspecialchars($valores['telefono']) ?>">
                </div>
                
                <div class="form-group" style="margin-top: 30px;">
                    <button type="submit">Registrarse</button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Validación de fortaleza de contraseña en tiempo real
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('strength-bar');
            let strength = 0;
            
            // Verificar longitud
            if (password.length > 7) strength += 1;
            if (password.length > 11) strength += 1;
            
            // Verificar caracteres especiales
            if (password.match(/[!@#$%^&*(),.?":{}|<>]/)) strength += 1;
            
            // Verificar números
            if (password.match(/[0-9]/)) strength += 1;
            
            // Verificar mayúsculas y minúsculas
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
            
            // Actualizar barra de fortaleza
            switch(strength) {
                case 0:
                    strengthBar.style.width = '0%';
                    strengthBar.style.background = '#dc3545';
                    break;
                case 1:
                    strengthBar.style.width = '25%';
                    strengthBar.style.background = '#dc3545';
                    break;
                case 2:
                    strengthBar.style.width = '50%';
                    strengthBar.style.background = '#ffc107';
                    break;
                case 3:
                    strengthBar.style.width = '75%';
                    strengthBar.style.background = '#28a745';
                    break;
                case 4:
                case 5:
                    strengthBar.style.width = '100%';
                    strengthBar.style.background = '#28a745';
                    break;
            }
        });
        
        // Validación del formulario antes de enviar
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>