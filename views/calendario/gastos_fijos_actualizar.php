<?php
require_once '../../config/db.php';






include '../header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_gasto = $_POST['id_gasto'];
    $descripcion = $_POST['descripcion'];
    $monto_estimado = $_POST['monto_estimado'];
    $fecha_pago = $_POST['fecha_pago'];

    $query = "UPDATE gastos_fijos SET descripcion = :descripcion, monto_estimado = :monto_estimado, fecha_pago = :fecha_pago WHERE id_gasto = :id_gasto";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':monto_estimado', $monto_estimado);
    $stmt->bindParam(':fecha_pago', $fecha_pago);
    $stmt->bindParam(':id_gasto', $id_gasto);
    
    if ($stmt->execute()) {
        echo "Gasto actualizado correctamente.";
    } else {
        echo "Error al actualizar el gasto.";
    }
}
?>
