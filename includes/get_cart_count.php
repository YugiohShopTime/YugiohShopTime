<?php
session_start();
header('Content-Type: application/json');

$count = 0;
if (isset($_SESSION['cart_count'])) {
    $count = $_SESSION['cart_count'];
}

echo json_encode(['count' => $count]);
?>