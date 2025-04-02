<?php
require_once 'includes/db.php';
require_once 'includes/funciones.php';

if (!isset($_GET['id'])) {
    header('Location: productos.php');
    exit();
}

$producto_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = :id");
$stmt->bindParam(':id', $producto_id);
$stmt->execute();
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header('Location: productos.php');
    exit();
}
// Determinar el tipo de carta para la plantilla
$card_type = 'monstruo'; // Por defecto
$tipo = strtolower($producto['carta'] ?? '');

if (strpos($tipo, 'magia') !== false) {
    $card_type = 'magia';
} elseif (strpos($tipo, 'trampa') !== false) {
    $card_type = 'trampa';
} elseif (strpos($tipo, 'xyz') !== false) {
    $card_type = 'xyz';
} elseif (strpos($tipo, 'sincro') !== false || strpos($tipo, 'sincr') !== false) {
    $card_type = 'sincro';
} elseif (strpos($tipo, 'link') !== false) {
    $card_type = 'link';
} elseif (strpos($tipo, 'fusion') !== false) {
    $card_type = 'fusion';
} elseif (strpos($tipo, 'ritual') !== false) {
    $card_type = 'ritual';
} elseif (strpos($tipo, 'pendulo') !== false || strpos($tipo, 'péndulo') !== false) {
    $card_type = 'pendulo';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include 'includes/header.php'; ?>
    <title><?= htmlspecialchars($producto['nombre']) ?> - Tienda Yu-Gi-Oh!</title>
    <style>
        :root {
            --color-primary: #1a3e72;
            --color-secondary: #e8e8e8;
            --color-accent: #d32f2f;
            --card-width: 300px;
        }
        
        body {
            font-family: 'Matrix Book', 'Yu-Gi-Oh! Matrix', 'Arial Narrow', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
            background-image: url('assets/card-background.jpg');
            background-size: cover;
            background-color: yellow;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .card-display {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }
        
        /* Estilos base para todas las cartas */
       .yugioh-card {
        width: 300px;
        height: 420px;
        border-radius: 12px;
        position: relative;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        overflow: hidden;
        transition: transform 0.3s;
        background-color: #f8f8f8;
        border: 3px solid #333;
    }
    
    /* Estilos específicos para cada tipo de carta */
    .yugioh-card.monstruo {
        background-color: #F8C665;
        border-color: #d4af37;
    }
    
    .yugioh-card.magia {
        background-color: #c1e7ff;
        border-color: #1a8cff;
    }
    
    .yugioh-card.trampa {
        background-color: #ffcccc;
        border-color: #ff3333;
    }
    
    .yugioh-card.xyz {
        background-color: #000000;
        border-color: #9933ff;
    }
    
    .yugioh-card.sincro {
        background-color: #ffffff;
        border-color: #cccccc;
    }
    
    .yugioh-card.link {
        background-color: #000033;
        border-color: #0066cc;
    }
    
    .yugioh-card.fusion {
        background-color: #ffccff;
        border-color: #cc00cc;
    }
    
    .yugioh-card.ritual {
        background-color: #99ccff;
        border-color: #003366;
    }
    
    .yugioh-card.pendulo {
        background: linear-gradient(135deg, #f0e6d2 50%, #ffcccc 50%);
        border-color: #666666;
    }
        
        .yugioh-card:hover {
            transform: scale(1.03);
        }
        
        /* Plantillas específicas por tipo de carta */
        .card-monster {
            background-image: url('assets/card-template-monster.jpg');
            border: 3px solid #d4af37;
        }
        
        .card-spell {
            background-image: url('assets/card-template-spell.jpg');
            border: 3px solid #1a8cff;
        }
        
        .card-trap {
            background-image: url('assets/card-template-trap.jpg');
            border: 3px solid #ff3333;
        }
        
        .card-extra {
            background-image: url('assets/card-template-extra.jpg');
            border: 3px solid #9933ff;
        }
        
        .card-name {
            position: absolute;
            top: 5px;
            left: 0;
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            color: #000;
            padding: 0 15px;
            box-sizing: border-box;
            border-radius: 10px;
        }
        
        .card-image {
            border-radius: 15px;
            position: absolute;
            top: 50px;
            left: 10px;
            width: 275px;
            height: 210px;
            border: 1px solid #000;
            background-color: #fff;
            overflow: hidden;
        }
        
        .card-image img {
            width: 100%;
            height: 100%;
            
        }
        
        .card-attribute {
            
            top: 220px;
            right: 20px;
            width: 5%;
            height: 5%;
        }
        
        .card-level {
            position: absolute;
            top: 220px;
            left: 20px;
            display: flex;
            gap: 2px;
        }
        
        
        
        .card-description {
            border-radius: 15px;
            position: absolute;
            bottom: 50px;
            left: 10px;
             width: 275px;
            
            height: 100px;
            background-color: rgba(255,255,255,0.8);
            border: 1px solid #000;
            padding: 5px;
            font-size: 10px;
            overflow-y: auto;
        }
        
         .card-type {
        position: absolute;
        bottom: 30px;
        left: 15px;
        width: calc(100% - 30px);
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        padding: 2px 0;
        border-radius: 3px;
    }
      .card-level-indicator {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 0 80px;
        border-radius: 15px;
        
    }
     .yugioh-card.monstruo  .card-level-indicator {
        
        top: 24px;
        height: 30px;
       right: 20px;
    }
    
    /* Posición y dirección para cartas XYZ (izquierda, crece hacia derecha) */
    .yugioh-card.xyz .card-level-indicator {
        position: absolute;
        top: 24px;
        left:-74px;
        height: 30px;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 0 80px;
        border-radius: 15px;
    }
    
    .level-circle {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        position: relative;
        background: radial-gradient(circle at 30% 30%, #FFF, #FFD700);
        border: 1px solid #000;
        box-shadow: 0 0px 10px rgba(0,0,0,0.3);
    }
     .yugioh-card.xyz .level-circle {
           
        top: 0px;
        left:0px;
        width: 18px;
        height: 18px;
        position: relative;
        display: flex;
        align-items: center;
        border-radius: 50%;
    }
     .yugioh-card.monstruo .level-circle {
           
          top: 0px;
        left:90px;
         width: 18px;
        height: 18px;
        position: relative;
        display: flex;
        align-items: center;
        border-radius: 50%;
    }
    .yugioh-card .level-circle {
        
        background-color: darkred;
    }
    
    .level-circle::after {

        content: "★";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        font-size: 21px;

        text-shadow: 0 0 1px rgba(255,255,255,0.5);
    }
    /* Colores oscuros - texto blanco */
    .yugioh-card.link .card-type,.card-name,
    .yugioh-card.xyz  .card-type,
    .yugioh-card.fusion .card-type,
    .yugioh-card.ritual .card-type {

        color: white;
        text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
        background-color: rgba(0,0,0,0.3);
    }
    
    /* Colores claros - texto negro */
    .yugioh-card.monstruo  .card-type,
    .yugioh-card.magia .card-type,
    .yugioh-card.trampa .card-type,
    .yugioh-card.sincro .card-type,
    .yugioh-card.pendulo .card-type {
        color: black;
        text-shadow: 1px 1px 1px rgba(255,255,255,0.5);
        background-color: rgba(255,255,255,0.3);
    }
        
        .card-id {
            position: absolute;
            bottom: 5px;
            right: 20px;
            font-size: 10px;
            color: #000;
        }
        
        .card-details {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .card-price {
            font-size: 24px;
            color: var(--color-accent);
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        
        @media (max-width: 768px) {
            :root {
                --card-width: 250px;
            }
        }
    </style>
</head>
<body>
   <?php include 'includes/navbar.php'; ?>
    
    <main class="container">
        <div class="card-display">
            <div class="yugioh-card <?= $card_type ?>">
         
                <div class="card-name"><?= htmlspecialchars($producto['nombre']) ?></div>
         


   <?php if (in_array($card_type, ['monstruo', 'xyz'])): ?>
    <?php 
    $nivel = (int)($producto['nivel'] ?? 0);
    $max = ($card_type === 'xyz') ? 13 : 12;
    $nivel = min(max($nivel, 0), $max); // Asegurar entre 0 y máximo
    
    // Orden inverso para monstruos
    $show_levels = ($card_type === 'monstruo') ? range($nivel-1, 0, -1) : range(0, $nivel-1);
    ?>
    
    <div class="card-level-indicator">
        <?php foreach ($show_levels as $i): ?>
            <div class="level-circle" title="<?= ($card_type === 'xyz') ? 'xyz' : 'Nivel' ?> <?= $i+1 ?>"></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
                <div class="card-image">
                    <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                </div>
                
                <?php if ($card_type === 'monstruo' || $card_type === 'extra'): ?>
                    <div class="card-attribute">
                        <img src="assets/attributes/<?= strtolower($producto['atributo'] ?? 'light') ?>.png" alt="<?= $producto['atributo'] ?? '' ?>">
                    </div>
                    
                    <div class="card-level">
                        <?php 
                        $level = (int)($producto[''] ?? 0);
                        for ($i = 0; $i < $level; $i++): ?>
                            <div class="card-level-indicators"></div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
                
                <div class="card-description">
                    <?= nl2br(htmlspecialchars($producto['descripcion'])) ?>
                </div>
                
                <div class="card-type">
                    [<?= htmlspecialchars($producto['carta'] ?? '') ?>]
                </div>
                
                <div class="card-id">
                    <?= htmlspecialchars($producto['codigo_producto'] ?? '') ?>
                </div>
            </div>
        </div>
        
        <div class="card-details">
            <div class="card-price">
                Precio: $<?= number_format($producto['precio'], 2) ?>
            </div>
            
            <div class="producto-attribute">
                <div class="attribute-title">Disponibilidad</div>
                <div class="attribute-value"><?= ($producto['stock'] > 0) ? '<span style="color:green">En stock</span>' : '<span style="color:red">Agotado</span>' ?></div>
            </div>
            
            <div class="producto-attribute">
                <div class="attribute-title">Fecha de Lanzamiento</div>
                <div class="attribute-value"><?= !empty($producto['fecha_lanzamiento']) ? date('d/m/Y', strtotime($producto['fecha_lanzamiento'])) : 'N/A' ?></div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Puedes añadir aquí funcionalidad JavaScript si es necesario
        document.querySelector('.btn-comprar').addEventListener('click', function() {
            // Lógica para añadir al carrito
            alert('Producto añadido al carrito: <?= htmlspecialchars(addslashes($producto['nombre'])) ?>');
        });
    </script>
</body>
</html>