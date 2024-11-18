    <?php
    session_start();
    require_once '../../config/db.php';

    // Verificación de permisos de administrador
    if ($_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        echo json_encode(["error" => "Acceso denegado."]);
        exit;
    }

    $eventos = [];

    // Obtener gastos fijos e incluir fechas en el calendario
    $queryGastos = "SELECT * FROM gastos_fijos";
    $stmtGastos = $pdo->prepare($queryGastos);
    $stmtGastos->execute();
    $gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($gastos as $gasto) {
        if (!empty($gasto['fecha_inicial'])) {
            $fechaInicial = new DateTime($gasto['fecha_inicial']);
            $hoy = new DateTime();

            while ($fechaInicial <= $hoy->modify('+1 year')) {
                $eventos[] = [
                    'id' => $gasto['id_gasto'],
                    'title' => $gasto['descripcion'] . " (Gasto Fijo)",
                    'start' => $fechaInicial->format('Y-m-d'),
                    'color' => '#FF5733',
                    'tipo' => 'gasto_fijo'
                ];

                if ($gasto['periodo'] === 'mensual') {
                    $fechaInicial->modify('+1 month');
                } elseif ($gasto['periodo'] === 'semestral') {
                    $fechaInicial->modify('+6 months');
                } elseif ($gasto['periodo'] === 'anual') {
                    $fechaInicial->modify('+1 year');
                } else {
                    break;
                }
            }
        }
    }

    // Obtener incidencias
    $queryIncidencias = "SELECT e.id_estimacion, e.fecha_estimacion, i.titulo AS descripcion, 
                        i.prioridad, i.estado, i.descripcion AS incidencia_descripcion
                        FROM estimaciones_incidencias e
                        JOIN incidencias i ON e.id_incidencia = i.id_incidencia";
    $stmtIncidencias = $pdo->prepare($queryIncidencias);
    $stmtIncidencias->execute();
    $incidencias = $stmtIncidencias->fetchAll(PDO::FETCH_ASSOC);

    foreach ($incidencias as $incidencia) {
        $eventos[] = [
            'id' => $incidencia['id_estimacion'],
            'title' => $incidencia['descripcion'] . " (Incidencia)",
            'start' => $incidencia['fecha_estimacion'],
            'color' => '#33FF57',
            'tipo' => 'incidencia',
            'extendedProps' => [
                'descripcion' => $incidencia['incidencia_descripcion'],
                'prioridad' => $incidencia['prioridad'],
                'estado' => $incidencia['estado']
            ]
        ];
    }

    // Obtener eventos adicionales de la tabla `eventos`
    $queryEventos = "SELECT * FROM eventos";
    $stmtEventos = $pdo->prepare($queryEventos);
    $stmtEventos->execute();
    $listaEventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($listaEventos as $evento) {
        $eventos[] = [
            'id' => $evento['id'],
            'title' => $evento['descripcion'] . " (Evento)",
            'start' => $evento['fecha'] . 'T' . $evento['hora'],
            'color' => '#FF0000',
            'tipo' => 'evento'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($eventos);
