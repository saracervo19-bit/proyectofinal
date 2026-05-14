<?php
// 1. Conexión a la base de datos
include '../config/db.php';

// 2. Comprobar que recibimos un ID válido
if (!isset($_GET['id'])) {
    header("Location: blog.php");
    exit();
}

$id = $_GET['id'];

// 3. Obtener los datos actuales del artículo
$stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
$stmt->execute([$id]);
$articulo = $stmt->fetch();

if (!$articulo) {
    die("Artículo no encontrado.");
}

$error = "";

// 4. Procesar el formulario cuando se envía (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $imagen_actual = $articulo['imagen'];
    
    // Lógica para la imagen (solo si se sube una nueva)
    if (!empty($_FILES['nueva_imagen']['name'])) {
        $nombre_imagen = time() . "_" . $_FILES['nueva_imagen']['name'];
        $ruta_destino = "../assets/img/blog/" . $nombre_imagen;
        
        if (move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $ruta_destino)) {
            // Borramos la imagen vieja del disco duro para ahorrar espacio
            if ($imagen_actual && file_exists("../assets/img/blog/" . $imagen_actual)) {
                unlink("../assets/img/blog/" . $imagen_actual);
            }
        } else {
            $error = "Hubo un problema al subir la nueva imagen de portada.";
            $nombre_imagen = $imagen_actual; // Mantenemos la vieja por seguridad
        }
    } else {
        $nombre_imagen = $imagen_actual; // Si no subió nada, mantenemos la actual
    }

    if (empty($error)) {
        $sql = "UPDATE blog SET titulo=?, contenido=?, imagen=? WHERE id=?";
        $stmt_update = $pdo->prepare($sql);
        
        if ($stmt_update->execute([$titulo, $contenido, $nombre_imagen, $id])) {
            header("Location: blog.php?exito=2"); // Redirigimos con mensaje de éxito
            exit();
        } else {
            $error = "Error al actualizar la noticia en la base de datos.";
        }
    }
}

// 5. Incluimos el esqueleto del panel
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Editar Noticia';</script>

<div style="margin-bottom: 20px;">
    <a href="blog.php" style="color: #64748b; text-decoration: none; font-weight: bold; padding: 10px 15px; background: white; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Volver al blog
    </a>
</div>

<div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 800px;">
    <h2 style="margin-top: 0; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px;">
        <i class="fas fa-pen-square" style="color: #3b82f6;"></i> Modificando: <?php echo htmlspecialchars($articulo['titulo']); ?>
    </h2>

    <?php if($error): ?>
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Título de la noticia *</label>
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($articulo['titulo']); ?>" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Contenido *</label>
            <textarea name="contenido" rows="12" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none; resize: vertical; line-height: 1.5;"><?php echo htmlspecialchars($articulo['contenido']); ?></textarea>
        </div>

        <div style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px dashed #cbd5e1; display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
            <div style="flex-shrink: 0; text-align: center;">
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px; font-size: 0.9em;">Portada Actual</label>
                <?php if($articulo['imagen']): ?>
                    <img src="../assets/img/blog/<?php echo $articulo['imagen']; ?>" style="width: 150px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                <?php else: ?>
                    <div style="width: 150px; height: 100px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8;"><i class="fas fa-image" style="font-size: 2em;"></i></div>
                <?php endif; ?>
            </div>
            <div style="flex-grow: 1; min-width: 200px;">
                <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Cambiar portada (Opcional)</label>
                <input type="file" name="nueva_imagen" accept="image/*" style="width: 100%; cursor: pointer;">
                <p style="font-size: 0.85em; color: #64748b; margin-top: 10px; margin-bottom: 0;">Sube una nueva imagen solo si deseas reemplazar la actual.</p>
            </div>
        </div>

        <div style="text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 20px;">
            <a href="blog.php" style="color: #64748b; text-decoration: none; font-weight: bold;">Cancelar</a>
            <button type="submit" class="btn-accion btn-editar" style="padding: 12px 25px; border: none; cursor: pointer; font-size: 1.05em;">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

</main>
</body>
</html>