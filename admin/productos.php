<?php
// 1. Primero la conexión a la base de datos
include '../config/db.php';

// --- LÓGICA PARA BORRAR UN PRODUCTO ---
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    
    // Buscamos el producto para saber si tiene imagen y borrarla del disco duro
    $stmt_img = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt_img->execute([$id_borrar]);
    $prod = $stmt_img->fetch();
    
    // RUTA ACTUALIZADA: Ahora apunta a ../assets/img/productos/
    if ($prod && $prod['imagen'] && file_exists("../assets/img/productos/" . $prod['imagen'])) {
        unlink("../assets/img/productos/" . $prod['imagen']); // Borra el archivo físico
    }

    // Borramos el producto de la base de datos
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id_borrar]);
    $mensaje_exito = "Producto eliminado correctamente del catálogo.";
}

// --- OBTENER TODOS LOS PRODUCTOS ---
$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id 
        ORDER BY p.id DESC";
$productos = $pdo->query($sql)->fetchAll();

// 2. Ahora incluimos el "esqueleto" (que ya carga el menú lateral y comprueba sesiones)
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Inventario de Productos';</script>

<div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
    <a href="nuevo_producto.php" class="btn-accion btn-nuevo" style="padding: 10px 20px; font-size: 1.05em;">
        <i class="fas fa-plus-circle"></i> Añadir Producto
    </a>
</div>

<?php if (isset($mensaje_exito)): ?>
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
    </div>
<?php endif; ?>

<?php if(count($productos) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 80px; text-align: center;">Foto</th>
                <th>Nombre del Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th style="text-align: right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($productos as $prod): ?>
            <tr>
                <td style="text-align: center;">
                    <?php if($prod['imagen']): ?>
                        <img src="../assets/img/productos/<?php echo $prod['imagen']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" alt="Foto">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-camera-slash"></i></div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong>
                </td>
                <td>
                    <span style="background: #f1f5f9; color: #475569; padding: 5px 10px; border-radius: 20px; font-size: 0.85em; font-weight: bold; border: 1px solid #e2e8f0;">
                        <?php echo $prod['categoria_nombre'] ? htmlspecialchars($prod['categoria_nombre']) : 'Sin categoría'; ?>
                    </span>
                </td>
                <td style="color: #2563eb; font-weight: bold;">
                    <?php echo number_format($prod['precio'], 2); ?> €
                </td>
                <td>
                    <?php if($prod['stock'] > 5): ?>
                        <span style="color: #16a34a; font-weight: bold;"><i class="fas fa-check-circle"></i> <?php echo $prod['stock']; ?> uds.</span>
                    <?php elseif($prod['stock'] > 0): ?>
                        <span style="color: #ca8a04; font-weight: bold;"><i class="fas fa-exclamation-triangle"></i> Solo <?php echo $prod['stock']; ?> uds.</span>
                    <?php else: ?>
                        <span style="color: #dc2626; font-weight: bold;"><i class="fas fa-times-circle"></i> Agotado</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: right;">
                    <a href="editar_producto.php?id=<?php echo $prod['id']; ?>" class="btn-accion btn-editar" title="Editar"><i class="fas fa-edit"></i></a>
                    <a href="productos.php?borrar=<?php echo $prod['id']; ?>" class="btn-accion btn-borrar" title="Borrar" onclick="return confirm('¿Seguro que deseas eliminar este producto permanentemente?');"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 50px; background: white; border-radius: 10px; border: 1px dashed #cbd5e1;">
        <i class="fas fa-box-open" style="font-size: 4em; color: #cbd5e1; margin-bottom: 20px;"></i>
        <h2 style="color: #64748b;">Tu almacén está vacío</h2>
        <p>Añade tu primer dron o robot para empezar a vender.</p>
    </div>
<?php endif; ?>

</main>
</body>
</html>