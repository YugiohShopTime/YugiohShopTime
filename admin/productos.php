<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

// Procesar acciones (añadir/editar/eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'agregar':
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $precio = $_POST['precio'];
                $stock = $_POST['stock'];
                $categoria = $_POST['categoria'];
                $destacado = isset($_POST['destacado']) ? 1 : 0;
                
                // Subir imagen
                $imagen = 'default.jpg';
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $imagen = uniqid() . '.' . $extension;
                    move_uploaded_file($_FILES['imagen']['tmp_name'], "$imagen");
                }
                
                $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, categoria, imagen, destacado) 
                                      VALUES (:nombre, :descripcion, :precio, :stock, :categoria, :imagen, :destacado)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':imagen', $imagen);
                $stmt->bindParam(':destacado', $destacado);
                $stmt->execute();
                
                $_SESSION['mensaje'] = 'Producto añadido correctamente';
                break;
                
            case 'editar':
                $id = $_POST['id'];
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $precio = $_POST['precio'];
                $stock = $_POST['stock'];
                $categoria = $_POST['categoria'];
                $destacado = isset($_POST['destacado']) ? 1 : 0;
                
                // Actualizar imagen si se subió una nueva
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $imagen = uniqid() . '.' . $extension;
                    //move_uploaded_file($_FILES['imagen']['tmp_name'], "../images/productos/$imagen");
                    move_uploaded_file($_FILES['imagen']['tmp_name'], "$imagen");
                    
                    // Eliminar imagen anterior si no es la default
                    $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($producto['imagen'] !== 'default.jpg') {
                        //@unlink("../images/productos/{$producto['imagen']}");
                        @unlink("{$producto['imagen']}");
                    }
                    
                    $stmt = $conn->prepare("UPDATE productos SET imagen = :imagen WHERE id = :id");
                    $stmt->bindParam(':imagen', $imagen);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                
                $stmt = $conn->prepare("UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, 
                                      stock = :stock, categoria = :categoria, destacado = :destacado WHERE id = :id");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':categoria', $categoria);
                $stmt->bindParam(':destacado', $destacado);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $_SESSION['mensaje'] = 'Producto actualizado correctamente';
                break;
                
            case 'eliminar':
                $id = $_POST['id'];
                
                // Eliminar imagen si no es la default
                $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($producto['imagen'] !== 'default.jpg') {
                    @unlink("../images/productos/{$producto['imagen']}");
                }
                
                $stmt = $conn->prepare("DELETE FROM productos WHERE id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                $_SESSION['mensaje'] = 'Producto eliminado correctamente';
                break;
        }
        
        header('Location: productos.php');
        exit();
    }
}

// Obtener todos los productos
$stmt = $conn->query("SELECT * FROM productos ORDER BY nombre");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Productos - Yu-Gi-Oh! Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .productos-table th, .productos-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .productos-table th {
            background: #f5f5f5;
        }
        
        .productos-table img {
            width: 90px;
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
        }
        
        .btn {
            padding: 5px 10px;
            font-size: 0.9rem;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
      .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 103%;
    border-radius: 25px;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    overflow: auto;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    
    max-width: 700px;
    margin: 0px auto;
    box-sizing: border-box;
}

.form-group {
    margin-bottom: 2px;
}

.form-group label {
    display: block;
    font-weight: 600;
    
}

