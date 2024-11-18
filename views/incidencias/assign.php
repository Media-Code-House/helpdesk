<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
// Verificar que el usuario es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Verificar que se enviaron los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_incidencia'], $_POST['id_asignado'])) {
    $id_incidencia = $_POST['id_incidencia'];
    $id_asignado = $_POST['id_asignado'];
    $fecha_asignacion = date('Y-m-d H:i:s');

    // Insertar la asignación en la tabla
    $query = "INSERT INTO asignaciones (id_incidencia, id_asignado, fecha_asignacion) VALUES (:id_incidencia, :id_asignado, :fecha_asignacion)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_incidencia', $id_incidencia);
    $stmt->bindParam(':id_asignado', $id_asignado);
    $stmt->bindParam(':fecha_asignacion', $fecha_asignacion);

    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Incidencia asignada correctamente.";
    } else {
        $_SESSION['error'] = "Error al asignar la incidencia.";
    }
    header("Location: list.php");
    exit;
} else {
    $_SESSION['error'] = "Datos incompletos para la asignación.";
    header("Location: list.php");
    exit;
}
