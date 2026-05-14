<?php
session_start();
include '../config/db.php';
// Nota: He quitado el include del header porque este archivo no muestra nada, 
// solo procesa datos y redirige. Incluir HTML aquí puede causar errores de "headers already sent".

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token de seguridad inválido.');
    }
    
    $accion = $_POST['accion'];
    $id_producto = isset($_POST['id_producto']) ? $_POST['id_producto'] : null;

    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    // 1. AÑADIR DESDE EL INDEX (Te devuelve al index fuera de la carpeta tienda)
    if ($accion == 'agregar' && $id_producto) {
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]++;
        } else {
            $_SESSION['carrito'][$id_producto] = 1;
        }
        header("Location: ../index.php");
        exit();
    }

    // 2. SUMAR DESDE EL CARRITO (Te mantiene dentro de la carpeta tienda)
    if ($accion == 'sumar_carrito' && $id_producto) {
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]++;
        }
        header("Location: carrito.php");
        exit();
    }

    // 3. RESTAR DESDE EL CARRITO
    if ($accion == 'restar' && $id_producto) {
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]--;
            // Si al restar se queda en cero, lo borramos completamente
            if ($_SESSION['carrito'][$id_producto] <= 0) {
                unset($_SESSION['carrito'][$id_producto]);
            }
        }
        header("Location: carrito.php");
        exit();
    }

    // 4. ELIMINAR COMPLETAMENTE (LA MAGIA OCURRE AQUÍ)
    if ($accion == 'eliminar' && $id_producto) {
        if (isset($_SESSION['carrito'][$id_producto])) {
            unset($_SESSION['carrito'][$id_producto]);
        }
        header("Location: carrito.php");
        exit();
    }
}

// Redirección por defecto si alguien intenta entrar aquí directamente sin enviar datos
header("Location: ../index.php");
exit();
?>