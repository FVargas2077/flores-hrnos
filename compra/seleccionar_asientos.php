<?php
// compra/seleccionar_asientos.php
include '../config/db.php';

// --- Control de Acceso ---
// 1. Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error_login'] = "Debes iniciar sesión para comprar un pasaje.";
    header('Location: ../auth/login.php');
    exit;
}

// 2. Verificar que se pasó un ID de viaje
if (!isset($_GET['viaje']) || empty($_GET['viaje'])) {
    header('Location: ../index.php');
    exit;
}

$id_viaje = (int)$_GET['viaje'];

// --- Obtener datos del Viaje y Bus ---
$sql_viaje = "SELECT 
    v.id_bus, v.fecha_salida,
    b.tipo_servicio, b.capacidad_piso1, b.capacidad_piso2,
    r.origen, r.destino
FROM viajes v
JOIN buses b ON v.id_bus = b.id_bus
JOIN rutas r ON v.id_ruta = r.id_ruta
WHERE v.id_viaje = $id_viaje";

$viaje_res = $conn->query($sql_viaje);
if ($viaje_res->num_rows == 0) {
    echo "El viaje no existe."; // Manejar error
    exit;
}
$viaje = $viaje_res->fetch_assoc();
$id_bus = $viaje['id_bus'];

// --- Obtener TODOS los asientos de ESE BUS ---
$sql_asientos = "SELECT id_asiento, numero_asiento, piso, precio, ubicacion 
                 FROM asientos 
                 WHERE id_bus = $id_bus 
                 ORDER BY piso, numero_asiento";
$asientos_res = $conn->query($sql_asientos);
$asientos = ['piso1' => [], 'piso2' => []];
while ($row = $asientos_res->fetch_assoc()) {
    if ($row['piso'] == 1) {
        $asientos['piso1'][] = $row;
    } else {
        $asientos['piso2'][] = $row;
    }
}

// --- Obtener los asientos OCUPADOS de ESE VIAJE ---
$sql_ocupados = "SELECT id_asiento 
                 FROM reservas 
                 WHERE id_viaje = $id_viaje 
                   AND (estado = 'confirmada' OR estado = 'pendiente')";
$ocupados_res = $conn->query($sql_ocupados);
$asientos_ocupados = [];
while ($row = $ocupados_res->fetch_assoc()) {
    $asientos_ocupados[] = $row['id_asiento'];
}

