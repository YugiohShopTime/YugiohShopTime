<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/funciones.php';

if (!isset($_SESSION['venta_id'])) {
    header('Location: productos.php');
    exit();
}

$venta_id = $_SESSION['venta_id'];
unset($_SESSION['venta_id']);

// Obtener información de la venta
$stmt = $conn->prepare("SELECT v.*, vd.producto_id, vd.cantidad, vd.precio_unitario, p.nombre as producto_nombre 
                       FROM ventas v
                       JOIN venta_detalles vd ON v.id = vd.venta_id
                       JOIN productos p ON vd.producto_id = p.id
                       WHERE v.id = :venta_id");
$stmt->bindParam(':venta_id', $venta_id);
$stmt->execute();
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($detalles)) {
    die("No se encontró la venta especificada");
}

$venta = $detalles[0]; // La primera fila contiene los datos de la venta
?>

<!DOCTYPE html>
<html lang="es">
<head>
    
    <title>Factura #<?= $venta_id ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .btn {
    display: inline-block;
    background: var(--primary-color);
    color: #000;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #e6b800;
    transform: translateY(-2px);
}

.btn-primary {
    background: var(--secondary-color);
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.carrito-icon {
    position: relative;
}

.carrito-count {
    position: absolute;
    top: -10px;
    right: -10px;
    background: var(--danger-color);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 0.8rem;
}

/* Hero section */
.hero {
    background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../images/yugioh-bg.jpg');
    background-size: cover;
    background-position: center;
    color: white;
    text-align: center;
    padding: 80px 20px;
    border-radius: 10px;
    margin: 20px 0;
}

.hero h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.hero p {
    font-size: 1.2rem;
    margin-bottom: 30px;
}

/* Productos */
.destacados {
    margin: 40px 0;
}



.productos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 40px;
}

.producto-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.producto-card:hover {
    transform: translateY(-5px);
}

.producto-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.producto-card h3 {
    padding: 10px 15px;
    font-size: 1.1rem;
}

.producto-card .precio {
    padding: 0 15px;
    font-weight: bold;
    font-size: 1.2rem;
    color: var(--secondary-color);
}

.disponibilidad {
    padding: 5px 15px;
}

.disponible {
    color: var(--success-color);
    font-weight: bold;
}

.agotado {
    color: var(--danger-color);
    font-weight: bold;
}

.producto-card .btn {
    margin: 15px;
    width: calc(100% - 30px);
}

/* Carrito */
.carrito-items {
    margin: 20px 0;
}

.carrito-item {
    display: flex;
    background: #962dd45c;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: relative;
}

.carrito-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
    margin-right: 15px;
}

.item-info {
    flex-grow: 1;
}

.carrito-item h3 {
    margin-bottom: 10px;
    color: aliceblue;
}

.carrito-item .eliminar {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    color: var(--danger-color);
    cursor: pointer;
    font-size: 1.2rem;
}

.carrito-total {
    text-align: right;
    font-size: 1.3rem;
    margin: 20px 0;
}

.carrito-total span {
    font-weight: bold;
    color: var(--secondary-color);
}

.carrito-acciones {
    display: flex;
    justify-content: space-between;
}

/* Checkout */
.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.info-envio, .resumen-compra {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input, 
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: inherit;
}

.resumen-items {
    margin: 20px 0;
}

