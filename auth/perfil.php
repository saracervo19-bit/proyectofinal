<?php
session_start();
include '../config/db.php';

// Si el usuario no ha iniciado sesión, lo mandamos al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['usuario_id'];

// 1. OBTENER DATOS DEL USUARIO
$stmt_user = $pdo->prepare("SELECT nombre, email, fecha_registro FROM usuarios WHERE id = ?");
$stmt_user->execute([$id_usuario]);
$usuario = $stmt_user->fetch();

// 2. OBTENER EL HISTORIAL DE PEDIDOS CON LOS PRODUCTOS COMPRADOS
$stmt_pedidos = $pdo->prepare("
    SELECT p.id, p.fecha, p.total, p.estado, 
    GROUP_CONCAT(prod.nombre SEPARATOR ', ') as nombres_productos
    FROM pedidos p
    LEFT JOIN detalles_pedido dp ON p.id = dp.id_pedido
    LEFT JOIN productos prod ON dp.id_producto = prod.id
    WHERE p.id_usuario = ? 
    GROUP BY p.id 
    ORDER BY p.fecha DESC
");
$stmt_pedidos->execute([$id_usuario]);
$pedidos = $stmt_pedidos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Zentek - Mi Perfil</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenedor-perfil">
        <h1 class="titulo-seccion" style="margin-bottom: 40px; text-align: left;"><i class="fas fa-user-circle"></i> Mi Panel de Cliente</h1>

        <div class="layout-perfil">
            
            <aside class="tarjeta-perfil">
                <h2 style="font-size: 1.2em; margin-bottom: 20px; color: var(--secondary); border-bottom: 2px solid var(--border-color); padding-bottom: 10px;">Mis Datos</h2>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85em; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Nombre completo</label>
                    <p style="font-size: 1.1em; color: var(--text-main); margin-top: 5px; font-weight: 600;"><?php echo htmlspecialchars($usuario['nombre']); ?></p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85em; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Correo electrónico</label>
                    <p style="font-size: 1.1em; color: var(--text-main); margin-top: 5px; font-weight: 600;"><?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-size: 0.85em; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Cliente desde</label>
                    <p style="font-size: 1.1em; color: var(--text-main); margin-top: 5px; font-weight: 600;"><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></p>
                </div>
                
                <a href="logout.php" class="btn-borrar" style="display: block; text-align: center; width: 100%; text-decoration: none;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </aside>

            <section class="historial-pedidos">
                <div class="tarjeta-perfil">
                    <h2 style="margin-top: 0; color: var(--secondary); border-bottom: 2px solid var(--border-color); padding-bottom: 15px; margin-bottom: 20px;">Historial de Compras</h2>
                    
                    <?php if (count($pedidos) > 0): ?>
                        
                        <table class="tabla-pedidos">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Productos</th>
                                    <th>Fecha</th>
                                    <th style="text-align: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td data-label="Pedido">
                                        <strong>#<?php echo $pedido['id']; ?></strong>
                                    </td>
                                    <td data-label="Productos" class="texto-productos">
                                        <?php echo htmlspecialchars($pedido['nombres_productos']); ?>
                                    </td>
                                    <td data-label="Fecha" class="texto-fecha">
                                        <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($pedido['fecha'])); ?>
                                    </td>
                                    <td data-label="Total" class="texto-total">
                                        <?php echo number_format($pedido['total'], 2); ?> €
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-shopping-bag" style="font-size: 3em; color: var(--border-color); margin-bottom: 15px;"></i>
                            <p style="color: var(--text-muted); margin-bottom: 20px;">Todavía no has realizado ninguna compra.</p>
                            <a href="../index.php" class="btn-comprar" style="display: inline-block; max-width: 200px; margin: 0 auto; text-decoration: none;">Ir a la tienda</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>