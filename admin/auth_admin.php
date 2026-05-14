<?php
session_start();

// Comprobamos si tiene sesión iniciada y si su rol es 'admin' o 'empleado'
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] != 'admin' && $_SESSION['usuario_rol'] != 'empleado')) {
    // Si es un cliente normal o un visitante, lo mandamos a la tienda
    header("Location: ../index.php");
    exit();
}
?>