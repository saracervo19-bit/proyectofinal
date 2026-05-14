<?php
// 1. Primero la conexión a la base de datos (antes de cualquier include que pinte HTML)
include '../config/db.php';
// Incluimos el esqueleto que ya arranca la sesión y comprueba que al menos sea empleado
include 'includes/admin_header.php';

// --- SEGURIDAD EXTRA: SOLO EL ADMIN PUEDE VER ESTA PÁGINA ---
// (Si entró un empleado, el admin_header le dejó pasar, pero aquí lo echamos)
if ($_SESSION['usuario_rol'] != 'admin') {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

// --- LÓGICA PARA CAMBIAR EL ROL (Ascender / Degradar) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    
    $allowed_roles = ['cliente', 'empleado', 'admin'];
    $nuevo_rol = $_POST['nuevo_rol'] ?? '';
    $id_usuario = $_POST['id_usuario'] ?? '';
    
    if (in_array($nuevo_rol, $allowed_roles) && $id_usuario) {
        // Evitamos que el admin se cambie el rol a sí mismo
        if ($id_usuario != $_SESSION['usuario_id']) {
            $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
            $stmt->execute([$nuevo_rol, $id_usuario]);
            $mensaje_exito = "El rol del usuario se ha actualizado a: " . strtoupper($nuevo_rol);
        } else {
            $mensaje_error = "No puedes cambiar tu propio rol de administrador.";
        }
    }
}

// --- LÓGICA PARA BORRAR UN USUARIO ---
if (isset($_GET['borrar'])) {
    $id_borrar = $_GET['borrar'];
    
    if ($id_borrar == $_SESSION['usuario_id']) {
        $mensaje_error = "Por seguridad, no puedes borrar tu propia cuenta.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id_borrar]);
            $mensaje_exito = "Usuario eliminado correctamente.";
        } catch (PDOException $e) {
            $mensaje_error = "No puedes borrar este usuario porque tiene pedidos registrados. (Para mantener el historial de ventas).";
        }
    }
}

// --- OBTENER TODOS LOS USUARIOS ---
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();
?>

<script>document.getElementById('page-title').innerText = 'Gestión de Usuarios';</script>

<?php if (isset($mensaje_exito)): ?>
    <div class="success-msg">
        <i class="fas fa-check-circle"></i> <?php echo $mensaje_exito; ?>
    </div>
<?php endif; ?>

<?php if (isset($mensaje_error)): ?>
    <div class="error-msg">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
    </div>
<?php endif; ?>

<table class="admin-table">
    <thead>
        <tr>
            <th style="width: 80px;">ID</th>
            <th>Nombre y Email</th>
            <th>Rol Actual</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usr): ?>
        <tr>
            <td style="color: #64748b; font-weight: bold;">#<?php echo $usr['id']; ?></td>
            <td>
                <strong style="color: #0f172a; font-size: 1.1em;"><?php echo htmlspecialchars($usr['nombre']); ?></strong><br>
                <a href="mailto:<?php echo htmlspecialchars($usr['email']); ?>" style="color: #3b82f6; font-size: 0.9em; text-decoration: none;"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usr['email']); ?></a>
            </td>
            <td>
                <?php if($usr['rol'] == 'admin'): ?>
                    <span style="background: #fef08a; color: #854d0e; padding: 6px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; border: 1px solid #fde047;"><i class="fas fa-key"></i> Administrador</span>
                <?php elseif($usr['rol'] == 'empleado'): ?>
                    <span style="background: #e0e7ff; color: #4338ca; padding: 6px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; border: 1px solid #c7d2fe;"><i class="fas fa-id-badge"></i> Empleado</span>
                <?php else: ?>
                    <span style="background: #f1f5f9; color: #475569; padding: 6px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; border: 1px solid #e2e8f0;"><i class="fas fa-shopping-bag"></i> Cliente</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if($usr['id'] != $_SESSION['usuario_id']): ?>
                    
                    <?php if($usr['rol'] == 'cliente'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="nuevo_rol" value="empleado">
                            <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                            <button type="submit" class="btn-ascender" title="Hacer Empleado"><i class="fas fa-arrow-up"></i> Ascender</button>
                        </form>
                    <?php elseif($usr['rol'] == 'empleado'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="nuevo_rol" value="cliente">
                            <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                            <button type="submit" class="btn-degradar" title="Quitar permisos"><i class="fas fa-arrow-down"></i> Degradar</button>
                        </form>
                    <?php endif; ?>

                    <a href="usuarios.php?borrar=<?php echo $usr['id']; ?>" class="btn-borrar" title="Eliminar cuenta" onclick="return confirm('¿Seguro que deseas eliminar a este usuario de la base de datos?');"><i class="fas fa-trash-alt"></i></a>
                
                <?php else: ?>
                    <span style="color: #94a3b8; font-size: 0.9em; font-weight: bold; background: #f1f5f9; padding: 8px 15px; border-radius: 6px;">Tú (Actual)</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</main>
</body>
</html>