.form-group input, 
.form-group textarea, 
.form-group select {
    width: 100%;
    padding: 3px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Para pantallas más grandes */
@media (min-width: 768px) {
    .modal-content {
        padding: -25px;
    }
}
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
         
        .mensaje {
            padding: 10px;
            background: #28a745;
            color: white;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
           .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
        }
        
        .recent-sales {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .recent-sales h2 {
            margin-bottom: 20px;
            color: var(--secondary-color);
        }
        
        .sales-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .sales-table th, .sales-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .sales-table th {
            background: #f5f5f5;
        }
        
        .admin-nav {
            background: var(--dark-color);
            padding: 15px 0;
        }
        
        .admin-nav ul {
            display: flex;
            list-style: none;
            justify-content: center;
        }
        
        .admin-nav li {
            margin: 0 15px;
        }
        
        .admin-nav a {
            color: white;
            font-weight: 500;
        }
        
        .admin-nav a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    
    
    <nav class="admin-nav">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="productos.php">Productos</a></li>
            <li><a href="ventas.php">Ventas</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1>Administrar Productos</h1>
            <button class="btn btn-primary" onclick="abrirModal('agregar')">Agregar Producto</button>
        </div>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <table class="productos-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Destacado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><img src="../images/productos/<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>"></td>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td>$<?= number_format($producto['precio'], 2) ?></td>
                        <td><?= $producto['stock'] ?></td>
                        <td><?= ucfirst($producto['categoria']) ?></td>
                        <td><?= $producto['destacado'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="abrirModal('editar', <?= $producto['id'] ?>)">Editar</button>
                            <button class="btn btn-delete" onclick="confirmarEliminar(<?= $producto['id'] ?>)">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal para agregar/editar productos -->
    <div class="modal" id="productoModal">
        <div class="modal-content">
            <h2 id="modalTitulo">Agregar Producto</h2>
            <form id="productoForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="accion" id="accion" value="agregar">
                <input type="hidden" name="id" id="productoId">
                
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <select id="categoria" name="categoria" required>
                        <option value="booster">Booster Pack</option>
                        <option value="structure">Structure Deck</option>
                        <option value="sencillas">Cartas Sencillas</option>
                        <option value="accesorios">Accesorios</option>
                    </select>
                </div>
                
                <div class="form-group">
                <li><a><label>
                        <input  id="destacado" name="destacado" type="checkbox"> Destacado
                    </label></a></li>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <div id="imagenPreview" style="margin-top: 10px;"></div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Formulario oculto para eliminar -->
    <form id="eliminarForm" method="post">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="eliminarId">
    </form>
    
    <script>
        function abrirModal(accion, id = null) {
            const modal = document.getElementById('productoModal');
            const form = document.getElementById('productoForm');
            const titulo = document.getElementById('modalTitulo');
            const accionInput = document.getElementById('accion');
            const productoId = document.getElementById('productoId');
            const imagenPreview = document.getElementById('imagenPreview');
            
            // Limpiar formulario
            form.reset();
            imagenPreview.innerHTML = '';
            
            if (accion === 'agregar') {
                titulo.textContent = 'Agregar Producto';
                accionInput.value = 'agregar';
            } else if (accion === 'editar' && id) {
                titulo.textContent = 'Editar Producto';
                accionInput.value = 'editar';
                productoId.value = id;
                
                // Obtener datos del producto (simulado, en realidad deberías hacer una petición AJAX)
                const producto = <?= json_encode($productos) ?>.find(p => p.id == id);
                if (producto) {
                    document.getElementById('nombre').value = producto.nombre;
                    document.getElementById('descripcion').value = producto.descripcion || '';
                    document.getElementById('precio').value = producto.precio;
                    document.getElementById('stock').value = producto.stock;
                    document.getElementById('categoria').value = producto.categoria;
                    document.getElementById('destacado').checked = producto.destacado == 1;
                    
                    // Mostrar imagen actual
                    if (producto.imagen) {
                        ///imagenPreview.innerHTML = `<img src="../images/productos/${producto.imagen}" width="100">`;
                        imagenPreview.innerHTML = `<img src="${producto.imagen}" width="100">`;
                    }
                }
            }
            
            modal.style.display = 'flex';
        }
        
        function cerrarModal() {
            document.getElementById('productoModal').style.display = 'none';
        }
        
        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de eliminar este producto?')) {
                document.getElementById('eliminarId').value = id;
                document.getElementById('eliminarForm').submit();
            }
        }
        
        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            const modal = document.getElementById('productoModal');
            if (event.target === modal) {
                cerrarModal();
            }
        }
        
        // Previsualizar imagen seleccionada
        document.getElementById('imagen').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagenPreview').innerHTML = `<img src="${e.target.result}" width="100">`;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>