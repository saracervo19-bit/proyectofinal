<?php
// Iniciamos sesión si todavía no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar CSRF token si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Headers de seguridad
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://kit.fontawesome.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://kit.fontawesome.com https://ka-f.fontawesome.com https://fonts.gstatic.com https://cdnjs.cloudflare.com;");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

$cantidad_carrito = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
?>
<nav class="navbar">
    
    <div class="nav-brand">
        <a href="/index.php">
            <img src="/assets/img/Logotipo.png" alt="Logotipo Zentek" class="logo-navbar">
        </a>
    </div>

    <div class="nav-search-container">
        <form action="/index.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Buscar drones, robots..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button type="submit" aria-label="Buscar"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <button class="menu-toggle" id="mobile-menu" aria-label="Abrir menú">
        <i class="fas fa-bars"></i>
    </button>

    <div class="nav-menu" id="nav-menu">
        <div class="nav-icons">
            <a href="/index.php" class="nav-link"><i class="fas fa-store"></i> Tienda</a>
            <a href="/blog/blog.php" class="nav-link"><i class="fas fa-newspaper"></i> Blog</a>

            <a href="/tienda/carrito.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if($cantidad_carrito > 0): ?>
                    <span class="badge-carrito"><?php echo $cantidad_carrito; ?></span>
                <?php endif; ?>
            </a>

            <?php if(isset($_SESSION['usuario_id'])): ?>
                <a href="/auth/perfil.php" class="nav-user">
                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </a>

                <?php 
                $rol = isset($_SESSION['usuario_rol']) ? $_SESSION['usuario_rol'] : 'cliente';
                if($rol === 'admin' || $rol === 'empleado'): 
                ?>
                    <a href="/admin/index.php" class="btn-admin">
                        <i class="fas fa-cog"></i> Panel
                    </a>
                <?php endif; ?>

            <?php else: ?>
                <a href="/auth/login.php" class="btn-registro"><i class="fas fa-user"></i> Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu').addEventListener('click', function() {
        document.getElementById('nav-menu').classList.toggle('active');
        const icon = this.querySelector('i');
        if(this.classList.contains('active-btn')) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
            this.classList.remove('active-btn');
        } else {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
            this.classList.add('active-btn');
        }
    });
</script>