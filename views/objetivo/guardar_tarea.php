<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';

$id_objetivo = isset($_POST['id_objetivo']) ? intval($_POST['id_objetivo']) : 0;
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

try {
    if ($accion === 'crear') {
        // Agregar una nueva tarea
        $nombre_tarea = $_POST['nombre_tarea'];
        $valor_kpi = $_POST['valor_kpi'];

        $stmt = $pdo->prepare("INSERT INTO objetivo_tareas (id_objetivo, nombre_tarea, valor_kpi, progreso) VALUES (?, ?, ?, 0)");
        $stmt->execute([$id_objetivo, $nombre_tarea, $valor_kpi]);

        $_SESSION['message'] = "Tarea creada correctamente.";
    } elseif (strpos($accion, 'actualizar_') === 0) {
        // Actualizar una tarea existente
        $id_tarea = str_replace('actualizar_', '', $accion);
        $nombre_tarea = $_POST['nombre_tarea'][$id_tarea];
        $valor_kpi = $_POST['valor_kpi'][$id_tarea];
        $progreso = $_POST['progreso'][$id_tarea];
        $comentario = $_POST['comentario'][$id_tarea];

        $stmt = $pdo->prepare("UPDATE objetivo_tareas SET nombre_tarea = ?, valor_kpi = ?, progreso = ?, comentario = ? WHERE id_tarea = ?");
        $stmt->execute([$nombre_tarea, $valor_kpi, $progreso, $comentario, $id_tarea]);

        $_SESSION['message'] = "Tarea actualizada correctamente.";
    }
    
    // Redirigir de regreso a la página de tareas del objetivo
    header("Location: tareas_objetivo.php?id=$id_objetivo");
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
