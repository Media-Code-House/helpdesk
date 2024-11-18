<?php
session_start();
require_once '../../config/db.php';

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../header.php';

// Obtener la lista de gastos fijos y calcular el total
$query = "SELECT * FROM gastos_fijos";
$stmt = $pdo->prepare($query);
$stmt->execute();
$gastos_fijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_gastos = $gastos_fijos ? array_sum(array_column($gastos_fijos, 'monto_estimado')) : 0;
?>
<!DOCTYPE html>
<html lang='es'>

<head>
    <meta charset='utf-8' />
    <title>Calendario de Gastos Fijos y Estimaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h3 class="center-align">Calendario de Gastos Fijos y Estimaciones de Incidencias</h3>

        <!-- Mostrar total de gastos -->
        <div class="section">
            <h5>Total Gastos Fijos: $<?php echo number_format($total_gastos, 2); ?></h5>
        </div>

        <!-- Botón para agregar gasto fijo -->
        <div class="right-align">
            <button class="btn modal-trigger btnAgregarGasto" href="#modalGasto">Agregar Gasto Fijo</button>
        </div>

        <!-- Sección de gastos fijos en la parte superior -->
        <div class="section">
            <h5>Gastos Fijos</h5>
            <ul id="listaGastosFijos" class="collection" style="max-height: 200px; overflow-y: auto;">
                <?php foreach ($gastos_fijos as $gasto): ?>
                    <li class="collection-item gasto-item" data-id="<?php echo $gasto['id']; ?>" style="cursor: pointer;">
                        <span><strong><?php echo htmlspecialchars($gasto['descripcion']); ?>:</strong>
                            <?php echo '$' . number_format($gasto['monto_estimado'], 2); ?>
                            <em>(Día de Pago: <?php echo htmlspecialchars($gasto['periodicidad']); ?>)</em></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Modal de Detalles del Gasto Fijo -->
        <div id="modalDetallesGasto" class="modal">
            <div class="modal-content">
                <h4>Detalles del Gasto Fijo</h4>
                <p><strong>Descripción:</strong> <span id="detalleDescripcion"></span></p>
                <p><strong>Monto Estimado:</strong> $<span id="detalleMonto"></span></p>
                <p><strong>Periodicidad:</strong> <span id="detallePeriodicidad"></span></p>
                <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                <p><strong>Período:</strong> <span id="detallePeriodo"></span></p>
            </div>
        </div>

        <!-- Modal de Agregar o Editar Gasto Fijo -->
        <div id="modalGasto" class="modal">
            <div class="modal-content">
                <h4>Agregar o Editar Gasto Fijo</h4>
                <form id="formGasto">
                    <div class="input-field">
                        <input type="text" name="descripcion" id="descripcion" required>
                        <label for="descripcion">Descripción</label>
                    </div>
                    <div class="input-field">
                        <input type="number" step="0.01" name="monto" id="monto" required>
                        <label for="monto">Monto Estimado</label>
                    </div>
                    <div class="input-field">
                        <select name="periodo" id="periodo" required>
                            <option value="" disabled selected>Selecciona el Periodo</option>
                            <option value="mensual">Mensual</option>
                            <option value="semestral">Cada 6 Meses</option>
                            <option value="anual">Anual</option>
                        </select>
                        <label for="periodo">Período</label>
                    </div>
                    <div class="input-field">
                        <input type="number" name="periodicidad" id="periodicidad" min="1" max="28" required>
                        <label for="periodicidad">Día de Pago (1-28)</label>
                    </div>
                    <button type="submit" class="btn">Guardar</button>
                </form>
            </div>
        </div>

        <!-- Calendario -->
        <div id='calendar'></div>
        <div id="modalDetallesGasto" class="modal">
            <div class="modal-content">
                <h4>Detalles del Gasto Fijo</h4>
                <p><strong>Descripción:</strong> <span id="detalleDescripcion"></span></p>
                <p><strong>Monto Estimado:</strong> $<span id="detalleMonto"></span></p>
                <p><strong>Periodicidad:</strong> <span id="detallePeriodicidad"></span></p>
                <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                <p><strong>Período:</strong> <span id="detallePeriodo"></span></p>
            </div>
        </div>

        <!-- JavaScript para FullCalendar y funciones adicionales -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: '/views/calendario/cargar_eventos.php', // Archivo para cargar eventos e incidencias
                    eventClick: function(info) {
                        var event = info.event;

                        if (event.extendedProps.tipo === 'incidencia') {
                            alert('Incidencia: ' + event.title +
                                '\nFecha estimada: ' + event.start.toISOString().split('T')[0] +
                                '\nDescripción: ' + event.extendedProps.descripcion +
                                '\nPrioridad: ' + event.extendedProps.prioridad +
                                '\nEstado: ' + event.extendedProps.estado);
                        } else if (event.extendedProps.tipo === 'gasto_fijo') {
                            // Llamada AJAX para obtener los detalles del gasto fijo
                            $.ajax({
                                url: '/views/calendario/obtener_gasto.php',
                                type: 'GET',
                                data: {
                                    id_gasto: event.id
                                },
                                dataType: 'json',
                                success: function(data) {
                                    if (!data.error) {
                                        $('#detalleDescripcion').text(data.descripcion);
                                        $('#detalleMonto').text(parseFloat(data.monto_estimado).toFixed(2));
                                        $('#detallePeriodicidad').text(data.periodicidad);
                                        $('#detalleEstado').text(data.estado);
                                        $('#detallePeriodo').text(data.periodo);

                                        // Abrir el modal con los detalles del gasto fijo
                                        M.Modal.getInstance(document.getElementById('modalDetallesGasto')).open();
                                    } else {
                                        alert(data.error);
                                    }
                                },
                                error: function() {
                                    alert('Error al obtener los detalles del gasto fijo.');
                                }
                            });
                        } else if (event.extendedProps.tipo === 'evento') {
                            alert('Evento: ' + event.title + '\nFecha y Hora: ' + event.start.toISOString());
                        }
                    },
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    }
                });

                calendar.render();

                // Inicializar el modal para detalles del gasto fijo y habilitar el cierre al hacer clic fuera
                var modalDetalles = M.Modal.init(document.getElementById('modalDetallesGasto'), {
                    dismissible: true // Permite cerrar el modal al hacer clic fuera de él
                });

                // Añadir evento de clic a cada elemento de gasto fijo en la lista
                document.querySelectorAll('.gasto-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        var idGasto = this.getAttribute('data-id');

                        // Llamada AJAX para obtener detalles del gasto fijo
                        $.ajax({
                            url: '/views/calendario/obtener_gasto.php',
                            type: 'GET',
                            data: {
                                id_gasto: idGasto
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (!data.error) {
                                    $('#detalleDescripcion').text(data.descripcion);
                                    $('#detalleMonto').text(parseFloat(data.monto_estimado).toFixed(2));
                                    $('#detallePeriodicidad').text(data.periodicidad);
                                    $('#detalleEstado').text(data.estado);
                                    $('#detallePeriodo').text(data.periodo);

                                    // Mostrar el modal con los detalles del gasto fijo
                                    modalDetalles.open();
                                } else {
                                    alert(data.error);
                                }
                            },
                            error: function() {
                                alert('Error al obtener los detalles del gasto fijo.');
                            }
                        });
                    });
                });

                // Abrir modal para agregar o editar gasto fijo
                document.querySelector('.btnAgregarGasto').addEventListener('click', function() {
                    document.getElementById('formGasto').reset();
                    M.Modal.getInstance(document.getElementById('modalGasto')).open();
                });

                // Enviar el formulario para guardar gasto fijo
                document.getElementById('formGasto').addEventListener('submit', function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: '/views/calendario/guardar_gasto.php',
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            alert(response);
                            calendar.refetchEvents(); // Refresca el calendario
                            $.ajax({
                                url: '/views/calendario/cargar_gastos.php',
                                type: 'GET',
                                success: function(html) {
                                    $('#listaGastosFijos').html(html);
                                    $.get('/views/calendario/cargar_total_gastos.php', function(total) {
                                        $('h5').first().html("Total Gastos Fijos: $" + parseFloat(total).toFixed(2));
                                    });
                                    M.Modal.getInstance(document.getElementById('modalGasto')).close();
                                }
                            });
                        }
                    });
                });
            });
        </script>



        <!-- Materialize JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                M.AutoInit();
            });
        </script>
    </div>
</body>

</html>