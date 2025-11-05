<?php
include 'includes/admin_header.php';

// Obtener datos para los dropdowns de rutas
$origenes_result = $conn->query("SELECT DISTINCT origen FROM rutas ORDER BY origen");
$destinos_result = $conn->query("SELECT DISTINCT destino FROM rutas ORDER BY destino");

// Variables para los formularios
$report_id = $_GET['report_id'] ?? '1';
$p_origen = $_GET['origen'] ?? '';
$p_destino = $_GET['destino'] ?? '';
$p_fecha = $_GET['fecha'] ?? date('Y-m-d');
// --- CORREGIDO ---
// Asegurarse que el turno tenga un valor válido, ej: '00:00' si está vacío
$p_turno = $_GET['turno'] ?? date('H:i');
if(empty($p_turno)) $p_turno = '00:00';


$report_title = "";
$results = null;
$error_msg = "";

// Procesar la solicitud del reporte
if (isset($_GET['run_report'])) {
    
    try {
        // Reportes 1-4
        if (in_array($report_id, ['1', '2', '3', '4'])) {
            if (empty($p_origen) || empty($p_destino) || empty($p_fecha) || empty($p_turno)) {
                $error_msg = "Para este reporte, debe especificar Origen, Destino, Fecha y Turno.";
            } else {
                
                // --- CORRECCIÓN 1: Combinar Fecha y Turno en un solo DATETIME ---
                // El SP espera un DATETIME (ej: '2025-12-26 22:00:00')
                // No podemos pasarle la fecha y la hora por separado.
                $p_fecha_partida_completa = $p_fecha . ' ' . $p_turno;
                // Si el turno no incluye segundos, MySQL podría necesitar que se los añadas:
                if(strlen($p_turno) == 5) { // Si es HH:MM
                     $p_fecha_partida_completa = $p_fecha . ' ' . $p_turno . ':00';
                }

                switch ($report_id) {
                    case '1':
                        $report_title = "Reporte 1: Lista de Pasajeros y Tripulantes";
                        $stmt = $conn->prepare("CALL sp_reporte_pasajeros_tripulantes(?, ?, ?)");
                        // --- CORRECCIÓN 1 (bind): Usamos 3 params (sss) y la fecha completa
                        $stmt->bind_param("sss", $p_origen, $p_destino, $p_fecha_partida_completa);
                        break;
                    case '2':
                        $report_title = "Reporte 2: Cantidad de Asientos Online";
                        // --- CORRECCIÓN 2 (Nombre SP): 'sp_reporte_asientos_online'
                        $stmt = $conn->prepare("CALL sp_reporte_asientos_online(?, ?, ?)");
                        // --- CORRECCIÓN 1 (bind):
                        $stmt->bind_param("sss", $p_origen, $p_destino, $p_fecha_partida_completa);
                        break;
                    case '3':
                        $report_title = "Reporte 3: Cantidad de Asientos Presencial";
                        // --- CORRECCIÓN 2 (Nombre SP): 'sp_reporte_asientos_presencial'
                        $stmt = $conn->prepare("CALL sp_reporte_asientos_presencial(?, ?, ?)");
                        // --- CORRECCIÓN 1 (bind):
                        $stmt->bind_param("sss", $p_origen, $p_destino, $p_fecha_partida_completa);
                        break;
                    case '4':
                        $report_title = "Reporte 4: Monto de Venta por Pisos";
                        // --- CORRECCIÓN 2 (Nombre SP): 'sp_reporte_monto_por_piso'
                        $stmt = $conn->prepare("CALL sp_reporte_monto_por_piso(?, ?, ?)");
                        // --- CORRECCIÓN 1 (bind):
                        $stmt->bind_param("sss", $p_origen, $p_destino, $p_fecha_partida_completa);
                        break;
                }
                $stmt->execute();
                $results = $stmt->get_result();
                $stmt->close();
            }
        }
        // Reporte 5 (Este ya estaba bien)
        elseif ($report_id == '5') {
            if (empty($p_fecha)) {
                $error_msg = "Para este reporte, debe especificar una Fecha.";
            } else {
                $report_title = "Reporte 5: Viajes con >80% de Capacidad";
                // El SP 5 sí espera solo un DATE, así que $p_fecha está bien.
                $stmt = $conn->prepare("CALL sp_reporte_viajes_alta_ocupacion(?)");
                $stmt->bind_param("s", $p_fecha);
                $stmt->execute();
                $results = $stmt->get_result();
                $stmt->close();
            }
        }

    } catch (Exception $e) {
        $error_msg = "Error al ejecutar el reporte: " . $e->getMessage();
        if(isset($stmt)) $stmt->close();
    }
    
    // Limpiar cualquier resultado pendiente
    while($conn->more_results() && $conn->next_result());
}
?>

