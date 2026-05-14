<?php
// 1. Conexión a la base de datos
include '../config/db.php';

// Obtener las categorías para mostrarlas en el desplegable
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token de seguridad inválido.";
    } else {
        $nombre = trim($_POST['nombre']);
        $descripcion = trim($_POST['descripcion']);
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $id_categoria = $_POST['id_categoria'];
        
        // Validar entrada
        if ($precio < 0) {
            $error = "El precio no puede ser negativo.";
        } elseif ($stock < 0) {
            $error = "El stock no puede ser negativo.";
        } elseif (strlen($descripcion) < 10) {
            $error = "La descripción debe tener al menos 10 caracteres.";
        } else {
            $nombre_imagen = "";
            
            // Procesar la subida de la imagen
            if (!empty($_FILES['imagen']['name'])) {
                // Validar tipo de archivo
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['imagen']['tmp_name']);
                
                if (!in_array($mime, $allowed_types)) {
                    $error = "Tipo de archivo no permitido. Solo JPG, PNG o GIF.";
                } elseif ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
                    $error = "El archivo es demasiado grande. Máximo 5MB.";
                } else {
                    // Nombre aleatorio
                    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $nombre_imagen = bin2hex(random_bytes(16)) . '.' . $extension;
                    $ruta_destino = "../assets/img/productos/" . $nombre_imagen;
                    
                    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                        $error = "Hubo un problema al guardar la imagen en el servidor.";
                    }
                }
            }

            if (empty($error)) {
                $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, imagen) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$nombre, $descripcion, $precio, $stock, $id_categoria, $nombre_imagen])) {
                    // Si va bien, volvemos a la lista de productos con un mensaje
                    header("Location: productos.php?exito=1");
                    exit();
                } else {
                    $error = "Error al guardar el producto en la base de datos.";
                }
            }
        }
    }
}

// 2. Incluimos el esqueleto del panel
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Añadir Nuevo Producto';</script>

<div style="margin-bottom: 20px;">
    <a href="productos.php" style="color: #64748b; text-decoration: none; font-weight: bold; padding: 10px 15px; background: white; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Volver al inventario
    </a>
</div>

<div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 800px;">
    <h2 style="margin-top: 0; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px;">
        Detalles del Producto
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
            <input type="text" name="nombre" required placeholder="Ej: Dron DJI Mini 3 Pro" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Categoría *</label>
            <select name="id_categoria" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none; background: white;">
                <option value="">-- Selecciona una categoría --</option>
                <?php foreach($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Precio (€) *</label>
                <input type="number" step="0.01" name="precio" required placeholder="0.00" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
            </div>
            <div>
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Stock Inicial *</label>
                <input type="number" name="stock" required placeholder="Unidades disponibles" min="0" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Descripción *</label>
            <textarea name="descripcion" rows="5" required placeholder="Escribe las características principales del producto..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none; resize: vertical;"></textarea>
        </div>

        <div style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Imagen del Producto</label>
            <input type="file" name="imagen" accept="image/*" style="width: 100%; cursor: pointer;">
            <p style="font-size: 0.85em; color: #64748b; margin-top: 10px; margin-bottom: 0;">
                <i class="fas fa-info-circle"></i> Sube una imagen en formato JPG o PNG. Preferiblemente cuadrada para que encaje bien en el catálogo.
            </p>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-accion btn-nuevo" style="padding: 12px 25px; border: none; cursor: pointer; font-size: 1.05em;">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
</div>

</main>
</body>
</html>