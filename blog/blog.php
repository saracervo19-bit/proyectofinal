<?php
session_start();
include '../config/db.php'; // Salimos de blog/ para entrar en config/

// 1. OBTENER TODAS LAS NOTICIAS
$stmt = $pdo->query("SELECT * FROM blog ORDER BY fecha_publicacion DESC");
$noticias = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Zentek - Blog Tecnológico</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenedor-blog-grid">
        <h1 class="titulo-seccion">Zentek Blog</h1>

        <div class="grid-4-columnas">
            <?php foreach ($noticias as $noticia): ?>
                <article class="producto-card">
                    
                    <div class="img-container">
                        <img src="../assets/img/blog/<?php echo $noticia['imagen']; ?>" alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
                    </div>
                    
                    <div class="producto-info">
                        <span style="color: var(--primary); font-weight: 800; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px;">Noticia</span>
                        
                        <h3 style="margin: 15px 0; font-size: 1.4em; color: var(--secondary); font-weight: 800; line-height: 1.3;">
                            <?php echo htmlspecialchars($noticia['titulo']); ?>
                        </h3>
                        
                        <p style="color: var(--text-muted); font-size: 1em; line-height: 1.6; margin-bottom: 20px; flex-grow: 1;">
                            <?php echo substr(strip_tags($noticia['contenido']), 0, 110) . '...'; ?>
                        </p>
                        
                        <a href="noticia.php?id=<?php echo $noticia['id']; ?>" style="display: inline-block; margin-top: auto; color: var(--primary); font-weight: 700; text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='var(--primary-hover)'" onmouseout="this.style.color='var(--primary)'">
                            Leer artículo completo <i class="fas fa-arrow-right" style="margin-left: 5px; font-size: 0.9em;"></i>
                        </a>
                    </div>
                    
                </article>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>