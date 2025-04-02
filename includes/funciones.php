<?php
function mostrarProducto($producto) {
    $disponibilidad = ($producto['stock'] > 0) ? 
        '<span class="disponible">Disponible</span>' : 
        '<span class="agotado">Agotado</span>';
    
    //<img src="images/productos/'.$producto['imagen'].'" alt="'.$producto['nombre'].'">
    return '
    <div class="producto-card">
        
        <img src="'.$producto['imagen'].'" alt="'.$producto['nombre'].'">
        <h3>'.$producto['nombre'].'</h3>
        <p class="precio">$'.number_format($producto['precio'], 2).'</p>
        <div class="disponibilidad">'.$disponibilidad.'</div>
        <button class="btn agregar-carrito" data-id="'.$producto['id'].'" '.($producto['stock'] <= 0 ? 'disabled' : '').'>
            '.($producto['stock'] > 0 ? 'Añadir al carrito' : 'Agotado').'
        </button>
    </div>';
}

function obtenerProductoPorId($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>