<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

if (isset($_SESSION['id_usuario'])) {
    if (!isset($_SESSION['2fa_verified'])) {
        header("Location: /views/auth/verify.php");
        exit;
    }
} else {
    header("Location: /views/auth/login.php");
    exit;
}
// Obtén y procesa los datos enviados desde el formulario
$id_objetivo = isset($_POST['id_objetivo']) ? intval($_POST['id_objetivo']) : 0;
$nombre = $_POST['nombre'];
$descripcion = $_POST['descripcion'];
$kpi = floatval($_POST['kpi']);
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$responsable = $_POST['responsable'];
$estado = $_POST['estado'];
$observaciones = $_POST['observaciones'];
$modificado_por = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : 'Desconocido';

try {
    if ($id_objetivo > 0) {
        // Código para actualizar un objetivo existente
        $stmt = $pdo->prepare("SELECT * FROM objetivos_empresariales WHERE id_objetivo = ?");
        $stmt->execute([$id_objetivo]);
        $objetivo_anterior = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("UPDATE objetivos_empresariales SET 
            nombre = ?, descripcion = ?, kpi = ?, fecha_inicio = ?, fecha_fin = ?, 
            responsable = ?, estado = ?, observaciones = ?, fecha_actualizacion = NOW() 
            WHERE id_objetivo = ?");
        $stmt->execute([$nombre, $descripcion, $kpi, $fecha_inicio, $fecha_fin, $responsable, $estado, $observaciones, $id_objetivo]);

        foreach (['nombre', 'descripcion', 'kpi', 'fecha_inicio', 'fecha_fin', 'responsable', 'estado', 'observaciones'] as $campo) {
            if ($objetivo_anterior[$campo] != $$campo) {
                $stmt = $pdo->prepare("INSERT INTO his_cambios_objetivos (
                    id_objetivo, campo_modificado, valor_anterior, valor_nuevo, modificado_por
                ) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_objetivo, $campo, $objetivo_anterior[$campo], $$campo, $modificado_por]);
            }
        }

        $_SESSION['message'] = "Objetivo actualizado correctamente.";
    } else {
        // Código para insertar un nuevo objetivo
        $stmt = $pdo->prepare("INSERT INTO objetivos_empresariales (
            nombre, descripcion, area, kpi, progreso_actual, fecha_inicio, fecha_fin, 
            responsable, estado, observaciones, fecha_creacion, fecha_actualizacion
        ) VALUES (?, ?, 'ventas', ?, 0, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$nombre, $descripcion, $kpi, $fecha_inicio, $fecha_fin, $responsable, $estado, $observaciones]);

        $_SESSION['message'] = "Objetivo creado correctamente.";
    }

    // Redirige a la lista de objetivos
    header("Location:objetivos_list.php");
    exit;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>