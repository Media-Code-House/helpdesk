<?php
session_start();
require_once '../../config/db.php';

// Verificar que el usuario es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Verificar que se recibieron los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario'], $_POST['nuevo_rol'])) {
    $id_usuario = $_POST['id_usuario'];
    $nuevo_rol = $_POST['nuevo_rol'];

    // Actualizar el rol del usuario en la base de datos
    $query = "UPDATE usuarios SET rol = :nuevo_rol WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nuevo_rol', $nuevo_rol);
    $stmt->bindParam(':id_usuario', $id_usuario);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Rol actualizado correctamente.";
    } else {
        $_SESSION['error'] = "Error al actualizar el rol.";
    }
    header("Location: list_users.php");
    exit;
} else {
    $_SESSION['error'] = "Datos incompletos para la asignación de rol.";
    header("Location: list_users.php");
    exit;
}

include '../header.php'; // Incluir después de las redirecciones si no se necesita antes.