<div class="card">
    <h3 class="card-header">Generador de Reportes</h3>
    
    <form action="reportes.php" method="GET" id="reportForm">
        <input type="hidden" name="run_report" value="1">
        
        <div class="form-group">
            <label for="report_id">Seleccione el Reporte</label>
            <select id="report_id" name="report_id" class="form-control" onchange="toggleReportInputs()">
                <option value="1" <?php echo $report_id == '1' ? 'selected' : ''; ?>>1. Lista de Pasajeros y Tripulantes por Viaje</option>
                <option value="2" <?php echo $report_id == '2' ? 'selected' : ''; ?>>2. Cantidad Asientos Online por Viaje</option>
                <option value="3" <?php echo $report_id == '3' ? 'selected' : ''; ?>>3. Cantidad Asientos Presencial por Viaje</option>
                <option value="4" <?php echo $report_id == '4' ? 'selected' : ''; ?>>4. Monto de Venta por Pisos por Viaje</option>
                <option value="5" <?php echo $report_id == '5' ? 'selected' : ''; ?>>5. Viajes con >80% Capacidad por Fecha</option>
            </select>
        </div>

        <div id="inputs_report_1_4" class="form-grid" style="display: <?php echo in_array($report_id, ['1', '2', '3', '4']) ? 'grid' : 'none'; ?>;">
            <div class="form-group">
                <label for="origen">Origen</label>
                <select id="origen" name="origen" class="form-control">
                    <option value="">Seleccione origen...</option>
                    <?php 
                    // Resetear el puntero del resultado por si se usó antes
                    $origenes_result->data_seek(0);
                    while($r = $origenes_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($r['origen']); ?>" <?php echo $p_origen == $r['origen'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['origen']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="destino">Destino</label>
                <select id="destino" name="destino" class="form-control">
                    <option value="">Seleccione destino...</option>
                    <?php 
                    // Resetear el puntero
                    $destinos_result->data_seek(0);
                    while($r = $destinos_result->fetch_assoc()): 
                    ?>
                        <option value="<?php echo htmlspecialchars($r['destino']); ?>" <?php echo $p_destino == $r['destino'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($r['destino']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="turno">Turno (HH:MM)</label>
                <input type="time" id="turno" name="turno" class="form-control" value="<?php echo htmlspecialchars($p_turno); ?>">
            </div>
        </div>

        <div id="inputs_report_5" class="form-grid" style="display: <?php echo $report_id == '5' ? 'grid' : 'none'; ?>;">
            </div>

        <div class="form-grid" style="grid-template-columns: 1fr 3fr;">
             <div class="form-group">
                <label for="fecha">Fecha Partida</label>
                <input type="date" id="fecha" name="fecha" class="form-control" value="<?php echo htmlspecialchars($p_fecha); ?>">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Generar Reporte</button>
        </div>
    </form>
</div>

<?php if (isset($_GET['run_report'])): ?>
<div class="card">
    <h3 class="card-header"><?php echo htmlspecialchars($report_title); ?></h3>
    
    <?php if (!empty($error_msg)): ?>
        <div class="alert danger"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php elseif ($results && $results->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <?php
                    // Imprimir encabezados de tabla
                    $fields = $results->fetch_fields();
                    foreach ($fields as $field) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Imprimir filas de datos
                while ($row = $results->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $data) {
                        echo "<td>" . htmlspecialchars($data) . "</td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    <?php elseif ($results): ?>
        <div class="alert">No se encontraron resultados para los parámetros seleccionados.</div>
    <?php else: ?>
        <div class="alert danger">Ocurrió un error al procesar la solicitud o $results es nulo.</div>
    <?php endif; ?>
    
    <?php
    // Limpieza final
    if ($results) $results->free();
    ?>
</div>
<?php endif; ?>

<script>
function toggleReportInputs() {
    var reportId = document.getElementById('report_id').value;
    
    if (reportId >= '1' && reportId <= '4') {
        document.getElementById('inputs_report_1_4').style.display = 'grid';
        document.getElementById('inputs_report_5').style.display = 'none';
    } else if (reportId == '5') {
        document.getElementById('inputs_report_1_4').style.display = 'none';
        document.getElementById('inputs_report_5').style.display = 'grid';
    }
}
// Ejecutar al cargar la página por si vienes con parámetros GET
toggleReportInputs();
</script>

<?php include 'includes/admin_footer.php'; ?>