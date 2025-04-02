<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
$stmt = $conn->prepare("
    SELECT p.*, 
           (p.stock - IFNULL((
               SELECT SUM(cantidad) 
               FROM carrito_temporal 
               WHERE producto_id = p.id
           ), 0)) as stock_real
    FROM productos p
    WHERE p.stock > 0
");
if ($categoria) {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE categoria = :categoria AND stock > 0");
    $stmt->bindParam(':categoria', $categoria);
} elseif ($busqueda) {
    $busquedaLike = "%$busqueda%";
    $stmt = $conn->prepare("SELECT * FROM productos WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda OR nivel  LIKE :busqueda OR atributo  LIKE :busqueda OR tipo  LIKE :busqueda OR precio  LIKE :busqueda OR efectos  LIKE :busqueda  AND stock > 0");
    $stmt->bindParam(':busqueda', $busquedaLike);
} else {
    $stmt = $conn->prepare("SELECT * FROM productos WHERE stock > 0");
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Productos Yu-Gi-Oh!</title>
    <style>
     
        /* Estilo cuando está activo (hover) */
.producto-card.hover-active .producto-front:hover {
    opacity: 0;
}

.producto-card.hover-active .producto-back {
    opacity: 1;
}
.producto-front {
    background: white;
    
    transition: opacity 0.4s ease;
}.producto-back {
    
    background: white;
    padding: 15px;
    
    opacity: 0;
    transition: opacity 0.4s ease;
}

        .filtros-container {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 25px;
            border-radius: 25px;
            margin-bottom: 40px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .filtros-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }
        
        .search-box input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            outline: none;
            background: white;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        .search-box i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .category-filter {
            min-width: 200px;
        }
        
        .category-filter select {
            width: 100%;
            padding: 15px 20px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            background: rgba(255,255,255,0.9);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 15px;
            transition: all 0.3s ease;
        }
        
        .category-filter select:focus {
            outline: none;
            background: white;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        
        .filter-btn {
            padding: 15px 30px;
            background: var(--primary-color);
            color: #000;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: #ffd700;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }
        
        .productos-grid {
            display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px;
        }
        .productos-grids:hover {
            display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        padding: 20px;
        }
       

         .producto-container {
        
        perspective: 1000px;
        height: 400px; /* Ajusta según necesidad */
    }
        
      .producto-card {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.8s;
        transform-style: preserve-3d;
        cursor: pointer;
    }
     
    
    
    .producto-front {
        background: white;
        
        
    }
   
      .producto-info {
        padding: 15px;
        flex-grow: 1;
    }
    
    .producto-nombre {
        font-size: 1.1rem;
        margin: 0 0 10px 0;
        color: #333;
    }
    
    .producto-precio {
        font-weight: bold;
        color: #e63946;
        font-size: 1.2rem;
        margin-top: 10px;
    }
    
    .descripcion:hover {
        font-size: 0.9rem;
        color: #555;
        margin-bottom: 15px;
    }
    
    .detalle-adicional {
        background: rgba(255,255,255,0.7);
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    
    .detalle-adicional p {
        margin: 5px 0;
        font-size: 0.9rem;
    }
         .producto-imagen:hover{
        border-radius: 85px;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: transparent;
        padding: 2px;
        
    }
    
        .producto-card:hover   {
        animation: producto-card 3s infinite;
        display: inline-block;
        transform: rotateY(380deg);           
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        background: white;
        display: flex;
        flex-direction: column;
        border-radius: 10px;
        background: darkorange;
        flex-direction: column;
        }
        .disponibilidad {
            display: inline-block;
            padding: 15px 12px;
            border-radius: 20px;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .disponible {
            background: #d4edda;
            color: #155724;
        }
        
        .agotado {
            background: #f8d7da;
            color: #721c24;

        }
       
       
        
        .agregar-carrito {
            width: 100%;
            padding: 12px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .agregar-carrito:hover {
            background: #0056b3;
        }
        
        .agregar-carrito:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        
        .no-resultados {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        
        .categoria-activa {
            text-align: center;
            margin: 20px 0;
            font-size: 1.5rem;
            color: var(--secondary-color);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .filtros-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            
        }
        .categorias-menu {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.categorias-menu h3 {
    margin-top: 0;
    color: var(--secondary-color);
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.categorias-menu ul {
    list-style: inline;
    padding: 0;
    margin: 0;
}

.categorias-menu li {
    margin-bottom: 8px;
}

.categorias-menu a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s;
}

.categorias-menu a:hover,
.categorias-menu a.active {
    background: var(--secondary-color);
    color: white;
}

.categorias-menu a:hover .badge,
.categorias-menu a.active .badge {
    background: white;
    color: var(--secondary-color);
}

.categorias-menu .badge {
    background: var(--secondary-color);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.8rem;
}
.categorias-combobox {
        max-width: 250px;
        margin-bottom: 20px;
    }
    
    .categorias-combobox .form-control {
        padding: 8px 12px;
        font-size: 0.9rem;
        border-radius: 20px;
        border: 1px solid var(--secondary-color);
        cursor: pointer;
    }
    
    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
     .pulse-icon {
            animation: pulse 1.5s infinite;
            display: inline-block;
        }
   
    .stock-alert {
        padding: 10px 15px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
    }
 @keyframes producto-card {            
           0% { transform: scale(0.9); }
            50% { transform: scale(1.0); }
            100% { transform: scale(0.9); }
            
        }

       @keyframes pulse {            
            0% { transform: scale(1); }
            50% { transform: scale(3.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <main class="productos-container">
        <div class="filtros-container">
            <form class="filtros-form" action="productos.php" method="get">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="busqueda" placeholder="Buscar cartas, mazos..." 
                           value="<?= htmlspecialchars($busqueda ?? '') ?>">
                </div>
                
               <?php
require_once 'includes/db.php';

// Obtener categorías para el sidebar
function obtenerCategoriasMenu($conn) {
    $sql = "SELECT c.*, COUNT(p.id) as total 
            FROM categorias c
            LEFT JOIN productos p ON c.id = p.categoria_id
            GROUP BY c.id
            ORDER BY c.nombre ASC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$categoriasMenu = obtenerCategoriasMenu($conn);
$categoriaSlug = $_GET['categoria'] ?? '';
?>

<!-- En el sidebar de productos.php -->
<div class="sidebar">
<div class="form-group categorias-combobox">
    <label for="selector-categorias" class="sr-only">Categorías</label>
    <select id="selector-categorias" class="form-control" onchange="location = this.value;">
        <option value="productos.php">Todas las categorías</option>
        <?php foreach ($categoriasMenu as $cat): ?>
            <option value="productos.php?categoria=<?= $cat['slug'] ?>" 
                <?= $categoriaSlug == $cat['slug'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nombre']) ?>
                <!-- CAMBIAR TOTAL -->
            </option>
        <?php endforeach; ?>
    </select>
</div>

                
                <button type="submit" class="filter-btn">Filtrar</button>
            </form>
        </div>
        
        
        <div class="productos-grid">

            <?php if (count($productos) > 0): ?>

                <?php foreach ($productos as $producto): ?>
                
                    <div class="producto-card" 
         onmouseover="this.classList.add('hover-active')" 
         onmouseout="this.classList.remove('hover-active')">
         
        <!-- Cara frontal -->
         
                        <!--<img src="images/productos/<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>" class="producto-imagen">-->
                        <div class="producto">
    <a href="detalle_producto.php?id=<?= $producto['id'] ?>">
                        <img src="<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>" class="producto-imagen"> </a>
    <!-- resto del código del producto -->
</div>
                        
                        <div class="producto-info">

                            <h3 class="producto-nombre"><?= htmlspecialchars($producto['nombre']) ?></h3>
                            
                            <div class="disponibilidad <?= ($producto['stock'] > 0) ? 'disponible' : 'agotado' ?>">
    <?php if ($producto['stock'] > 0): ?>
        <?= $producto['stock'] ."  ✅ " ?> unidades disponibles
    <?php else: ?>
        Agotado
    <?php endif; ?>
    </div>

<div class="producto-precio">$<?= number_format($producto['precio'], 2) ?></div>

<?php if ($producto['stock' ] <= 0): ?>
    <div class="alert alert-warning stock-alert">
        <i class="fas fa-exclamation-circle" ></i>  💔 Este producto está actualmente agotado
    </div >
<?php elseif ($producto['stock'] <= 5): ?>
    <?php 
    // Generar color HSL con tonos amarillos/anaranjados/rojos
    $hue = rand(0, 40); // Rango más amplio para más variedad (rojos a amarillos)
    $saturation = rand(80, 100); // Saturación alta para colores vibrantes
    $lightness = rand(50, 60); // Luminosidad media
    $randomColor = "hsl($hue, $saturation%, $lightness%)";
    ?>
    
     <div class="alert alert-warning stock-alert">
        <i class="fas fa-exclamation-triangle pulse-icon" style="color: <?= $randomColor ?>"></i>
        ¡Últimas unidades! Solo quedan <?= $producto['stock'] ?>
    </div><br>
    
 <?php endif; ?>
                            <button class="agregar-carrito" 
                                    data-id="<?= $producto['id'] ?>" 
                                    <?= $producto['stock'] <= 0 ? 'disabled' : '' ?>>
                                <?= ($producto['stock'] > 0) ? 'Añadir al carrito' : 'Agotado' ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="no-resultados">
                    <p>No se encontraron productos con los filtros seleccionados.</p><br>
                    <a href="productos.php" class="btn">Ver todos los productos</a>
                </div>
            <?php endif; ?>
        </div>
       
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
  
        // Agregar producto al carrito
    document.querySelectorAll('.agregar-carrito').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        const stockElement = this.closest('.producto-info').querySelector('.disponibilidad');

        const currentStock = parseInt(stockElement.textContent.match(/\d+/)[0]) || 0;
        
        if (currentStock <= 0) {
             button.disabled = true;
            button.textContent = 'Agotado';
            stockElement.innerHTML = 'Agotado';
            stockElement.classList.remove('disponible');
            stockElement.classList.add('agotado');
            
            showAlert('No hay suficiente stock disponible', 'error');
            return;

        }

        fetch('carrito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=agregar&id_producto=${productId}`
        })
        .then(response => response.text())
        .then(() => {
            // Actualizar el stock visualmente
            const newStock = currentStock - 1;
            stockElement.innerHTML = newStock > 0 ? 
                `${newStock} ✅ unidades disponibles` : 'Agotado';
            
            // Mostrar notificación de éxito
            showAlert('Producto añadido al carrito', 'success');
            
            // Desactivar botón si stock llega a cero
            if (newStock <= 0) {
                this.disabled = true;
                this.textContent = 'Agotado';
                localStorage.setItem(`producto_${productId}_agotado`, 'true');
                // Mostrar alerta de stock agotado
                showAlert('Este producto se ha agotado', 'warning');
                
                // Agregar clase de agotado
                stockElement.classList.remove('producto-card');
                stockElement.classList.remove('disponible');
                stockElement.classList.add('agotado');
            }
            
            // Actualizar contador del carrito
            if (typeof actualizarContadorCarrito === 'function') {
                actualizarContadorCarrito();
            }
        })
        .catch(error => {
            showAlert('Error al agregar el producto', 'error');
            console.error('Error:', error);
        });
    });
});

// Función para mostrar alertas
function showAlert(message, type) {
    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107'
    };
    
    const icon = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div style="position: fixed; bottom: 20px; right: 20px; 
            background: ${colors[type]}; color: white; 
            padding: 15px 25px; border-radius: 10px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex; align-items: center; z-index: 1000; 
            animation: slideIn 0.5s forwards;">
            <i class="fas ${icon[type]}" style="margin-right: 10px;"></i>
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'fadeOut 0.5s forwards';
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

        // Animaciones CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
    <script>
document.querySelectorAll('.producto-card').forEach(card => {
    card.addEventListener('mouseenter', () => card.classList.add('hover-active'));
    card.addEventListener('mouseleave', () => card.classList.remove('hover-active'));
});
</script>
</body>
</html>