<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// 1. Barrera de seguridad: ¿Está logueado? 
// CORREGIDO: Ahora el login está en ../auth/login.php
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php?error=carrito");
    exit();
}

// 2. Barrera de seguridad: ¿Tiene algo en el carrito?
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header("Location: ../index.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];
$subtotal = 0;

// 3. Calculamos el subtotal real preguntando a la BD y verificamos stock
foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
    $stmt = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ?");
    $stmt->execute([$id_prod]);
    $producto = $stmt->fetch();
    
    if ($producto) {
        if ($producto['stock'] < $cantidad) {
            die("Stock insuficiente para el producto ID: $id_prod");
        }
        $subtotal += $producto['precio'] * $cantidad;
    } else {
        die("Producto no encontrado: $id_prod");
    }
}

// --- MAGIA DE CUPONES ---
$total_compra = $subtotal;
$descuento_aplicado = 0;
$codigo_usado = '';

if (isset($_SESSION['cupon']) && $subtotal > 0) {
    $codigo_usado = $_SESSION['cupon']['codigo'];
    
    if ($_SESSION['cupon']['tipo'] == 'porcentaje') {
        $descuento_aplicado = $subtotal * ($_SESSION['cupon']['descuento'] / 100);
    } else {
        $descuento_aplicado = $_SESSION['cupon']['descuento'];
    }
    
    if ($descuento_aplicado > $subtotal) {
        $descuento_aplicado = $subtotal;
    }
    
    $total_compra = $subtotal - $descuento_aplicado;
}

// 4. CREAMOS EL PEDIDO PRINCIPAL Y DETALLES CON TRANSACCIÓN
try {
    $pdo->beginTransaction();
    
    $stmt_pedido = $pdo->prepare("INSERT INTO pedidos (id_usuario, total, estado) VALUES (?, ?, 'Completado')");
    $stmt_pedido->execute([$id_usuario, $total_compra]);
    $id_pedido = $pdo->lastInsertId();

    // 5. GUARDAMOS LOS DETALLES Y RESTAMOS EL STOCK
    foreach ($_SESSION['carrito'] as $id_prod => $cantidad) {
        $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmt->execute([$id_prod]);
        $producto = $stmt->fetch();

        if ($producto) {
            $stmt_detalle = $pdo->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
            $stmt_detalle->execute([$id_pedido, $id_prod, $cantidad, $producto['precio']]);

            $stmt_stock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt_stock->execute([$cantidad, $id_prod]);
        }
    }
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error procesando pedido: " . $e->getMessage());
}

// 6. VACIAMOS EL CARRITO Y EL CUPÓN
unset($_SESSION['carrito']);
unset($_SESSION['cupon']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Zentek - Pedido Confirmado</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
    <style>
        .checkout-box { text-align: center; padding: 60px 20px; }
        .icon-success { font-size: 5em; color: #10b981; margin-bottom: 20px; animation: popIn 0.5s ease-out; }
        @keyframes popIn { 0% { transform: scale(0); } 80% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .resumen-pedido { background: #f8f9fa; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin: 30px auto; max-width: 400px; text-align: left; }
    </style>
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh;">

    <main class="contenedor-perfil" style="max-width: 800px; display: flex; align-items: center; justify-content: center; flex: 1; margin: 40px auto; min-height: 60vh; padding: 0 20px;">
        <div class="tarjeta-perfil checkout-box" style="width: 100%; background: white; border-radius: 15px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
            
            <div class="icon-success">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 style="color: #0f172a; margin-bottom: 10px;">¡Pago Completado!</h1>
            <p style="color: #64748b; font-size: 1.1em;">Tu pedido ha sido procesado correctamente y ya estamos preparándolo.</p>

            <div class="resumen-pedido">
                <p style="margin: 0 0 10px 0; color: #475569;"><strong>Número de Pedido:</strong> <span style="float: right; color: #0f172a;">#<?php echo $id_pedido; ?></span></p>
                <p style="margin: 0 0 10px 0; color: #475569;"><strong>Fecha:</strong> <span style="float: right; color: #0f172a;"><?php echo date('d/m/Y'); ?></span></p>
                
                <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 15px 0;">
                
                <p style="margin: 0 0 10px 0; color: #475569;">Subtotal: <span style="float: right; color: #0f172a;"><?php echo number_format($subtotal, 2); ?> €</span></p>
                
                <?php if ($descuento_aplicado > 0): ?>
                    <p style="margin: 0 0 10px 0; color: #16a34a;">Descuento (<?php echo htmlspecialchars($codigo_usado); ?>): <span style="float: right;">- <?php echo number_format($descuento_aplicado, 2); ?> €</span></p>
                <?php endif; ?>

                <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 15px 0;">
                
                <p style="margin: 0; font-size: 1.2em; color: #0f172a;"><strong>Total Pagado:</strong> <span style="float: right; color: #2563eb; font-weight: bold;"><?php echo number_format($total_compra, 2); ?> €</span></p>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="../auth/perfil.php" class="btn-submit" style="width: auto; padding: 12px 25px; text-decoration: none; background: #2563eb; color: white; border-radius: 5px; font-weight: bold;"><i class="fas fa-receipt"></i> Ver mis pedidos</a>
                <a href="../index.php" style="margin: 0; padding: 12px 25px; background: #e2e8f0; color: #0f172a; text-decoration: none; border-radius: 5px; font-weight: bold;"><i class="fas fa-store"></i> Volver a la tienda</a>
            </div>

        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>