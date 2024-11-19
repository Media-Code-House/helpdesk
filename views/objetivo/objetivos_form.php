<?php
session_start();
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';
include '../header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$objetivo = [
    'nombre' => '',
    'descripcion' => '',
    'area' => 'ventas',
    'kpi' => '',
    'progreso_actual' => 0,
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'responsable' => '',
    'estado' => 'no iniciado',
    'observaciones' => ''
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM objetivos_empresariales WHERE id_objetivo = ?");
    $stmt->execute([$id]);
    $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$objetivo) {
        $_SESSION['message'] = "Objetivo no encontrado.";
        header("Location: objetivos_list.php");
        exit;
    }
}
?>

<div class="container">
    <h4 class="center-align"><?php echo $id > 0 ? 'Editar Objetivo Empresarial' : 'Crear Objetivo Empresarial'; ?></h4>
    <form action="objetivos_guardar.php" method="POST">
        <input type="hidden" name="id_objetivo" value="<?php echo $id; ?>">

        <div class="input-field">
            <label for="nombre" class="active">Nombre del Objetivo</label>
            <input type="text" name="nombre" id="nombre" required value="<?php echo htmlspecialchars($objetivo['nombre']); ?>">
        </div>

        <div class="input-field">
            <label for="descripcion" class="active">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="materialize-textarea" required><?php echo htmlspecialchars($objetivo['descripcion']); ?></textarea>
        </div>

        <div class="input-field">
            <label for="kpi" class="active">KPI Objetivo</label>
            <input type="number" step="0.01" name="kpi" id="kpi" required value="<?php echo htmlspecialchars($objetivo['kpi']); ?>">
        </div>

        <div class="input-field">
            <label for="fecha_inicio" class="active">Fecha de Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="datepicker" required value="<?php echo htmlspecialchars($objetivo['fecha_inicio']); ?>">
        </div>

        <div class="input-field">
            <label for="fecha_fin" class="active">Fecha de Fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="datepicker" required value="<?php echo htmlspecialchars($objetivo['fecha_fin']); ?>">
        </div>

        <div class="input-field">
            <label for="responsable" class="active">Responsable</label>
            <input type="text" name="responsable" id="responsable" required value="<?php echo htmlspecialchars($objetivo['responsable']); ?>">
        </div>

        <label>Estado</label>
        <div class="input-field">
            <select name="estado" class="browser-default" required>
                <option value="no iniciado" <?php echo $objetivo['estado'] == 'no iniciado' ? 'selected' : ''; ?>>No Iniciado</option>
                <option value="en progreso" <?php echo $objetivo['estado'] == 'en progreso' ? 'selected' : ''; ?>>En Progreso</option>
                <option value="en espera" <?php echo $objetivo['estado'] == 'en espera' ? 'selected' : ''; ?>>En Espera</option>
                <option value="revisión" <?php echo $objetivo['estado'] == 'revisión' ? 'selected' : ''; ?>>Revisión</option>
                <option value="completado" <?php echo $objetivo['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                <option value="cancelado" <?php echo $objetivo['estado'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
            </select>
        </div>

        <div class="input-field">
            <label for="observaciones" class="active">Observaciones</label>
            <textarea name="observaciones" id="observaciones" class="materialize-textarea"><?php echo htmlspecialchars($objetivo['observaciones']); ?></textarea>
        </div>

        <button type="submit" class="btn waves-effect waves-light blue"><?php echo $id > 0 ? 'Actualizar Objetivo' : 'Crear Objetivo'; ?></button>
        <a href="objetivos_list.php" class="btn waves-effect waves-light grey">Cancelar</a>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var elems = document.querySelectorAll('select');
        M.FormSelect.init(elems);

        var dateElems = document.querySelectorAll('.datepicker');
        M.Datepicker.init(dateElems, {
            format: 'yyyy-mm-dd'
        });
    });
</script>
