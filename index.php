<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Tienda Yu-Gi-Oh! - Inicio</title>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <section class="hero">
            <h1>Bienvenido a nuestra Tienda de Yu-Gi-Oh!</h1>
            <p>Las mejores cartas y productos para duelistas</p>
            <a href="productos.php" class="btn">Ver Productos</a>
        </section>
        
        <section class="destacados">
            <h2>Productos Destacados</h2>
            <div class="productos-grid">
                <?php
                $productos = obtenerProductosDestacados($conn);
                foreach ($productos as $producto) {
                    echo mostrarProducto($producto);
                }
                ?>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>