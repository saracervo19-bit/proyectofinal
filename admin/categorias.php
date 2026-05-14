<?php
// 1. Conexión a la base de datos
include '../config/db.php';

// --- LÓGICA PARA AÑADIR UNA CATEGORÍA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nueva_categoria'])) {
    $nombre_cat = trim($_POST['nombre_categoria']);
    
    if (!empty($nombre_cat)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        if ($stmt->execute([$nombre_cat])) {
            $mensaje_exito = "Categoría '$nombre_cat' creada correctamente.";
        } else {
            $mensaje_error = "Error al crear la categoría.";
        }
    }
}

// --- LÓGICA PARA BORRAR UNA CATEGORÍA ---
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->execute([$id_borrar]);
        $mensaje_exito = "Categoría eliminada con éxito.";
    } catch (PDOException $e) {
        $mensaje_error = "No puedes borrar esta categoría porque hay productos que la están utilizando. (Cámbialos de categoría primero).";
    }
}

// --- OBTENER TODAS LAS CATEGORÍAS ---
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY id DESC")->fetchAll();

// 2. Incluimos el esqueleto del panel de control
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Gestión de Categorías';</script>

<?php if (isset($mensaje_exito)): ?>
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
    </div>
<?php endif; ?>
<?php if (isset($mensaje_error)): ?>
    <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
    </div>
<?php endif; ?>

<div style="background: white; padding: 25px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
    <div style="font-size: 2.5em; color: #cbd5e1;">
        <i class="fas fa-folder-plus"></i>
    </div>
    <form action="" method="POST" style="flex: 1; display: flex; gap: 15px; margin: 0; align-items: center; flex-wrap: wrap;">
        <input type="hidden" name="nueva_categoria" value="1">
        <input type="text" name="nombre_categoria" placeholder="Nombre de la nueva categoría (Ej: Drones de Carreras)" required style="flex: 1; min-width: 250px; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; outline: none;">
        <button type="submit" class="btn-accion btn-nuevo" style="padding: 12px 25px; border: none; cursor: pointer; font-size: 1em;">
            <i class="fas fa-plus"></i> Crear Categoría
        </button>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th style="width: 80px;">ID</th>
            <th>Nombre de la Familia/Categoría</th>
            <th style="text-align: right;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if(count($categorias) > 0): ?>
            <?php foreach ($categorias as $cat): ?>
            <tr>
                <td style="color: #64748b; font-weight: bold;">#<?php echo $cat['id']; ?></td>
                <td>
                    <strong style="color: #0f172a; font-size: 1.1em;">
                        <i class="fas fa-folder" style="color: #94a3b8; margin-right: 8px;"></i> <?php echo htmlspecialchars($cat['nombre']); ?>
                    </strong>
                </td>
                <td style="text-align: right;">
                    <a href="categorias.php?borrar=<?php echo $cat['id']; ?>" class="btn-accion btn-borrar" title="Eliminar" onclick="return confirm('¿Seguro que deseas borrar esta categoría permanentemente?');">
                        <i class="fas fa-trash-alt"></i> Borrar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fas fa-folder-open" style="font-size: 3em; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                    No hay categorías creadas. Usa el formulario de arriba para añadir la primera.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</main>
</body>
</html>