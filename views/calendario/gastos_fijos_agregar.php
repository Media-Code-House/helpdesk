<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $descripcion = $_POST['descripcion'];
    $monto_estimado = $_POST['monto_estimado'];
    $fecha_pago = $_POST['fecha_pago'];
    $periodo = $_POST['periodo'];
    $banco = $_POST['banco'];
    $usuario = $_SESSION['usuario'];  // Usuario autenticado
    $tipo_transaccion = 'egreso';
    $categoria = $_POST['categoria'];

    $query = "INSERT INTO gastos_fijos (descripcion, monto_estimado, fecha_pago, periodo, banco, usuario, tipo_transaccion, categoria)
              VALUES (:descripcion, :monto_estimado, :fecha_pago, :periodo, :banco, :usuario, :tipo_transaccion, :categoria)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':monto_estimado', $monto_estimado);
    $stmt->bindParam(':fecha_pago', $fecha_pago);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->bindParam(':banco', $banco);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':tipo_transaccion', $tipo_transaccion);
    $stmt->bindParam(':categoria', $categoria);

    if ($stmt->execute()) {
        echo "Gasto fijo añadido exitosamente.";
    } else {
        echo "Error al añadir el gasto fijo.";
    }
}
?>
