<?php
// 1. Conexión a la base de datos
include '../config/db.php';

// 2. COMPROBAR QUE RECIBIMOS UN ID VÁLIDO
if (!isset($_GET['id'])) {
    header("Location: productos.php");
    exit();
}

$id = $_GET['id'];

// 3. OBTENER LOS DATOS ACTUALES DEL PRODUCTO
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    die("Producto no encontrado.");
}

$error = "";

// 4. PROCESAR EL FORMULARIO CUANDO SE ENVÍA (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token de seguridad inválido.";
    } else {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $id_categoria = $_POST['id_categoria'];
        $imagen_actual = $producto['imagen'];

        // Validar entrada
        if ($precio < 0) {
            $error = "El precio no puede ser negativo.";
        } elseif ($stock < 0) {
            $error = "El stock no puede ser negativo.";
        } elseif (strlen($descripcion) < 10) {
            $error = "La descripción debe tener al menos 10 caracteres.";
        } else {
            // Lógica para la imagen (solo si se sube una nueva)
            if (!empty($_FILES['nueva_imagen']['name'])) {
                // Validar tipo de archivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $mime = mime_content_type($_FILES['nueva_imagen']['tmp_name']);
                
                if (!in_array($mime, $allowed_types)) {
                    $error = "Tipo de archivo no permitido. Solo JPG, PNG o GIF.";
                } elseif ($_FILES['nueva_imagen']['size'] > 5 * 1024 * 1024) {
                    $error = "El archivo es demasiado grande. Máximo 5MB.";
                } else {
                    // Nombre aleatorio
                    $extension = pathinfo($_FILES['nueva_imagen']['name'], PATHINFO_EXTENSION);
                    $nombre_imagen = bin2hex(random_bytes(16)) . '.' . $extension;
                    $ruta_destino = "../assets/img/productos/" . $nombre_imagen;
                    
                    if (move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $ruta_destino)) {
                        // Si subió la foto nueva correctamente y la anterior existía, borramos la vieja del disco duro
                        if ($imagen_actual && file_exists("../assets/img/productos/" . $imagen_actual)) {
                            unlink("../assets/img/productos/" . $imagen_actual);
                        }
                    } else {
                        $error = "Hubo un problema al subir la nueva imagen.";
                        $nombre_imagen = $imagen_actual; // Si falla, nos quedamos con la vieja por seguridad
                    }
                }
            } else {
                $nombre_imagen = $imagen_actual; // Si no subió nada, mantenemos la que ya tenía
            }

            if (empty($error)) {
                $sql = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, id_categoria=?, imagen=? WHERE id=?";
                $stmt_update = $pdo->prepare($sql);
                
                if ($stmt_update->execute([$nombre, $descripcion, $precio, $stock, $id_categoria, $nombre_imagen, $id])) {
                    header("Location: productos.php?editado=1");
                    exit();
                } else {
                    $error = "Error al actualizar el producto en la base de datos.";
                }
            }
        }
    }
}

// 5. OBTENER CATEGORÍAS PARA EL DESPLEGABLE
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();

// 6. Incluimos el esqueleto del panel
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Editar Producto';</script>

<div style="margin-bottom: 20px;">
    <a href="productos.php" style="color: #64748b; text-decoration: none; font-weight: bold; padding: 10px 15px; background: white; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Volver al inventario
    </a>
</div>

<div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 800px;">
    <h2 style="margin-top: 0; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px;">
        <i class="fas fa-edit" style="color: #3b82f6;"></i> Modificando: <?php echo htmlspecialchars($producto['nombre']); ?>
    </h2>

    <?php if($error): ?>
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Nombre del Producto *</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Categoría *</label>
            <select name="id_categoria" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none; background: white;">
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $producto['id_categoria']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Precio (€) *</label>
                <input type="number" step="0.01" name="precio" value="<?php echo $producto['precio']; ?>" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
            </div>
            <div>
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Stock actual *</label>
                <input type="number" name="stock" value="<?php echo $producto['stock']; ?>" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Descripción *</label>
            <textarea name="descripcion" rows="5" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none; resize: vertical;"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
        </div>

        <div style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px dashed #cbd5e1; display: flex; gap: 20px; align-items: center;">
            <div style="flex-shrink: 0; text-align: center;">
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px; font-size: 0.9em;">Imagen Actual</label>
                <?php if($producto['imagen']): ?>
                    <img src="../assets/img/productos/<?php echo $producto['imagen']; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                <?php else: ?>
                    <div style="width: 100px; height: 100px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-image" style="font-size: 2em;"></i></div>
                <?php endif; ?>
            </div>
            <div style="flex-grow: 1;">
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Subir nueva imagen (Opcional)</label>
                <input type="file" name="nueva_imagen" accept="image/*" style="width: 100%; cursor: pointer;">
                <p style="font-size: 0.85em; color: #64748b; margin-top: 10px; margin-bottom: 0;">Si subes una foto nueva, la anterior se borrará automáticamente para ahorrar espacio en tu servidor.</p>
            </div>
        </div>

        <div style="text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 20px;">
            <a href="productos.php" style="color: #64748b; text-decoration: none; font-weight: bold;">Cancelar</a>
            <button type="submit" class="btn-accion btn-editar" style="padding: 12px 25px; border: none; cursor: pointer; font-size: 1.05em;">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

</main>
</body>
</html>
