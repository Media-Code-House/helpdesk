<?php
session_start();
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/db.php';
include '../header.php';

try {
    // Parámetros de paginación
    $results_per_page = 20;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $results_per_page;

    // Filtros de búsqueda
    $filtros = [];
    $params = [];

    if (!empty($_GET['id'])) {
        $filtros[] = 'id_objetivo = :id';
        $params[':id'] = $_GET['id'];
    }

    if (!empty($_GET['nombre'])) {
        $filtros[] = 'nombre LIKE :nombre';
        $params[':nombre'] = '%' . $_GET['nombre'] . '%';
    }

    if (!empty($_GET['estado'])) {
        $filtros[] = 'estado = :estado';
        $params[':estado'] = $_GET['estado'];
    }

    if (!empty($_GET['responsable'])) {
        $filtros[] = 'responsable LIKE :responsable';
        $params[':responsable'] = '%' . $_GET['responsable'] . '%';
    }

    // Construir la consulta con filtros y paginación
    $filtro_sql = !empty($filtros) ? 'WHERE ' . implode(' AND ', $filtros) : '';
    $query = "SELECT * FROM objetivos_empresariales $filtro_sql LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $objetivos_base = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar el total de registros (para la paginación)
    $query_count = "SELECT COUNT(*) FROM objetivos_empresariales $filtro_sql";
    $stmt_count = $pdo->prepare($query_count);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_records = $stmt_count->fetchColumn();
    $total_pages = ceil($total_records / $results_per_page);

    // Preparar el array de objetivos con progreso actualizado
    $total_kpi = 0;
    $total_progreso = 0;
    $objetivos_completados = 0;
    $objetivos = [];

    foreach ($objetivos_base as $objetivo) {
        $id_objetivo = $objetivo['id_objetivo'];
        $stmt_tareas = $pdo->prepare("SELECT SUM(progreso) as progreso_total, SUM(valor_kpi) as kpi_total FROM objetivo_tareas WHERE id_objetivo = ?");
        $stmt_tareas->execute([$id_objetivo]);
        $tareas_progreso = $stmt_tareas->fetch(PDO::FETCH_ASSOC);

        $progreso_actual = $tareas_progreso['progreso_total'] ?? 0;
        $kpi_total = $tareas_progreso['kpi_total'] ?? $objetivo['kpi'];
        
        // Calcula el porcentaje de cumplimiento del objetivo
        $objetivo['progreso_actual'] = $kpi_total > 0 ? ($progreso_actual / $kpi_total) * 100 : 0;

        // Acumular datos para el resumen general
        $total_kpi += $kpi_total;
        $total_progreso += $progreso_actual;
        if ($objetivo['estado'] === 'completado') {
            $objetivos_completados++;
        }

        $objetivos[] = $objetivo;
    }

    // Cálculo del resumen de cumplimiento
    $porcentaje_cumplimiento = $total_kpi > 0 ? ($total_progreso / $total_kpi) * 100 : 0;
    $porcentaje_objetivos_completados = $total_records > 0 ? ($objetivos_completados / $total_records) * 100 : 0;
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

if (isset($_SESSION['message'])) {
    echo "<div class='card-panel green lighten-4 green-text text-darken-4'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message']);
}
?>

<div class="container">
    <h4 class="center-align">Resumen de Cumplimiento de Objetivos</h4>
    <div class="row">
        <div class="col s12 m6">
            <div class="card blue lighten-4">
                <div class="card-content">
                    <span class="card-title">Porcentaje de Cumplimiento Total</span>
                    <p><?php echo number_format($porcentaje_cumplimiento, 2); ?>%</p>
                </div>
            </div>
        </div>
        <div class="col s12 m6">
            <div class="card green lighten-4">
                <div class="card-content">
                    <span class="card-title">Objetivos Completados</span>
                    <p><?php echo number_format($porcentaje_objetivos_completados, 2); ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="center-align">Lista de Objetivos Empresariales</h4>
    <div class="row">
        <a href="objetivos.php" class="btn waves-effect waves-light blue">Crear Nuevo Objetivo</a>
    </div>

    <!-- Formulario de filtros -->
    <div class="row">
        <form method="GET" action="" class="col s12">
            <div class="input-field col s3">
                <input type="text" name="id" id="filter-id" placeholder="Filtrar por ID" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
            </div>
            <div class="input-field col s3">
                <input type="text" name="nombre" id="filter-nombre" placeholder="Filtrar por Nombre" value="<?php echo isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : ''; ?>">
            </div>
            <div class="input-field col s3">
                <input type="text" name="estado" id="filter-estado" placeholder="Filtrar por Estado" value="<?php echo isset($_GET['estado']) ? htmlspecialchars($_GET['estado']) : ''; ?>">
            </div>
            <div class="input-field col s3">
                <input type="text" name="responsable" id="filter-responsable" placeholder="Filtrar por Responsable" value="<?php echo isset($_GET['responsable']) ? htmlspecialchars($_GET['responsable']) : ''; ?>">
            </div>
            <button type="submit" class="btn waves-effect waves-light blue">Filtrar</button>
        </form>
    </div>

    <!-- Tabla con scroll y paginación -->
    <div style="max-height: 400px; overflow-y: auto;">
        <table class="striped responsive-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>KPI Objetivo</th>
                    <th>Progreso (%)</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha de Fin</th>
                    <th>Estado</th>
                    <th>Responsable</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($objetivos as $objetivo) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($objetivo['id_objetivo']); ?></td>
                        <td><?php echo htmlspecialchars($objetivo['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($objetivo['kpi']); ?></td>
                        <td><?php echo number_format($objetivo['progreso_actual'], 2); ?>%</td>
                        <td><?php echo htmlspecialchars($objetivo['fecha_inicio']); ?></td>
                        <td><?php echo htmlspecialchars($objetivo['fecha_fin']); ?></td>
                        <td><?php echo htmlspecialchars($objetivo['estado']); ?></td>
                        <td><?php echo htmlspecialchars($objetivo['responsable']); ?></td>
                        <td>
                            <a href="objetivos_form.php?id=<?php echo $objetivo['id_objetivo']; ?>" class="btn yellow darken-2 waves-effect waves-light">Editar</a>
                            <a href="objetivo_detalle.php?id=<?php echo $objetivo['id_objetivo']; ?>" class="btn green darken-1 accent-3 waves-effect waves-light">Ver Detalles</a>
                            <a href="tareas_objetivo.php?id=<?php echo $objetivo['id_objetivo']; ?>" class="btn purple waves-effect waves-light">Tareas</a>
                            <a href="objetivos_historial.php?id=<?php echo $objetivo['id_objetivo']; ?>" class="btn blue waves-effect waves-light">Historial</a>
                            <a href="objetivos_eliminar.php?id=<?php echo $objetivo['id_objetivo']; ?>" class="btn red darken-1 waves-effect waves-light" onclick="return confirm('¿Está seguro de eliminar este objetivo?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Enlaces de paginación -->
    <div class="pagination center-align">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="btn blue">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i == $page ? 'blue darken-2' : 'blue lighten-1'; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="btn blue">Siguiente</a>
        <?php endif; ?>
    </div>
</div>
