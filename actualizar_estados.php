<?php
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['cliente'])) {
    echo json_encode([]);
    exit();
}

$cliente_id = $_SESSION['cliente']['id'];

// Obtener los últimos estados de los pedidos
$stmt = $conn->prepare("SELECT id, estado FROM ventas WHERE cliente_id = :cliente_id");
$stmt->bindParam(':cliente_id', $cliente_id);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($pedidos);
?>