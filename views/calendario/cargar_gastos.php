<?php
require_once '../../config/db.php';


// Obtener los gastos fijos actualizados
$query = "SELECT * FROM gastos_fijos";
$stmt = $pdo->prepare($query);
$stmt->execute();
$gastos_fijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Contenedor de lista con límite de altura y barra de desplazamiento -->
<ul id="listaGastosFijos" class="collection" style="max-height: 200px; overflow-y: auto;">
    <?php foreach ($gastos_fijos as $gasto): ?>
        <li class="collection-item">
            <span>
                <strong><?php echo htmlspecialchars($gasto['descripcion']); ?>:</strong> 
                <?php echo '$' . number_format($gasto['monto_estimado'], 2); ?> 
                <em>(Día de Pago: <?php echo htmlspecialchars($gasto['periodicidad']); ?>)</em>
            </span>
        </li>
    <?php endforeach; ?>
</ul>
