<?php 
session_start();
include '../config/db.php';

// Recogemos el ID de la URL
$id_producto = isset($_GET['id']) ? $_GET['id'] : null;

// Si no hay ID, redirigimos a la portada
if (!$id_producto) {
    header("Location: ../index.php");
    exit();
}

// Buscamos el producto en la base de datos
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id_producto]);
$producto = $stmt->fetch();

// Si el ID no existe en la base de datos, redirigimos
if (!$producto) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - Zentek</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">

    <?php include '../includes/header.php'; ?>

    <main style="flex: 1; max-width: 1000px; margin: 50px auto; padding: 30px; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <div style="display: flex; gap: 50px; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 300px;">
                <?php if($producto['imagen']): ?>
                    <img src="../assets/img/productos/<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" style="width: 100%; border-radius: 12px; object-fit: cover;">
                <?php else: ?>
                    <div style="width: 100%; height: 350px; background: #f8fafc; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.2em;">
                        <i class="fas fa-camera" style="font-size: 2em; margin-bottom: 10px;"></i>
                        Sin foto
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column;">
                <h2 style="color: #0f172a; font-size: 2.2em; margin-top: 0; margin-bottom: 10px;"><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                <p class="precio" style="font-size: 2.5em; color: #2563eb; font-weight: 800; margin: 0 0 20px 0;"><?php echo number_format($producto['precio'], 2); ?> €</p>
                
                <p style="color: #64748b; font-size: 0.95em;"><i class="fas fa-box"></i> <strong>Stock disponible:</strong> <?php echo $producto['stock']; ?> unidades</p>
                
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;">
                
                <h3 style="color: #1e293b; margin-bottom: 15px;">Especificaciones Técnicas</h3>
                <p style="line-height: 1.7; color: #475569; margin-bottom: 30px; font-size: 1.05em;"><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
                
                <form method="POST" action="accion_carrito.php" style="margin-top: auto;">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                    <button type="submit" class="btn-comprar" style="width: 100%; padding: 16px; font-size: 1.1em; border-radius: 8px; background: #2563eb; color: white; border: none; font-weight: bold; cursor: pointer; display: flex; justify-content: center; gap: 10px; align-items: center; transition: 0.3s;">
                        <i class="fas fa-cart-plus"></i> Añadir al carrito
                    </button>
                </form>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>