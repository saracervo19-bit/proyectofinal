<?php
// Evitamos iniciar sesión si ya está iniciada
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Seguridad general para todo el panel: Solo admin o empleado
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'admin' && $_SESSION['usuario_rol'] !== 'empleado')) {
    header("Location: ../auth/login.php");
    exit();
}

// Para saber en qué página estamos y pintar el botón de azul (clase active)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    
    <title>Panel de Control - Zentek Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/estilos.css?v=<?php echo time(); ?>">

    <style>
        /* ESTILOS BASE DEL PANEL DE ADMINISTRACIÓN */
        body { background-color: #f8fafc; margin: 0; display: flex; min-height: 100vh; flex-direction: row; font-family: 'Inter', sans-serif; }
        
        /* Barra Lateral (Sidebar) - Escritorio */
        .admin-sidebar { width: 260px; background: #0f172a; color: white; display: flex; flex-direction: column; flex-shrink: 0; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        .admin-brand { padding: 25px 20px; font-size: 1.5em; font-weight: 800; border-bottom: 1px solid #1e293b; text-align: center; letter-spacing: 2px; color: white; text-decoration: none; }
        
        .admin-nav { display: flex; flex-direction: column; padding: 20px 0; flex: 1; gap: 5px; }
        .admin-nav a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: flex; align-items: center; gap: 15px; transition: all 0.3s; font-weight: 600; border-left: 4px solid transparent; }
        .admin-nav a:hover, .admin-nav a.active { background: #1e293b; color: white; border-left-color: #3b82f6; }
        
        .admin-nav a.logout { margin-top: auto; color: #f87171; border-top: 1px solid #1e293b; }
        .admin-nav a.logout:hover { background: #7f1d1d; color: white; border-left-color: #ef4444; }

        /* Contenido Principal */
        .admin-main { flex: 1; padding: 40px; overflow-y: auto; width: 100%; box-sizing: border-box; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .admin-header h1 { margin: 0; color: #0f172a; font-size: 1.8em; font-weight: 800; }
        
        .user-badge { background: white; padding: 10px 20px; border-radius: 30px; font-weight: bold; color: #475569; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }

        /* Botones de Menú Móvil (Ocultos por defecto) */
        .admin-menu-toggle { display: none; background: none; border: none; font-size: 1.5em; color: #0f172a; cursor: pointer; }
        .admin-menu-close { display: none; background: none; border: none; font-size: 2em; color: white; cursor: pointer; position: absolute; top: 20px; right: 25px; }

        /* Clases de utilidad para tablas y botones del admin */
        .btn-accion { padding: 8px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.9em; display: inline-flex; align-items: center; gap: 5px; border: none; cursor: pointer; transition: 0.2s; }
        .btn-nuevo { background: #22c55e; color: white; }
        .btn-nuevo:hover { background: #16a34a; }
        .btn-editar { background: #3b82f6; color: white; }
        .btn-borrar { background: #ef4444; color: white; }

        .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 20px; }
        .admin-table th { background: #0f172a; color: white; padding: 15px; text-align: left; font-size: 0.85em; text-transform: uppercase; letter-spacing: 1px; }
        .admin-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #1e293b; }
        .admin-table tr:hover { background: #f8fafc; }

        /* --- ESTILOS MAGIA PARA EL LOGO DEL ADMIN --- */
        .logo-admin-sidebar {
            height: 50px; /* Tamaño ideal para el panel lateral */
            width: auto;
            margin: 0 auto;
            display: block;
            filter: brightness(0) invert(1); /* ¡Convierte el logo azul oscuro a BLANCO PURO! */
            transition: transform 0.3s ease;
        }
        .logo-admin-sidebar:hover {
            transform: scale(1.05);
        }

        /* =========================================
           ADAPTACIÓN MÓVIL (MENÚ PANTALLA COMPLETA)
           ========================================= */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            
            /* El sidebar se oculta a la izquierda y ocupa toda la pantalla */
            .admin-sidebar { 
                position: fixed; 
                top: 0; 
                left: -100%; 
                width: 100%; 
                height: 100vh; 
                z-index: 9999; 
                transition: left 0.3s ease-in-out; 
                background: #0f172a; /* Mismo fondo azul oscuro */
            }
            .admin-sidebar.active { left: 0; } /* Clase que activaremos con JS */
            
            .admin-menu-close { display: block; } /* Mostramos la X */
            .admin-brand { padding-top: 30px; text-align: left; padding-left: 25px; border-bottom: none; }
            .logo-admin-sidebar { margin: 0; } /* En móvil alineamos el logo a la izquierda */
            
            .admin-nav { padding: 10px 20px; align-items: stretch; }
            .admin-nav a { padding: 18px 20px; font-size: 1.1em; border-left: none; border-bottom: 1px solid #1e293b; }
            
            /* Ajustamos la cabecera principal para mostrar la hamburguesa */
            .admin-main { padding: 15px; }
            .admin-header { 
                flex-direction: row; 
                align-items: center; 
                justify-content: flex-start; 
                padding: 15px; 
                margin-bottom: 25px; 
                background: white; 
                border-radius: 10px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .admin-menu-toggle { display: block; margin-right: 15px; } /* Mostramos la hamburguesa */
            #page-title { flex-grow: 1; font-size: 1.3em; margin: 0; }
            .user-badge { display: none; } /* Ocultamos el badge de usuario para que quepa el título */
        }
    </style>
</head>
<body>

    <aside class="admin-sidebar" id="admin-sidebar">
        <button class="admin-menu-close" id="close-admin-menu" aria-label="Cerrar menú"><i class="fas fa-times"></i></button>
        
        <a href="index.php" class="admin-brand" style="padding: 20px;">
            <img src="/assets/img/Logotipo.png" alt="Zentek Admin" class="logo-admin-sidebar">
        </a>
        
        <nav class="admin-nav">
            <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Inicio
            </a>
            <a href="productos.php" class="<?php echo ($current_page == 'productos.php' || $current_page == 'nuevo_producto.php' || $current_page == 'editar_producto.php') ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Productos
            </a>
            <a href="categorias.php" class="<?php echo ($current_page == 'categorias.php') ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categorías
            </a>
            <a href="cupones.php" class="<?php echo ($current_page == 'cupones.php') ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> Cupones
            </a>
            <a href="blog.php" class="<?php echo ($current_page == 'blog.php' || $current_page == 'nuevo_articulo.php' || $current_page == 'editar_articulo.php') ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i> Blog
            </a>
            
            <?php if($_SESSION['usuario_rol'] === 'admin'): ?>
                <a href="usuarios.php" class="<?php echo ($current_page == 'usuarios.php') ? 'active' : ''; ?>" style="color: #fbbf24;">
                    <i class="fas fa-users"></i> Usuarios
                </a>
            <?php endif; ?>
            
            <a href="../index.php"><i class="fas fa-external-link-alt"></i> Ver Tienda</a>
            <a href="../auth/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <button class="admin-menu-toggle" id="open-admin-menu" aria-label="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>
            
            <h1 id="page-title">Panel de Control</h1> 
            
            <div class="user-badge">
                <i class="fas fa-user-shield" style="color: <?php echo $_SESSION['usuario_rol'] === 'admin' ? '#ca8a04' : '#3b82f6'; ?>"></i> 
                <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?> (<?php echo ucfirst($_SESSION['usuario_rol']); ?>)
            </div>
        </header>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('admin-sidebar');
                const openBtn = document.getElementById('open-admin-menu');
                const closeBtn = document.getElementById('close-admin-menu');

                // Abrir menú
                if(openBtn) {
                    openBtn.addEventListener('click', function() {
                        sidebar.classList.add('active');
                        document.body.style.overflow = 'hidden'; // Evita hacer scroll en el fondo mientras el menú está abierto
                    });
                }

                // Cerrar menú
                if(closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        sidebar.classList.remove('active');
                        document.body.style.overflow = ''; // Devuelve el scroll
                    });
                }
            });
        </script>