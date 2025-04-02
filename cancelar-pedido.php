<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['cliente'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: mis-pedidos.php');
    exit();
}

$pedido_id = $_GET['id'];
$cliente_id = $_SESSION['cliente']['id'];

// Verificar que el pedido pertenece al cliente
$stmt = $conn->prepare("SELECT id FROM ventas WHERE id = :id AND cliente_id = :cliente_id AND estado = 'pendiente'");
$stmt->bindParam(':id', $pedido_id);
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "No puedes cancelar este pedido";
    header('Location: mis-pedidos.php');
    exit();
}

// Actualizar estado a cancelado
$stmt = $conn->prepare("UPDATE ventas SET estado = 'cancelado' WHERE id = :id");
$stmt->bindParam(':id', $pedido_id);
$stmt->execute();

$_SESSION['success'] = "Pedido cancelado correctamente";
header('Location: mis-pedidos.php');
exit();
?>