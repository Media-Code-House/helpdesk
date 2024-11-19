<?php
session_start();
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';






include '../header.php';

$id_objetivo = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar que el ID es válido y que el objetivo existe
if ($id_objetivo <= 0) {
    echo "<div class='container'><p>ID de objetivo no válido.</p></div>";
    exit;
}

$stmt = $pdo->prepare("SELECT nombre FROM objetivos_empresariales WHERE id_objetivo = ?");
$stmt->execute([$id_objetivo]);
$objetivo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$objetivo) {
    echo "<div class='container'><p>Objetivo no encontrado.</p></div>";
    exit;
}

// Obtener el historial de cambios
$stmt = $pdo->prepare("SELECT * FROM his_cambios_objetivos WHERE id_objetivo = ? ORDER BY fecha_modificacion DESC");
$stmt->execute([$id_objetivo]);
$cambios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h4 class="center-align">Historial de Cambios para el Objetivo: <?php echo htmlspecialchars($objetivo['nombre']); ?></h4>

    <?php if (empty($cambios)) : ?>
        <p>No hay cambios registrados para este objetivo.</p>
    <?php else : ?>
        <table class="striped responsive-table">
            <thead>
                <tr>
                    <th>Campo Modificado</th>
                    <th>Valor Anterior</th>
                    <th>Valor Nuevo</th>
                    <th>Fecha de Modificación</th>
                    <th>Modificado por</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cambios as $cambio) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cambio['campo_modificado']); ?></td>
                        <td><?php echo htmlspecialchars($cambio['valor_anterior']); ?></td>
                        <td><?php echo htmlspecialchars($cambio['valor_nuevo']); ?></td>
                        <td><?php echo htmlspecialchars($cambio['fecha_modificacion']); ?></td>
                        <td><?php echo htmlspecialchars($cambio['modificado_por']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="center-align">
        <a href="objetivos_list.php" class="btn waves-effect waves-light grey">Volver a la Lista de Objetivos</a>
    </div>
</div>
