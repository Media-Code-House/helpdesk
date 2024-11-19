<?php
session_start();

include '../header.php';
require_once '../../config/db.php';
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$objetivo = [
    'nombre' => '',
    'descripcion' => '',
    'area' => 'ventas',
    'kpi' => '',
    'progreso_actual' => 0,
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'responsable' => '', // Cambiado a campo de texto
    'estado' => 'no iniciado',
    'observaciones' => ''
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM objetivos_empresariales WHERE id_objetivo = ?");
    $stmt->execute([$id]);
    $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container">
    <h2><?php echo $id > 0 ? 'Editar Objetivo Empresarial' : 'Crear Objetivo Empresarial'; ?></h2>
    <form action="objetivos_guardar.php" method="POST">
        <input type="hidden" name="id_objetivo" value="<?php echo $id; ?>">

        <div class="form-group">
            <label for="nombre">Nombre del Objetivo</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required value="<?php echo htmlspecialchars($objetivo['nombre']); ?>">
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" required><?php echo htmlspecialchars($objetivo['descripcion']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="kpi">KPI Objetivo 'numero'</label>
            <input type="number" step="0.01" name="kpi" id="kpi" class="form-control" required value="<?php echo htmlspecialchars($objetivo['kpi']); ?>">
        </div>

        <div class="form-group">
            <label for="fecha_inicio">Fecha de Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required value="<?php echo htmlspecialchars($objetivo['fecha_inicio']); ?>">
        </div>

        <div class="form-group">
            <label for="fecha_fin">Fecha de Fin</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required value="<?php echo htmlspecialchars($objetivo['fecha_fin']); ?>">
        </div>

        <div class="form-group">
            <label for="responsable">Responsable</label>
            <input type="text" name="responsable" id="responsable" class="form-control" required value="<?php echo htmlspecialchars($objetivo['responsable']); ?>">
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select name="estado" id="estado" class="form-control" required>
                <option value="no iniciado" <?php echo $objetivo['estado'] == 'no iniciado' ? 'selected' : ''; ?>>No Iniciado</option>
                <option value="en progreso" <?php echo $objetivo['estado'] == 'en progreso' ? 'selected' : ''; ?>>En Progreso</option>
                <option value="en espera" <?php echo $objetivo['estado'] == 'en espera' ? 'selected' : ''; ?>>En Espera</option>
                <option value="revisión" <?php echo $objetivo['estado'] == 'revisión' ? 'selected' : ''; ?>>Revisión</option>
                <option value="completado" <?php echo $objetivo['estado'] == 'completado' ? 'selected' : ''; ?>>Completado</option>
                <option value="cancelado" <?php echo $objetivo['estado'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
            </select>
        </div>

        <div class="form-group">
            <label for="observaciones">Observaciones</label>
            <textarea name="observaciones" id="observaciones" class="form-control"><?php echo htmlspecialchars($objetivo['observaciones']); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo $id > 0 ? 'Actualizar Objetivo' : 'Crear Objetivo'; ?></button>
        <a href="objetivos_list.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
