<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $producto = obtenerProductoPorId($conn, $id);
    
    if ($producto) {
        // Verificar si el producto está en el carrito para calcular stock disponible
        $cantidadEnCarrito = 0;
        if (isset($_SESSION['carrito'][$id])) {
            $cantidadEnCarrito = $_SESSION['carrito'][$id]['cantidad'];
        }
        
        $stockDisponible = max(0, $producto['stock'] - $cantidadEnCarrito);
        echo json_encode(['stock' => $stockDisponible]);
        exit();
    }
}

echo json_encode(['stock' => 0]);
?>