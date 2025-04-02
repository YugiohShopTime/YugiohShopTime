<?php
$servername = "baqoze9aqiwnr6yg63n9-mysql.services.clever-cloud.com";
$username = "ulnh9vhu3qfuwq2j";
$password = "6fBsnjgIhXIQVcWyQOyZ";
$dbname = "baqoze9aqiwnr6yg63n9";

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
