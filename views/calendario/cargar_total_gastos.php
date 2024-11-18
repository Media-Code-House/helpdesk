<?php
session_start();
require_once '../../config/db.php';

include '../header.php';

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

try {
    // Consulta para obtener el total de los gastos fijos
    $query = "SELECT SUM(monto_estimado) AS total_gastos FROM gastos_fijos";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Mostrar el total de gastos o 0.00 si no hay datos
    echo isset($result['total_gastos']) ? number_format($result['total_gastos'], 2, '.', '') : "0.00";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error al calcular el total de gastos fijos.";
    exit;
}
?>
