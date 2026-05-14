<?php
// 1. Recuperamos la sesión actual
session_start();

// 2. Vaciamos todas las variables de sesión
session_unset();

// 3. Destruimos la sesión por completo
session_destroy();

// 4. Redirigimos a la portada principal de la tienda (saliendo de la carpeta auth con los dos puntos)
header("Location: ../index.php");
exit();
?>