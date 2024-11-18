<?php
session_start();
require_once '../../config/db.php';

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado."]);
    exit;
}

// Obtener el ID del gasto fijo desde la solicitud GET
$id_gasto = isset($_GET['id_gasto']) ? intval($_GET['id_gasto']) : 0;

// Verificar si el ID es válido
if ($id_gasto > 0) {
    // Consulta para obtener los detalles del gasto fijo
    $query = "SELECT * FROM gastos_fijos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id_gasto, PDO::PARAM_INT);
    $stmt->execute();
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gasto) {
        echo json_encode($gasto);
    } else {
        echo json_encode(["error" => "Gasto fijo no encontrado."]);
    }
} else {
    echo json_encode(["error" => "ID de gasto fijo no válido."]);
}
?>
