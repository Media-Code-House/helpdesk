<?php
require_once '../../config/db.php';






include '../header.php';
if (isset($_GET['id_gasto'])) {
    $id_gasto = $_GET['id_gasto'];

    // Obtener información del gasto
    $query = "SELECT * FROM gastos_fijos WHERE id_gasto = :id_gasto";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_gasto', $id_gasto);
    $stmt->execute();
    $gasto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gasto) {
        echo '<form id="formEditar" method="POST" action="gastos_fijos_actualizar.php">';
        echo '<input type="hidden" name="id_gasto" value="' . htmlspecialchars($id_gasto) . '">';
        echo '<div class="input-field"><input type="text" name="descripcion" value="' . htmlspecialchars($gasto['descripcion']) . '" required><label>Descripción</label></div>';
        echo '<div class="input-field"><input type="number" step="0.01" name="monto_estimado" value="' . htmlspecialchars($gasto['monto_estimado']) . '" required><label>Monto Estimado</label></div>';
        echo '<div class="input-field"><input type="date" name="fecha_pago" value="' . htmlspecialchars($gasto['fecha_pago']) . '" required><label>Fecha de Pago</label></div>';
        echo '<button type="submit" class="btn">Guardar Cambios</button>';
        echo '</form>';
        echo '<form id="formMarcarPagado" method="POST" action="gastos_fijos_pagado.php">';
        echo '<input type="hidden" name="id_gasto" value="' . htmlspecialchars($id_gasto) . '">';
        echo '<button type="submit" class="btn red">Marcar como Pagado</button>';
        echo '</form>';
    } else {
        echo '<p>Gasto no encontrado.</p>';
    }
}
?>