.resumen-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.resumen-total {
    display: flex;
    justify-content: space-between;
    font-size: 1.2rem;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

/* Mensajes */
.mensaje {
    padding: 10px 15px;
    background: var(--success-color);
    color: white;
    border-radius: 5px;
    margin-bottom: 20px;
}

.errores {
    padding: 15px;
    background: #f8d7da;
    color: #721c24;
    border-radius: 5px;
    margin-bottom: 20px;
}

/* Footer */
footer {
    background: var(--dark-color);
    color: white;
    text-align: center;
    padding: 30px 0;
    margin-top: 50px;
}

.footer-links {
    display: flex;
    justify-content: center;
    list-style: none;
    margin: 20px 0;
}

.footer-links li {
    margin: 0 15px;
}

.footer-links a {
    color: white;
}

.footer-links a:hover {
    color: var(--primary-color);
}

.social-icons {
    margin: 20px 0;
}

.social-icons a {
    color: white;
    margin: 0 10px;
    font-size: 1.5rem;
}
@media (max-width: 768px) {
    /* Mover los iconos al header en móvil */
    .mobile-tooltips {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 10px 15px;
        background: var(--header-bg-color); /* Ajusta según tu diseño */
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    /* Ocultar los originales en móvil */
    .desktop-tooltips {
        display: none;
    }
    
    /* Estilos para los iconos en móvil */
    .mobile-tooltips .fa-tooltip {
        font-size: 1.2rem;
        color: var(--text-color); /* Ajusta el color */
    }
}

@media (min-width: 769px) {
    .mobile-tooltips {
        display: none;
    }
    
    .desktop-tooltips {
        display: block;
    }
}
/* Responsive */
@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .productos-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
    
    .carrito-item {
        flex-direction: column;
    }
    
    .carrito-item img {
        width: 100%;
        height: auto;
        margin-bottom: 15px;
    }
}
/* ======================
   Tooltip de Usuario
   ====================== */

.user-tooltip {
    position: relative;
    display: inline-block;
}

.user-tooltip .tooltip-text {
    visibility: hidden;
    width: auto;
    background-color: rgba(0,0,0,0.7);
    color: #FFD166; /* Color principal amarillo-naranja */
    text-align: center;
    border-radius: 6px;
    padding: 5px 10px;
    position: absolute;
    z-index: 1000;
    bottom: 15%;
    left: 100%;
    transform: translateX(-50%);
    margin-left: 10px;
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.8rem;
    white-space: nowrap;
    font-weight: 100;
    pointer-events: none;
}

.user-tooltip .tooltip-text::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: rgba(0,0,0,0.7) transparent transparent transparent;
}

.user-tooltip:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}

/* Icono de usuario */
.user-icon {
    transition: all 0.3s ease;
    color: white;
}

.user-icon:hover {
    color: #FFD166;
    transform: scale(1.1);
}

/* Variantes de color (opcionales) */
.user-tooltip.orange-intense .tooltip-text {
    color: #FFB347;
}

.user-tooltip.light-yellow .tooltip-text {
    color: #FFDE7D;
}

.user-tooltip.custom-color .tooltip-text {
    color: #FDB750;
}
.user-float {
    position: fixed; /* Cambiado de absolute a fixed para mejor posicionamiento */
    bottom: 20px; /* Distancia desde la parte inferior */
    right: 25px; /* Distancia desde la derecha */
    z-index: 1000;
}
/* Ajustar posición del tooltip cuando está en la parte inferior */
.user-float .user-tooltip .tooltip-text {
    bottom: auto;
    top: 100%;
    margin-top: 10px;
}

