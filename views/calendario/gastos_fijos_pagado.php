<?php
session_start();
require_once '../../config/db.php';






include '../header.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_gasto'])) {
    $id_gasto = $_POST['id_gasto'];
    
    // Obtener datos del gasto fijo
    $queryGasto = "SELECT * FROM gastos_fijos WHERE id_gasto = :id_gasto";
    $stmtGasto = $pdo->prepare($queryGasto);
    $stmtGasto->bindParam(':id_gasto', $id_gasto);
    $stmtGasto->execute();
    $gasto = $stmtGasto->fetch(PDO::FETCH_ASSOC);

    if ($gasto) {
        $descripcion = $gasto['descripcion'];
        $valor = $gasto['monto_estimado'];
        $usuario = $gasto['usuario'];
        $banco = $gasto['banco'];
        $tipo_transaccion = 'egreso';
        $categoria = $gasto['categoria'];

        // Insertar la transacción en la tabla `cuenta`
        $queryCuenta = "INSERT INTO cuenta (banco, descripcion, usuario, valor, tipo_transaccion, categoria)
                        VALUES (:banco, :descripcion, :usuario, :valor, :tipo_transaccion, :categoria)";
        $stmtCuenta = $pdo->prepare($queryCuenta);
        $stmtCuenta->bindParam(':banco', $banco);
        $stmtCuenta->bindParam(':descripcion', $descripcion);
        $stmtCuenta->bindParam(':usuario', $usuario);
        $stmtCuenta->bindParam(':valor', $valor);
        $stmtCuenta->bindParam(':tipo_transaccion', $tipo_transaccion);
        $stmtCuenta->bindParam(':categoria', $categoria);
        
        if ($stmtCuenta->execute()) {
            // Marcar el gasto fijo como pagado
            $queryUpdate = "UPDATE gastos_fijos SET pagado = TRUE WHERE id_gasto = :id_gasto";
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->bindParam(':id_gasto', $id_gasto);
            $stmtUpdate->execute();
            echo "Gasto marcado como pagado y registrado en cuentas.";
        } else {
            echo "Error al registrar la transacción.";
        }
    } else {
        echo "Gasto fijo no encontrado.";
    }
}
?>
