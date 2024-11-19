<?php
session_start();
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';
include '../header.php';

$id_objetivo = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Obtener el objetivo principal
    $stmt = $pdo->prepare("SELECT nombre, kpi FROM objetivos_empresariales WHERE id_objetivo = ?");
    $stmt->execute([$id_objetivo]);
    $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si se encontró el objetivo
    if (!$objetivo) {
        $_SESSION['message'] = "Objetivo no encontrado.";
        header("Location: objetivos_list.php");
        exit;
    }

    // Obtener las tareas asociadas al objetivo
    $stmt = $pdo->prepare("SELECT * FROM objetivo_tareas WHERE id_objetivo = ?");
    $stmt->execute([$id_objetivo]);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular el progreso total del objetivo basado en las tareas
    $total_progreso = array_sum(array_column($tareas, 'progreso'));
    $total_kpi_tareas = array_sum(array_column($tareas, 'valor_kpi'));
    $porcentaje_total = $total_kpi_tareas > 0 ? ($total_progreso / $total_kpi_tareas) * 100 : 0;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>



<div class="container">
    <h4 class="center-align">Tareas del Objetivo: <?php echo htmlspecialchars($objetivo['nombre']); ?> (KPI Total: <?php echo $objetivo['kpi']; ?>)</h4>

    <!-- Barra de progreso total del objetivo -->
    <h5>Progreso Total del Objetivo</h5>
    <div class="progress">
        <div class="determinate" style="width: <?php echo number_format($porcentaje_total, 2); ?>%"></div>
    </div>
    <p><?php echo number_format($porcentaje_total, 2); ?>% completado</p>

    <!-- Formulario para crear nueva tarea -->
    <form action="guardar_tarea.php" method="POST">
        <input type="hidden" name="id_objetivo" value="<?php echo $id_objetivo; ?>">
        
        <h5>Agregar Nueva Tarea</h5>
        <div class="input-field">
            <label for="nombre_tarea">Nombre de la Tarea</label>
            <input type="text" name="nombre_tarea" id="nombre_tarea" required>
        </div>
        
        <div class="input-field">
            <label for="valor_kpi">Valor de KPI</label>
            <input type="number" name="valor_kpi" id="valor_kpi" required min="1">
        </div>
        
        <button type="submit" name="accion" value="crear" class="btn waves-effect waves-light blue">Agregar Tarea</button>
    </form>
    
    <!-- Formulario para actualizar tareas existentes -->
    <h5 class="mt-4">Tareas Existentes</h5>
    <?php if (empty($tareas)) : ?>
        <p>No hay tareas registradas para este objetivo.</p>
    <?php else : ?>
        <form action="guardar_tarea.php" method="POST">
            <input type="hidden" name="id_objetivo" value="<?php echo $id_objetivo; ?>">
            <table class="striped responsive-table">
                <thead>
                    <tr>
                        <th>Nombre de la Tarea</th>
                        <th>Valor de KPI</th>
                        <th>Progreso (%)</th>
                        <th>Comentario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $tarea) : 
                        $porcentaje_tarea = $tarea['valor_kpi'] > 0 ? ($tarea['progreso'] / $tarea['valor_kpi']) * 100 : 0;
                    ?>
                        <tr>
                            <td>
                                <input type="text" name="nombre_tarea[<?php echo $tarea['id_tarea']; ?>]" value="<?php echo htmlspecialchars($tarea['nombre_tarea']); ?>">
                            </td>
                            <td>
                                <input type="number" name="valor_kpi[<?php echo $tarea['id_tarea']; ?>]" value="<?php echo htmlspecialchars($tarea['valor_kpi']); ?>" min="1">
                            </td>
                            <td>
                                <!-- Barra de progreso de la tarea -->
                                <div class="progress">
                                    <div class="determinate" style="width: <?php echo number_format($porcentaje_tarea, 2); ?>%"></div>
                                </div>
                                <input type="number" name="progreso[<?php echo $tarea['id_tarea']; ?>]" value="<?php echo htmlspecialchars($tarea['progreso']); ?>" min="0" max="<?php echo $tarea['valor_kpi']; ?>" required>
                            </td>
                            <td>
                                <textarea name="comentario[<?php echo $tarea['id_tarea']; ?>]"><?php echo htmlspecialchars($tarea['comentario']); ?></textarea>
                            </td>
                            <td>
                                <button type="submit" name="accion" value="actualizar_<?php echo $tarea['id_tarea']; ?>" class="btn green">Guardar</button>
                                <a href="eliminar_tarea.php?id_tarea=<?php echo $tarea['id_tarea']; ?>&id_objetivo=<?php echo $id_objetivo; ?>" class="btn red" onclick="return confirm('¿Está seguro de eliminar esta tarea?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    <?php endif; ?>
</div>
