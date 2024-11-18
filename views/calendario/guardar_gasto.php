<?php
session_start();
require_once '../../config/db.php';
// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

// Verificar que se hayan recibido los datos necesarios
if (!isset($_POST['descripcion'], $_POST['monto'], $_POST['periodo'], $_POST['periodicidad'])) {
    http_response_code(400);
    echo "Datos incompletos.";
    exit;
}

// Obtener los datos del formulario
$descripcion = $_POST['descripcion'];
$monto = $_POST['monto'];
$periodo = $_POST['periodo'];
$periodicidad = (int) $_POST['periodicidad'];

// Validar que periodicidad esté entre 1 y 28
if ($periodicidad < 1 || $periodicidad > 28) {
    http_response_code(400);
    echo "El día de pago debe estar entre 1 y 28.";
    exit;
}

// Guardar el gasto fijo en la base de datos
if (isset($_POST['id_gasto']) && !empty($_POST['id_gasto'])) {
    // Actualizar gasto fijo existente
    $id_gasto = $_POST['id_gasto'];
    $query = "UPDATE gastos_fijos SET descripcion = :descripcion, monto_estimado = :monto, periodo = :periodo, periodicidad = :periodicidad WHERE id = :id_gasto";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_gasto', $id_gasto, PDO::PARAM_INT);
} else {
    // Insertar nuevo gasto fijo
    $query = "INSERT INTO gastos_fijos (descripcion, monto_estimado, periodo, periodicidad) VALUES (:descripcion, :monto, :periodo, :periodicidad)";
    $stmt = $pdo->prepare($query);
}

$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':monto', $monto);
$stmt->bindParam(':periodo', $periodo);
$stmt->bindParam(':periodicidad', $periodicidad);

if ($stmt->execute()) {
    echo "Gasto fijo guardado exitosamente.";
} else {
    http_response_code(500);
    echo "Error al guardar el gasto fijo.";
}
?>
