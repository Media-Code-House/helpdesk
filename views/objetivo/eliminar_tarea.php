<?php
session_start();
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';






include '../header.php';
// Obtener el ID de la tarea y el ID del objetivo de la URL
$id_tarea = isset($_GET['id_tarea']) ? intval($_GET['id_tarea']) : 0;
$id_objetivo = isset($_GET['id_objetivo']) ? intval($_GET['id_objetivo']) : 0;

// Verificar que se recibió un ID de tarea válido
if ($id_tarea <= 0 || $id_objetivo <= 0) {
    $_SESSION['message'] = "ID de tarea o de objetivo no válido.";
    header("Location: tareas_objetivo.php?id=$id_objetivo");
    exit;
}

try {
    // Eliminar la tarea
    $stmt = $pdo->prepare("DELETE FROM objetivo_tareas WHERE id_tarea = ? AND id_objetivo = ?");
    $stmt->execute([$id_tarea, $id_objetivo]);

    $_SESSION['message'] = "Tarea eliminada correctamente.";
    header("Location: tareas_objetivo.php?id=$id_objetivo");
    exit;

} catch (PDOException $e) {
    $_SESSION['message'] = "Error al eliminar la tarea: " . $e->getMessage();
    header("Location: tareas_objetivo.php?id=$id_objetivo");
    exit;
}
