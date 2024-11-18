<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

// Verificar que se hayan recibido los datos necesarios
if (!isset($_POST['descripcion'], $_POST['fecha'], $_POST['hora'])) {
    http_response_code(400);
    echo "Datos incompletos.";
    exit;
}

$descripcion = $_POST['descripcion'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];

// Guardar el evento en la base de datos
$query = "INSERT INTO eventos (descripcion, fecha, hora) VALUES (:descripcion, :fecha, :hora)";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':fecha', $fecha);
$stmt->bindParam(':hora', $hora);

if ($stmt->execute()) {
    echo "Evento guardado exitosamente.";
} else {
    http_response_code(500);
    echo "Error al guardar el evento.";
}
?>