.user-float .user-tooltip .tooltip-text::after {
    top: auto;
    bottom: 100%;
    border-color: transparent transparent rgba(0,0,0,0.8) transparent;
}
        
        .factura-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .factura-info div {
            width: 48%;
        }
        .factura-cliente {
            margin-bottom: 15px;
        }
        .factura-cliente h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .factura-detalles {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .factura-detalles th, .factura-detalles td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        .factura-detalles th {
            background-color: #C32222;
            font-size: 11px;
        }
        .factura-detalles td {
            background-color: #35C2C9;
            font-size: 11px;
        }
         .factura-detalles h1 {
            background-color: ##54EC3A;
            font-size: 11px;
        }
        .factura-detalles h2 {
            background-color: #C32222;
            font-size: 11px;
        }
        .factura-detalles p {
            background-color: #483AEC;
            font-size: 11px;
        }
         .factura-detalles strong {
            background-color: #C523DC;
            font-size: 11px;
        }
        .factura-total {
            text-align: right;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .factura-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .botones-factura {
            margin-top: 20px;
            text-align: center;
        }
        .oxxo-referencia {
            margin-bottom: 15px;
            font-size: 11px;
        }
        .oxxo-referencia h3 {
            font-size: 13px;
            margin-bottom: 5px;
        }
        @media print {
            .botones-factura {
                display: none;
            }
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    
    
    <main class="container">
        <div class="factura-container" id="factura">
            
                <h1>PizzaEX Shop</h1>
                <h2>Factura #<?= 'PizzaEX' . $venta_id . '-' . strtoupper(substr(md5(mt_rand()), 0, 4)) ?></h2>
                <p>Fecha: <?= date('d/m/Y', strtotime($venta['fecha'])) ?></p>
            </div>
            
            <div class="factura-info">
                <div>
                    <p><strong>Tienda Yu-Gi-Oh!</strong></p>
                    <p>Dirección: Calle Duelista 123, Ciudad</p>
                    <p>Teléfono: (123) 456-7890</p>
                </div>
                <div>
                    <p><strong>Método de Pago:</strong> <?= ucfirst($venta['metodo_pago']) ?></p>
                    <p><strong>Estado:</strong> <?= $venta['metodo_pago'] === 'oxxo' ? 'Pendiente de pago' : 'Pendiente' ?></p>
                </div>
            </div>
            
            <div class="factura-cliente">
                <h3>Datos del Cliente</h3>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($venta['nombre_cliente']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($venta['email']) ?></p>
                <p><strong>Dirección de envío:</strong> <?= htmlspecialchars($venta['direccion']) ?></p>
            </div>
            
            <table class="factura-detalles">
                <thead>
                    <tr>
                        <th width="50%">Producto</th>
                        <th width="15%">Cantidad</th>
                        <th width="20%">Precio Unitario</th>
                        <th width="15%">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $detalle): ?>
                        <tr>
                            <td><?= htmlspecialchars($detalle['producto_nombre']) ?></td>
                            <td><?= $detalle['cantidad'] ?></td>
                            <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                            <td>$<?= number_format($detalle['precio_unitario'] * $detalle['cantidad'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="factura-total">
                <p>Total: $<?= number_format($venta['total'], 2) ?></p>
            </div>
            
            <?php if ($venta['metodo_pago'] === 'oxxo'): ?>
                <div class="oxxo-referencia">
                    <h3>Instrucciones para pago en OXXO</h3>
                    <p>1. Acude a cualquier tienda OXXO</p>
                    <p>2. Proporciona la siguiente referencia: <strong>YUGO<?= str_pad($venta_id, 6, '0', STR_PAD_LEFT) ?></strong></p>
                    <p>3. Realiza el pago por $<?= number_format($venta['total'], 2) ?></p>
                    <p>4. Guarda tu ticket de pago</p>
                    <p>Una vez confirmado tu pago, procesaremos tu pedido.</p>
                </div>
            <?php endif; ?>
            
            <div class="factura-footer">
                <p>¡Gracias por tu compra!</p>
                <p>Para cualquier duda o aclaración, contáctanos en contacto@tiendayugioh.com</p>
            </div>
        </div>
        
        <div class="botones-factura">
            <button onclick="window.print()" class="btn">Imprimir Factura</button>
            <a href="productos.php" class="btn">Volver a la Tienda</a>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function descargarPDF() {
            const element = document.getElementById('factura');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'factura_yugioh_<?= $venta_id ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { 
                    scale: 2,
                    letterRendering: true,
                    useCORS: true
                },
                jsPDF: { 
                    unit: 'mm', 
                    format: 'a4', 
                    orientation: 'portrait',
                    compress: true
                },
                pagebreak: { mode: 'avoid-all' }
            };
            
            // Configuración adicional para manejar múltiples páginas si es necesario
            const pdfOptions = {
                ...opt,
                onclone: function(clonedDoc) {
                    clonedDoc.querySelector('.factura-container').style.padding = '5mm';
                }
            };
            
            html2pdf().set(pdfOptions).from(element).save();
        }
    </script>
</body>
</html>