// Función para renderizar un asiento
function render_asiento($asiento, $ocupados) {
    $id = $asiento['id_asiento'];
    $num = $asiento['numero_asiento'];
    $precio = $asiento['precio'];
    $es_ocupado = in_array($id, $ocupados);

    if ($es_ocupado) {
        return "<div class='asiento ocupado'>$num</div>";
    } else {
        // Asiento libre
        return "
        <label class='asiento libre' for='asiento_$id' data-precio='$precio'>
            <input type='checkbox' name='asientos[]' id='asiento_$id' value='$id'>
            <span>$num</span>
        </label>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Asientos</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; }
        nav { background-color: #004a99; padding: 1em; color: white; margin-bottom: 2em; }
        nav a { color: white; text-decoration: none; padding: 0 1em; }
        .container { max-width: 1000px; margin: auto; }
        .bus-layout { display: flex; gap: 2em; }
        .piso { background-color: white; padding: 1.5em; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .piso h2 { margin-top: 0; color: #004a99; border-bottom: 2px solid #f0f0f0; padding-bottom: 0.5em; }
        .asientos-grid { display: grid; grid-template-columns: repeat(4, 60px); gap: 10px; justify-content: center; }
        .asiento { width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; border-radius: 5px; font-weight: bold; }
        .asiento.ocupado { background-color: #e0e0e0; color: #888; border: 1px solid #ccc; }
        .asiento.libre { background-color: #c8e6c9; border: 1px solid #81c784; cursor: pointer; }
        .asiento.libre:hover { background-color: #a5d6a7; }
        .asiento input[type='checkbox'] { display: none; }
        .asiento input[type='checkbox']:checked + span {
            background-color: #d9534f; color: white; border-color: #c9302c;
            display: block; width: 100%; height: 100%;
            border-radius: 5px;
            display: flex; justify-content: center; align-items: center;
        }
        .asiento span { display: block; width: 100%; height: 100%; border-radius: 5px; display: flex; justify-content: center; align-items: center; }
        .resumen { background-color: white; padding: 1.5em; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); flex-shrink: 0; width: 300px; height: fit-content; }
        .resumen button { background-color: #d9534f; color: white; padding: 0.8em; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em; width: 100%; }
        .leyenda { display: flex; gap: 1em; margin-top: 1em; justify-content: center; }
        .leyenda div { display: flex; align-items: center; gap: 0.5em; }
        .leyenda span { width: 20px; height: 20px; border-radius: 3px; }
    </style>
</head>
<body>
    <nav>
        <a href="../index.php"><b>Buses Flores</b></a>
        <div style="float:right;">
            <a href="../cliente/index.php">Mi Perfil</a>
            <a href="../auth/logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <h1>Selecciona tus asientos</h1>
        <p>Viaje: <strong><?php echo "{$viaje['origen']} a {$viaje['destino']}"; ?></strong></p>
        <p>Fecha: <strong><?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?></strong></p>
        
        <div class="leyenda">
            <div><span style="background-color: #c8e6c9; border: 1px solid #81c784;"></span> Libre</div>
            <div><span style="background-color: #d9534f; border: 1px solid #c9302c;"></span> Seleccionado</div>
            <div><span style="background-color: #e0e0e0; border: 1px solid #ccc;"></span> Ocupado</div>
        </div>
        
        <form action="pago.php" method="POST">
            <input type="hidden" name="id_viaje" value="<?php echo $id_viaje; ?>">
            <div classs="bus-layout" style="display: flex; gap: 2em; margin-top: 2em;">
                <div class="piso" style="flex-grow: 1;">
                    <h2>Piso 1 (<?php echo $viaje['capacidad_piso1']; ?> asientos)</h2>
                    <div class="asientos-grid">
                        <?php foreach ($asientos['piso1'] as $asiento) {
                            echo render_asiento($asiento, $asientos_ocupados);
                        } ?>
                    </div>
                </div>

                <?php if ($viaje['capacidad_piso2'] > 0): ?>
                <div class="piso" style="flex-grow: 1;">
                    <h2>Piso 2 (<?php echo $viaje['capacidad_piso2']; ?> asientos)</h2>
                    <div class="asientos-grid">
                         <?php foreach ($asientos['piso2'] as $asiento) {
                            echo render_asiento($asiento, $asientos_ocupados);
                        } ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="resumen">
                    <h2>Resumen de Compra</h2>
                    <ul id="lista-asientos">
                        <!-- Asientos seleccionados se añadirán aquí -->
                    </ul>
                    <hr>
                    <h3>Total: S/ <span id="total-precio">0.00</span></h3>
                    <button type="submit" id="btn-continuar" disabled>Continuar al Pago</button>
                </div>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const asientosGrid = document.querySelector('.bus-layout');
        const listaAsientos = document.getElementById('lista-asientos');
        const totalPrecioEl = document.getElementById('total-precio');
        const btnContinuar = document.getElementById('btn-continuar');
        
        let seleccionados = [];
        let totalPrecio = 0;

        asientosGrid.addEventListener('change', (e) => {
            if (e.target.type === 'checkbox') {
                const asientoLabel = e.target.closest('.asiento');
                const idAsiento = e.target.value;
                const numAsiento = asientoLabel.querySelector('span').innerText;
                const precio = parseFloat(asientoLabel.dataset.precio);

                if (e.target.checked) {
                    // Añadir
                    seleccionados.push({ id: idAsiento, num: numAsiento, precio: precio });
                    totalPrecio += precio;
                } else {
                    // Quitar
                    seleccionados = seleccionados.filter(a => a.id !== idAsiento);
                    totalPrecio -= precio;
                }
                
                actualizarResumen();
            }
        });

        function actualizarResumen() {
            listaAsientos.innerHTML = '';
            seleccionados.forEach(a => {
                const li = document.createElement('li');
                li.innerHTML = `Asiento N° ${a.num} <span>S/ ${a.precio.toFixed(2)}</span>`;
                listaAsientos.appendChild(li);
            });
            
            totalPrecioEl.innerText = totalPrecio.toFixed(2);

            if (seleccionados.length > 0) {
                btnContinuar.disabled = false;
            } else {
                btnContinuar.disabled = true;
            }
        }
    });
    </script>

</body>
</html>
