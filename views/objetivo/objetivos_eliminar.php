<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        // Preparar y ejecutar la eliminación del objetivo
        $stmt = $pdo->prepare("DELETE FROM objetivos_empresariales WHERE id_objetivo = ?");
        $stmt->execute([$id]);

        $_SESSION['message'] = "Objetivo eliminado correctamente.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error al eliminar el objetivo: " . $e->getMessage();
    }
} else {
    $_SESSION['message'] = "ID de objetivo no válido.";
}

// Redirige a la lista de objetivos sin ninguna salida previa
header("Location: objetivos_list.php");
exit;
