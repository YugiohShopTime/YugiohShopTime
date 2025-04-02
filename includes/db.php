<?php
$servername = "bljazoj7p9lmqk5hjipv-mysql.services.clever-cloud.com";
$username = "uu6mtxlqbxk5cyyl";
$password = "HJdoY3kc3jyxT0WYooY6";
$dbname = "bljazoj7p9lmqk5hjipv";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

function obtenerProductosDestacados($conn) {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE destacado = 1 AND stock > 0 LIMIT 4");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>