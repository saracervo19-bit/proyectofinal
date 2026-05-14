<?php
session_start();
include '../config/db.php';        // Correcto: sale de auth/ y entra en config/
// Nota: He quitado el include del header de aquí arriba porque lo ideal es ponerlo 
// dentro del body para que no rompa el diseño, pero mantenemos la lógica.

$error = "";

// Rate limiting para prevenir brute force
$login_attempts = $_SESSION['login_attempts'] ?? 0;
$last_attempt = $_SESSION['last_attempt'] ?? 0;

if ($login_attempts >= 5 && time() - $last_attempt < 3600) {
    $error = "Demasiados intentos. Intenta en 1 hora.";
} else {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $error = "Token de seguridad inválido.";
        } else {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_rol'] = $user['rol'];
                
                // Reset attempts on success
                unset($_SESSION['login_attempts'], $_SESSION['last_attempt']);
                
                // RUTA CORREGIDA: Volvemos a la raíz para ir al index
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Correo o contraseña incorrectos.";
                $_SESSION['login_attempts'] = $login_attempts + 1;
                $_SESSION['last_attempt'] = time();
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
    <title>Zentek - Iniciar Sesión</title>
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
            <h2>¡Hola de nuevo!</h2>
            <p>Introduce tus datos para acceder a tu cuenta.</p>

            <?php if($error): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="input-group">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" name="email" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-submit">Entrar a Zentek</button>
            </form>

            <div class="auth-links">
                ¿Aún no tienes cuenta? <a href="registro.php">Regístrate gratis</a>
            </div>
        </div>
    </div>

</body>
</html>