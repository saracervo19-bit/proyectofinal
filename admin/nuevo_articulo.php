<?php
// 1. Conexión a la base de datos
include '../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    
    // Necesitamos saber el nombre del usuario logueado para ponerlo de autor.
    // Como el admin_header (que tiene la sesión) se carga más abajo, la iniciamos aquí un momento.
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $autor = $_SESSION['usuario_nombre']; 
    
    $imagen = "";

    // Lógica para subir la imagen
    if (!empty($_FILES['imagen']['name'])) {
        $nombre_img = time() . "_" . $_FILES['imagen']['name'];
        // Asegúrate de que existe la carpeta assets/img/blog/ en tu proyecto
        $ruta_destino = "../assets/img/blog/" . $nombre_img;
        
        if(move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
            $imagen = $nombre_img;
        } else {
            $error = "Hubo un problema al subir la imagen al servidor.";
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO blog (titulo, contenido, imagen, autor) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$titulo, $contenido, $imagen, $autor])) {
            header("Location: blog.php");
            exit();
        } else {
            $error = "Error al publicar la noticia en la base de datos.";
        }
    }
}

// 2. Incluimos el esqueleto del panel
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Escribir Nueva Noticia';</script>

<div style="margin-bottom: 20px;">
    <a href="blog.php" style="color: #64748b; text-decoration: none; font-weight: bold; padding: 10px 15px; background: white; border-radius: 8px; border: 1px solid #e2e8f0; display: inline-block;">
        <i class="fas fa-arrow-left"></i> Volver al blog
    </a>
</div>

<div style="background: white; padding: 30px; border-radius: 15px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); max-width: 800px;">
    <h2 style="margin-top: 0; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px;">
        Redactar Artículo
    </h2>

    <?php if($error): ?>
        <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Título de la noticia *</label>
            <input type="text" name="titulo" required placeholder="Ej: Los nuevos drones de 2026..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none;">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Contenido *</label>
            <textarea name="contenido" rows="10" required placeholder="Escribe aquí el cuerpo de la noticia..." style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1em; box-sizing: border-box; outline: none; resize: vertical;"></textarea>
        </div>

        <div style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <label style="display: block; font-weight: bold; color: #475569; margin-bottom: 8px;">Imagen de portada</label>
            <input type="file" name="imagen" accept="image/*" style="width: 100%; cursor: pointer;">
            <p style="font-size: 0.85em; color: #64748b; margin-top: 10px; margin-bottom: 0;">
                <i class="fas fa-info-circle"></i> Sube una imagen horizontal (formato apaisado) para que quede perfecta en el blog.
            </p>
        </div>

        <div style="text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 20px;">
            <a href="blog.php" style="color: #64748b; text-decoration: none; font-weight: bold;">Cancelar</a>
            <button type="submit" class="btn-accion btn-editar" style="padding: 12px 25px; border: none; cursor: pointer; font-size: 1.05em;">
                <i class="fas fa-paper-plane"></i> Publicar
            </button>
        </div>
    </form>
</div>

</main>
</body>
</html>