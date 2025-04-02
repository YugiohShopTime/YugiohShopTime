<?php
session_start();
header('Content-Type: application/json');

$count = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {
        $count += $item['cantidad'];
    }
}

echo json_encode(['count' => $count]);
?>