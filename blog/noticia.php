<?php
session_start();
include '../config/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// OBTENER LA NOTICIA ESPECÍFICA
$stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    header("Location: blog.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($noticia['titulo']); ?> - Zentek</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenedor-blog">
        <a href="blog.php" style="color: var(--text-muted); text-decoration: none; font-weight: 600; margin-bottom: 25px; display: inline-block; transition: 0.3s;">
            <i class="fas fa-arrow-left"></i> Volver a Zentek Blog
        </a>
        
        <article class="post">
            
            <img src="../assets/img/blog/<?php echo $noticia['imagen']; ?>" class="post-img" alt="<?php echo htmlspecialchars($noticia['titulo']); ?>">
            
            <div class="post-info">
                <h1 class="post-titulo"><?php echo htmlspecialchars($noticia['titulo']); ?></h1>
                
                <div class="post-fecha">
                    <span><i class="fas fa-calendar"></i> <?php echo date('d M, Y', strtotime($noticia['fecha_publicacion'])); ?></span>
                    <span><i class="fas fa-user-circle"></i> Por <?php echo htmlspecialchars($noticia['autor']); ?></span>
                </div>
                
                <div class="post-contenido">
                    <?php echo nl2br(htmlspecialchars($noticia['contenido'])); ?>
                </div>
            </div>

        </article>
    </main>

    <?php include '../includes/footer.php'; ?>

</body>
</html>