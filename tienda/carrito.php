<?php
session_start();
include '../config/db.php';

// --- 1. LÓGICA PARA AÑADIR/QUITAR PRODUCTOS DEL CARRITO ---
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar'])) {
    $id_producto = $_POST['id_producto'];
    
    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]++;
    } else {
        $_SESSION['carrito'][$id_producto] = 1;
    }
    header("Location: carrito.php");
    exit();
}

// --- 2. LÓGICA DE CUPONES PROMOCIONALES ---
$mensaje_cupon = '';
$error_cupon = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aplicar_cupon'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_cupon = "Token de seguridad inválido.";
    } else {
        $codigo_introducido = strtoupper(trim($_POST['codigo_cupon']));
        
        // Búsqueda del cupón
        $stmt = $pdo->prepare("SELECT * FROM cupones WHERE codigo = ? AND fecha_expiracion >= CURDATE()");
        $stmt->execute([$codigo_introducido]);
        $cupon_valido = $stmt->fetch();

        if ($cupon_valido) {
            $_SESSION['cupon'] = [
                'codigo' => $cupon_valido['codigo'],
                'descuento' => $cupon_valido['descuento'],
                'tipo' => $cupon_valido['tipo']
            ];
            $mensaje_cupon = "¡Cupón aplicado con éxito!";
        } else {
            $error_cupon = "El código no es válido o ha caducado.";
        }
    }
}

if (isset($_GET['quitar_cupon'])) {
    unset($_SESSION['cupon']);
    header("Location: carrito.php");
    exit();
}

// --- 3. CÁLCULO DE TOTALES ---
$subtotal = 0;
$total_final = 0;
$descuento_aplicado = 0;
$productos_carrito = [];

