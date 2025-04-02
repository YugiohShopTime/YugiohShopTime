<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

verificarAdmin();

// Procesar formulario para añadir categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_categoria'])) {
    $nombre = trim($_POST['nombre']);
    $slug = strtolower(str_replace(' ', '-', $nombre));
    $descripcion = trim($_POST['descripcion'] ?? '');

    $stmt = $conn->prepare("INSERT INTO categorias (nombre, slug, descripcion) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $slug, $descripcion]);
    
    $_SESSION['exito'] = "Categoría agregada correctamente";
    header('Location: categorias.php');
    exit();
}

// Obtener todas las categorías
$categorias = obtenerCategorias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <?php include '../includes/header.php'; ?>
    <title>Administrar Categorías</title>
</head>
<body>
    <?php include '../includes/navbar-admin.php'; ?>
    
    <div class="admin-container">
        <h1>Administrar Categorías</h1>
        
        <!-- Formulario para añadir nueva categoría -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Agregar Nueva Categoría</h2>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="nombre">Nombre de la categoría</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción (opcional)</label>
                        <textarea id="descripcion" name="descripcion" class="form-control"></textarea>
                    </div>
                    <button type="submit" name="agregar_categoria" class="btn btn-primary">Agregar Categoría</button>
                </form>
            </div>
        </div>
        
        <!-- Listado de categorías existentes -->
        <div class="card">
            <div class="card-header">
                <h2>Categorías Existentes</h2>
            </div>
            <div class="card-body">
                <?php if (empty($categorias)): ?>
                    <p>No hay categorías registradas.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Slug</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $cat): ?>
                                    <tr>
                                        <td><?= $cat['id'] ?></td>
                                        <td><?= htmlspecialchars($cat['nombre']) ?></td>
                                        <td><?= htmlspecialchars($cat['slug']) ?></td>
                                        <td><?= htmlspecialchars($cat['descripcion']) ?></td>
                                        <td>
                                            <a href="editar-categoria.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                            <a href="eliminar-categoria.php?id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>