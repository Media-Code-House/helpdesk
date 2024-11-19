<?php

// Iniciar la sesión
session_start();

// Verificar si el usuario ya ha iniciado sesión
if (isset($_SESSION['id_usuario']) && !isset($_SESSION['2fa_verified'])) {
    // Si el usuario está autenticado, redirigir al dashboard
    header("Location: /views/dashboard.php");
    exit;
} else {
    // Si no está autenticado, redirigir a la página de inicio de sesión
    header("Location: /views/auth/login.php");
    exit;
}
?>