if (count($_SESSION['carrito']) > 0) {
    $ids = array_keys($_SESSION['carrito']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $productos_carrito = $stmt->fetchAll();

    foreach ($productos_carrito as $prod) {
        $cantidad = $_SESSION['carrito'][$prod['id']];
        $subtotal += $prod['precio'] * $cantidad;
    }
}

if (isset($_SESSION['cupon']) && $subtotal > 0) {
    if ($_SESSION['cupon']['tipo'] == 'porcentaje') {
        $descuento_aplicado = $subtotal * ($_SESSION['cupon']['descuento'] / 100);
    } else {
        $descuento_aplicado = $_SESSION['cupon']['descuento'];
    }
    
    if ($descuento_aplicado > $subtotal) {
        $descuento_aplicado = $subtotal;
    }
}

$total_final = $subtotal - $descuento_aplicado;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - Zentek</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
    <style>
        .carrito-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        .resumen-caja { background: #f8fafc; padding: 25px; border-radius: 10px; border: 1px solid #e2e8f0; position: sticky; top: 20px; height: fit-content; }
        .cupon-form { display: flex; gap: 10px; margin: 20px 0; }
        .cupon-form input { flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; text-transform: uppercase; outline: none; }
        .btn-aplicar { background: #0f172a; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .btn-pagar { display: block; width: 100%; text-align: center; margin-top: 25px; padding: 15px; font-size: 1.1em; text-decoration: none; background: #2563eb; color: white; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; transition: 0.3s; }
        .btn-pagar:hover { background: #1d4ed8; transform: translateY(-2px); }

        /* MAGIA RESPONSIVE */
        @media (max-width: 768px) {
            .carrito-grid { grid-template-columns: 1fr; }
            .tabla-carrito-responsive, .tabla-carrito-responsive thead, .tabla-carrito-responsive tbody, .tabla-carrito-responsive tr, .tabla-carrito-responsive td {
                display: block; width: 100%;
            }
            .tabla-carrito-responsive thead { display: none; }
            .tabla-carrito-responsive tr { margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 15px; padding: 10px; background: white; }
            .tabla-carrito-responsive td { display: flex; justify-content: space-between; align-items: center; padding: 12px 5px; border-bottom: 1px solid #f1f5f9; text-align: right; }
            .tabla-carrito-responsive td:last-child { border-bottom: none; }
            .tabla-carrito-responsive td::before { content: attr(data-label); font-weight: 800; color: #64748b; font-size: 0.75em; text-transform: uppercase; text-align: left; }
            .img-carrito { width: 40px !important; height: 40px !important; }
        }
    </style>
</head>
<body style="display: flex; flex-direction: column; min-height: 100vh; background: #f8fafc;">

    <?php include '../includes/header.php'; ?>

    <main style="flex: 1; max-width: 1100px; margin: 40px auto; padding: 0 20px; width: 100%; box-sizing: border-box;">
        <h1 style="margin-bottom: 30px; color: #0f172a; font-weight: 800;"><i class="fas fa-shopping-basket"></i> Tu Carrito</h1>

        <?php if($mensaje_cupon): ?>
            <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #bbf7d0;"><i class="fas fa-check-circle"></i> <?php echo $mensaje_cupon; ?></div>
        <?php endif; ?>
        <?php if($error_cupon): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #fecaca;"><i class="fas fa-exclamation-circle"></i> <?php echo $error_cupon; ?></div>
        <?php endif; ?>

        <?php if (empty($productos_carrito)): ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <i class="fas fa-shopping-cart" style="font-size: 4em; color: #cbd5e1; margin-bottom: 20px;"></i>
                <h2 style="color: #1e293b;">Tu carrito está muy solo</h2>
                <p style="color: #64748b; margin-top: 10px;">¡Seguro que hay algún dron esperándote en la tienda!</p>
                <br>
                <a href="../index.php" style="display: inline-block; padding: 12px 30px; text-decoration: none; background: #2563eb; color: white; border-radius: 8px; font-weight: bold;">Ir a la tienda</a>
            </div>
        <?php else: ?>
            <div class="carrito-grid">
                
                <div>
                    <table class="tabla-carrito-responsive" style="width: 100%; border-collapse: collapse; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                        <thead style="background: #0f172a; color: white;">
                            <tr>
                                <th style="padding: 18px; text-align: left;">Producto</th>
                                <th style="padding: 18px; text-align: center;">Cantidad</th>
                                <th style="padding: 18px; text-align: right;">Total</th>
                                <th style="padding: 18px; text-align: center; width: 60px;"></th> 
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_carrito as $prod): ?>
                            <tr>
                                <td data-label="Producto" style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                                    <?php if($prod['imagen']): ?>
                                        <img src="../assets/img/productos/<?php echo $prod['imagen']; ?>" class="img-carrito" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px; border: 1px solid #f1f5f9;">
                                    <?php endif; ?>
                                    <span style="color: #1e293b; font-weight: 700;"><?php echo htmlspecialchars($prod['nombre']); ?></span>
                                </td>
                                <td data-label="Cantidad" style="padding: 15px; text-align: center; font-weight: 800; color: #475569;">
                                    x <?php echo $_SESSION['carrito'][$prod['id']]; ?>
                                </td>
                                <td data-label="Total" style="padding: 15px; text-align: right; font-weight: 800; color: #0f172a;">
                                    <?php echo number_format($prod['precio'] * $_SESSION['carrito'][$prod['id']], 2); ?> €
                                </td>
                                <td data-label="Eliminar" style="padding: 15px; text-align: center;">
                                    <form action="accion_carrito.php" method="POST" style="margin: 0; display: inline-block;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id_producto" value="<?php echo $prod['id']; ?>">
                                        <button type="submit" title="Eliminar producto" style="background: #fee2e2; color: #dc2626; border: none; padding: 10px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='#fca5a5'" onmouseout="this.style.background='#fee2e2'">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top: 25px;">
                        <a href="../index.php" style="color: #64748b; text-decoration: none; font-weight: 600; transition: 0.3s;"><i class="fas fa-chevron-left"></i> Seguir comprando</a>
                    </div>
                </div>

                <div class="resumen-caja">
                    <h3 style="border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; color: #0f172a; font-weight: 800;">Resumen del Pedido</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: #475569;">
                        <span>Subtotal:</span>
                        <strong style="color: #1e293b;"><?php echo number_format($subtotal, 2); ?> €</strong>
                    </div>

                    <?php if (isset($_SESSION['cupon'])): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; color: #16a34a; background: #f0fdf4; padding: 8px; border-radius: 8px;">
                            <span><i class="fas fa-tag"></i> Dto. (<?php echo $_SESSION['cupon']['codigo']; ?>):</span>
                            <strong>- <?php echo number_format($descuento_aplicado, 2); ?> €</strong>
                        </div>
                        <div style="text-align: right; margin-bottom: 20px;">
                            <a href="carrito.php?quitar_cupon=1" style="color: #ef4444; font-size: 0.85em; text-decoration: none; font-weight: 600;">Eliminar cupón</a>
                        </div>
                    <?php else: ?>
                        <form action="carrito.php" method="POST" class="cupon-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="text" name="codigo_cupon" placeholder="CÓDIGO PROMO" required>
                            <button type="submit" name="aplicar_cupon" class="btn-aplicar">OK</button>
                        </form>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; font-size: 1.6em; font-weight: 800; margin-top: 25px; border-top: 2px solid #0f172a; padding-top: 20px; color: #0f172a;">
                        <span>Total:</span>
                        <span style="color: #2563eb;"><?php echo number_format($total_final, 2); ?> €</span>
                    </div>

                    <a href="checkout.php" class="btn-pagar">
                        <i class="fas fa-shield-check"></i> Finalizar Compra
                    </a>
                    
                    <p style="text-align: center; color: #94a3b8; font-size: 0.8em; margin-top: 15px;">
                        <i class="fas fa-lock"></i> Pago 100% seguro y encriptado
                    </p>
                </div>

            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>