<?php
// 1. Conexión a la base de datos
include '../config/db.php';

// 2. Lógica para CREAR un cupón
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_cupon'])) {
    $codigo = strtoupper(trim($_POST['codigo']));
    $descuento = $_POST['descuento'];
    $tipo = $_POST['tipo'];
    $fecha_expiracion = $_POST['fecha_expiracion'];

    try {
        $stmt = $pdo->prepare("INSERT INTO cupones (codigo, descuento, tipo, fecha_expiracion) VALUES (?, ?, ?, ?)");
        $stmt->execute([$codigo, $descuento, $tipo, $fecha_expiracion]);
        $mensaje = "¡Cupón $codigo creado con éxito!";
    } catch(PDOException $e) {
        $error = "Error: Es posible que ese código ya exista o falte algún dato.";
    }
}

// 3. Lógica para BORRAR un cupón
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    $pdo->prepare("DELETE FROM cupones WHERE id = ?")->execute([$id_borrar]);
    header("Location: cupones.php?eliminado=1");
    exit();
}

// 4. Obtener la lista de cupones
$cupones = $pdo->query("SELECT * FROM cupones ORDER BY id DESC")->fetchAll();

// 5. Incluimos el esqueleto del admin
include 'includes/admin_header.php';
?>

<script>document.getElementById('page-title').innerText = 'Gestión de Cupones';</script>

<div class="admin-container">
    
    <?php if($mensaje || isset($_GET['eliminado'])): ?>
        <div class="success-msg">
            <i class="fas fa-check-circle"></i> <?php echo $mensaje ? $mensaje : "Cupón eliminado correctamente."; ?>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <form action="cupones.php" method="POST" class="form-cupones">
            <input type="hidden" name="crear_cupon" value="1">
            
            <div class="grupo-input">
                <label>Código del cupón</label>
                <input type="text" name="codigo" required placeholder="EJ: VERANO20" style="text-transform: uppercase;">
            </div>
            
            <div class="grupo-input">
                <label>Cantidad Dto.</label>
                <input type="number" name="descuento" step="0.01" required placeholder="Ej: 15">
            </div>
            
            <div class="grupo-input">
                <label>Tipo de descuento</label>
                <select name="tipo">
                    <option value="porcentaje">% Porcentaje</option>
                    <option value="fijo">€ Euros Fijos</option>
                </select>
            </div>
            
            <div class="grupo-input">
                <label>Fecha de expiración</label>
                <input type="date" name="fecha_expiracion" required>
            </div>
            
            <button type="submit" class="btn-accion btn-nuevo">
                <i class="fas fa-plus-circle"></i> Crear Cupón
            </button>
        </form>
    </div>

    <div class="admin-card" style="margin-top: 30px;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descuento aplicado</th>
                    <th>Válido hasta</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if(count($cupones) > 0): ?>
                <?php foreach ($cupones as $c): ?>
                <tr>
                    <td data-label="Código">
                        <span class="coupon-code-badge">
                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($c['codigo']); ?>
                        </span>
                    </td>
                    <td data-label="Descuento">
                        <span class="discount-pill <?php echo $c['tipo']; ?>">
                            <?php echo $c['descuento']; ?><?php echo $c['tipo'] == 'porcentaje' ? '%' : '€'; ?> DTO.
                        </span>
                    </td>
                    <td data-label="Fecha Límite">
                        <?php 
                            $hoy = date('Y-m-d');
                            $caducado = ($c['fecha_expiracion'] < $hoy);
                        ?>
                        <div class="date-badge <?php echo $caducado ? 'expired' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i> 
                            <?php echo date('d/m/Y', strtotime($c['fecha_expiracion'])); ?>
                            <?php if($caducado) echo '<span>(CADUCADO)</span>'; ?>
                        </div>
                    </td>
                    <td data-label="Acciones">
                        <a href="cupones.php?borrar=<?php echo $c['id']; ?>" class="btn-accion btn-borrar" onclick="return confirm('¿Seguro que deseas eliminar este cupón?');">
                            <i class="fas fa-trash-alt"></i> Borrar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <p>No hay cupones creados en la base de datos.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</main> </body>
</html>