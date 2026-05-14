<?php
session_start();
include '../config/db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token de seguridad inválido.";
    } else {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Validar contraseña
        if (strlen($password) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Z]/', $password) || 
                  !preg_match('/[a-z]/', $password) ||
                  !preg_match('/[0-9]/', $password)) {
            $error = "La contraseña debe contener mayúsculas, minúsculas y números.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Comprobar si el email ya existe
            $check = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                $error = "El correo ya está registrado.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'cliente')");
                if ($stmt->execute([$nombre, $email, $password_hash])) {
                    $success = "Cuenta creada con éxito. Ya puedes iniciar sesión.";
                } else {
                    $error = "Hubo un error al crear la cuenta.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zentek - Registro</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="nav-auth">
        <a href="../index.php">ZENTEK</a>
    </div>

    <div class="auth-wrapper">
        <div class="auth-box">
            <h2>Crear Cuenta</h2>
            <p>Únete a la comunidad de drones más grande.</p>

            <?php if($error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success-msg"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form action="registro.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Tu nombre" required>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" name="email" placeholder="email@ejemplo.com" required>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 8 caracteres, con mayúsculas, minúsculas y números" required>
                </div>

                <button type="submit" class="btn-submit">Registrarme</button>
            </form>

            <div class="auth-links">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>
        </div>
    </div>

</body>
</html>