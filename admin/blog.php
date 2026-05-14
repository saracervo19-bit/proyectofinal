<?php
// 1. Conexión a la base de datos
include '../config/db.php';

// --- LÓGICA PARA BORRAR UNA NOTICIA ---
if (isset($_GET['borrar'])) {
    $id = $_GET['borrar'];
    
    // Lo hacemos con mensaje de éxito en lugar de redirigir de golpe, 
    // así queda igual que en las demás pantallas del admin.
    $stmt = $pdo->prepare("DELETE FROM blog WHERE id = ?");
    if ($stmt->execute([$id])) {
        $mensaje_exito = "Artículo eliminado correctamente del blog.";
    }
}

// --- OBTENER TODAS LAS NOTICIAS ---
$noticias = $pdo->query("SELECT * FROM blog ORDER BY fecha_publicacion DESC")->fetchAll();

// 2. Incluimos el esqueleto del panel de control
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Artículos del Blog';</script>

<div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
    <a href="nuevo_articulo.php" class="btn-accion btn-nuevo" style="padding: 10px 20px; font-size: 1.05em;">
        <i class="fas fa-plus-circle"></i> Nuevo Artículo
    </a>
</div>

<?php if (isset($mensaje_exito)): ?>
    <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
        <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
    </div>
<?php endif; ?>

<?php if(count($noticias) > 0): ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Título de la Noticia</th>
                <th>Autor</th>
                <th>Fecha de Publicación</th>
                <th style="text-align: right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($noticias as $n): ?>
            <tr>
                <td>
                    <strong style="color: #0f172a; font-size: 1.1em;"><?php echo htmlspecialchars($n['titulo']); ?></strong>
                </td>
                <td style="color: #64748b;">
                    <i class="fas fa-user-edit" style="color: #94a3b8; margin-right: 5px;"></i> 
                    <?php echo htmlspecialchars($n['autor']); ?>
                </td>
                <td style="color: #475569; font-weight: bold;">
                    <i class="fas fa-calendar-alt" style="color: #94a3b8; margin-right: 5px;"></i> 
                    <?php echo date('d/m/Y', strtotime($n['fecha_publicacion'])); ?>
                </td>
                <td style="text-align: right;">
                    <a href="editar_articulo.php?id=<?php echo $n['id']; ?>" class="btn-accion btn-editar" title="Editar"><i class="fas fa-edit"></i></a>
                    
                    <a href="blog.php?borrar=<?php echo $n['id']; ?>" class="btn-accion btn-borrar" title="Borrar" onclick="return confirm('¿Seguro que deseas eliminar este artículo permanentemente?');"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 50px; background: white; border-radius: 10px; border: 1px dashed #cbd5e1;">
        <i class="fas fa-newspaper" style="font-size: 4em; color: #cbd5e1; margin-bottom: 20px;"></i>
        <h2 style="color: #64748b;">No hay artículos publicados</h2>
        <p>Escribe tu primera noticia para mantener informados a tus clientes.</p>
    </div>
<?php endif; ?>

</main>
</body>
</html>