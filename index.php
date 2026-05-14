<?php
session_start();
include 'config/db.php';

// 1. Recogemos lo que el usuario ha escrito en el buscador
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// 2. Preparamos la consulta SQL dependiendo de si hay búsqueda o no
if (!empty($busqueda)) {
    // Si hay búsqueda: Buscamos en el nombre del producto O en el nombre de la categoría
    $sql = "SELECT p.*, c.nombre as nombre_categoria 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id 
            WHERE p.nombre LIKE :busqueda 
               OR c.nombre LIKE :busqueda 
            ORDER BY p.id DESC";
            
    $stmt = $pdo->prepare($sql);
    // Usamos comodines % para que busque la palabra en cualquier parte del texto
    $stmt->execute(['busqueda' => "%$busqueda%"]);
} else {
    // Si NO hay búsqueda: Mostramos todos los productos normalmente
    $sql = "SELECT * FROM productos ORDER BY id DESC";
    $stmt = $pdo->query($sql);
}

// 3. Guardamos los resultados en la variable $productos
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zentek - Drones y Robots</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <?php if(empty($busqueda)): ?>
    <header class="hero-section">
        <div class="hero-content">
            <h1>El futuro, hoy en tus manos</h1>
            <p>Descubre nuestra nueva colección de drones 4K y robótica avanzada. Innovación al alcance de todos.</p>
            <a href="blog/blog.php" class="btn-hero">Ver Blog</a>
        </div>
    </header>
    <?php endif; ?>

    <main class="contenedor-catalogo" id="catalogo">
        <h2 class="titulo-seccion">
            <?php echo !empty($busqueda) ? 'Resultados para "' . htmlspecialchars($busqueda) . '"' : 'Nuestro catálogo'; ?>
        </h2>
        
        <div class="grid-productos">
            <?php if (count($productos) > 0): ?>
            
                <?php foreach($productos as $prod): ?>
                    <div class="producto-card">
                        <a href="tienda/producto.php?id=<?php echo $prod['id']; ?>" class="img-container">
                            <?php if($prod['imagen']): ?>
                                <img src="assets/img/productos/<?php echo $prod['imagen']; ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>">
                            <?php else: ?>
                                <div class="img-placeholder" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#94a3b8; background: #f1f5f9;">
                                    <i class="fas fa-camera" style="font-size:2em; margin-bottom:10px;"></i>Sin foto
                                </div>
                            <?php endif; ?>
                        </a>
                        
                        <div class="producto-info">
                            <a href="tienda/producto.php?id=<?php echo htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration: none; color: inherit;">
                                <h3><?php echo htmlspecialchars($prod['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            </a>
                            <p class="precio"><?php echo htmlspecialchars(number_format($prod['precio'], 2), ENT_QUOTES, 'UTF-8'); ?> €</p>
                            <?php if (!empty($prod['especificaciones'])): ?>
                                <p class="producto-especificaciones"><?php echo nl2br(htmlspecialchars($prod['especificaciones'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php endif; ?>
                            <form action="tienda/accion_carrito.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($prod['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="accion" value="agregar">
                                <button type="submit" class="btn-comprar">
                                    <i class="fas fa-cart-plus"></i> Añadir al carrito
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                
            <?php else: ?>
                <div class="sin-resultados">
                    <i class="fas fa-search" style="font-size: 3em; color: #cbd5e1; margin-bottom: 20px; display: block;"></i>
                    <h2 style="color: #475569; margin-bottom: 10px;">No hemos encontrado resultados</h2>
                    <p style="color: #64748b; margin-bottom: 30px;">Prueba a buscar con otras palabras o navega por nuestras categorías.</p>
                    <a href="index.php" class="btn-comprar" style="max-width: 250px; margin: 0 auto; text-decoration: none;">Ver todo el catálogo</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>