<?php
session_start();
include '../config/db.php';

// Redirigir si no está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Redirigir si no hay viaje seleccionado
if (!isset($_GET['viaje'])) {
    header('Location: ../index.php');
    exit();
}

$id_viaje = (int)$_GET['viaje'];
$id_usuario = $_SESSION['id_usuario'];

// 1. Obtener información del viaje y del bus
$viaje_sql = "
    SELECT v.id_viaje, v.fecha_salida, v.id_bus,
           r.origen, r.destino,
           b.modelo, b.tipo_servicio, b.capacidad_piso1, b.capacidad_piso2
    FROM viajes v
    JOIN rutas r ON v.id_ruta = r.id_ruta
    JOIN buses b ON v.id_bus = b.id_bus
    WHERE v.id_viaje = $id_viaje
";
$viaje_result = $conn->query($viaje_sql);

if ($viaje_result->num_rows == 0) {
    echo "Error: Viaje no encontrado.";
    exit();
}
$viaje = $viaje_result->fetch_assoc();
$id_bus = $viaje['id_bus'];

// 2. Obtener TODOS los asientos para ESE bus
$asientos_sql = "SELECT id_asiento, numero_asiento, piso, precio, estado FROM asientos WHERE id_bus = $id_bus ORDER BY piso, numero_asiento";
$asientos_result = $conn->query($asientos_sql);

$asientos_piso1 = [];
$asientos_piso2 = [];
while ($asiento = $asientos_result->fetch_assoc()) {
    if ($asiento['piso'] == 1) {
        $asientos_piso1[] = $asiento;
    } else {
        $asientos_piso2[] = $asiento;
    }
}

// 3. Obtener asientos YA RESERVADOS para ESE viaje
$reservados_sql = "SELECT id_asiento FROM reservas WHERE id_viaje = $id_viaje AND estado != 'cancelada'";
$reservados_result = $conn->query($reservados_sql);
$asientos_reservados = [];
while ($row = $reservados_result->fetch_assoc()) {
    $asientos_reservados[] = $row['id_asiento'];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Asientos</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
            gap: 20px;
            flex-wrap: wrap; /* Para responsive */
        }
        .bus-container {
            flex: 3;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .resumen-container {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: sticky; /* Para que el resumen se quede fijo */
            top: 20px;
            align-self: flex-start; /* Alinea al inicio */
        }
        h1, h2, h3 {
            color: #333;
        }
        .bus-piso {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .bus-piso h3 {
            border-bottom: 2px solid #004a99;
            padding-bottom: 5px;
            margin-top: 0;
        }
        .asientos-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 asientos por fila */
            gap: 10px;
            justify-items: center;
        }
        .asiento {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #333;
            background-color: #c8e6c9; /* Verde (Disponible) */
            border: 1px solid #a5d6a7;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        .asiento:hover:not(.ocupado):not(.seleccionado) {
            background-color: #a5d6a7;
            transform: scale(1.05);
        }
        .asiento.ocupado {
            background-color: #ffcdd2; /* Rojo (Ocupado) */
            border-color: #ef9a9a;
            cursor: not-allowed;
            color: #c62828;
        }
        .asiento.seleccionado {
            background-color: #004a99; /* Azul (Seleccionado) */
            border-color: #002a5c;
            color: white;
            transform: scale(1.1);
        }
        .leyenda {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .leyenda-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .leyenda-item div {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }
        #resumen-asientos li {
            list-style: none;
            padding: 5px 0;
            border-bottom: 1px dashed #ccc;
            display: flex;
            justify-content: space-between;
        }
        #resumen-total {
            font-size: 1.5em;
            font-weight: bold;
            color: #d9534f; /* Rojo */
            margin-top: 10px;
        }
        .btn-pagar {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            margin-top: 20px;
        }
        .btn-pagar:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .resumen-container {
                position: static; /* Quita el sticky en móvil */
            }
            .asientos-grid {
                grid-template-columns: repeat(3, 1fr); /* 3 asientos en móvil */
            }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <div class="bus-container">
            <h1>Selecciona tus Asientos</h1>
            <h2><?php echo $viaje['origen'] . " - " . $viaje['destino']; ?></h2>
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?> | <strong>Servicio:</strong> <?php echo $viaje['tipo_servicio']; ?></p>

            <div class="leyenda">
                <div class="leyenda-item"><div class="asiento" style="width:20px; height:20px;"></div> Disponible</div>
                <div class="leyenda-item"><div class="asiento ocupado" style="width:20px; height:20px;"></div> Ocupado</div>
                <div class="leyenda-item"><div class="asiento seleccionado" style="width:20px; height:20px;"></div> Seleccionado</div>
            </div>

            <!-- Piso 1 -->
            <?php if ($viaje['capacidad_piso1'] > 0): ?>
            <div class="bus-piso">
                <h3>Piso 1 (<?php echo "S/ " . number_format($asientos_piso1[0]['precio'], 2); ?>)</h3>
                <div class="asientos-grid">
                    <?php
                    foreach ($asientos_piso1 as $asiento) {
                        $id_asiento = $asiento['id_asiento'];
                        $numero = $asiento['numero_asiento'];
                        $precio = $asiento['precio'];
                        
                        // Comprobar si está en la lista de reservados
                        $esOcupado = in_array($id_asiento, $asientos_reservados);
                        
                        $clase = $esOcupado ? 'ocupado' : '';
                        $data_attrs = $esOcupado ? '' : "data-id='$id_asiento' data-numero='$numero' data-precio='$precio'";
                        
                        echo "<div class='asiento $clase' $data_attrs>$numero</div>";
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Piso 2 -->
            <?php if ($viaje['capacidad_piso2'] > 0): ?>
            <div class="bus-piso">
                <h3>Piso 2 (<?php echo "S/ " . number_format($asientos_piso2[0]['precio'], 2); ?>)</h3>
                <div class="asientos-grid">
                    <?php
                    foreach ($asientos_piso2 as $asiento) {
                        $id_asiento = $asiento['id_asiento'];
                        $numero = $asiento['numero_asiento'];
                        $precio = $asiento['precio'];
                        
                        // Comprobar si está en la lista de reservados
                        $esOcupado = in_array($id_asiento, $asientos_reservados);
                        
                        $clase = $esOcupado ? 'ocupado' : '';
                        $data_attrs = $esOcupado ? '' : "data-id='$id_asiento' data-numero='$numero' data-precio='$precio'";
                        
                        echo "<div class='asiento $clase' $data_attrs>$numero</div>";
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="resumen-container">
            <h3>Resumen de Compra</h3>
            <form id="form-pago" action="pago.php" method="POST">
                <input type="hidden" name="id_viaje" value="<?php echo $id_viaje; ?>">
                
                <ul id="resumen-asientos">
                    <!-- Los asientos seleccionados se añaden aquí con JS -->
                </ul>
                
                <div id="resumen-total">
                    Total: S/ 0.00
                </div>
                
                <!-- Inputs ocultos para enviar los datos -->
                <div id="hidden-inputs-container">
                    <!-- <input type="hidden" name="asientos[]" value="id_asiento"> -->
                </div>
                <input type="hidden" id="total_pagar" name="total_pagar" value="0">

                <button type="submit" id="btn-pagar" class="btn-pagar" disabled>Realizar Pago</button>
            </form>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const asientosGrid = document.querySelectorAll('.asiento:not(.ocupado)');
        const resumenAsientos = document.getElementById('resumen-asientos');
        const resumenTotal = document.getElementById('resumen-total');
        const btnPagar = document.getElementById('btn-pagar');
        const hiddenInputsContainer = document.getElementById('hidden-inputs-container');
        const totalPagarInput = document.getElementById('total_pagar');

        let asientosSeleccionados = {}; // Usar un objeto para fácil gestión

        asientosGrid.forEach(asiento => {
            asiento.addEventListener('click', () => {
                const id = asiento.dataset.id;
                
                if (asiento.classList.contains('seleccionado')) {
                    // Deseleccionar
                    asiento.classList.remove('seleccionado');
                    delete asientosSeleccionados[id];
                } else {
                    // Seleccionar
                    asiento.classList.add('seleccionado');
                    asientosSeleccionados[id] = {
                        numero: asiento.dataset.numero,
                        precio: parseFloat(asiento.dataset.precio)
                    };
                }
                
                actualizarResumen();
            });
        });

        function actualizarResumen() {
            // Limpiar resumen
            resumenAsientos.innerHTML = '';
            hiddenInputsContainer.innerHTML = '';
            
            let total = 0;
            
            // Llenar resumen
            for (const id in asientosSeleccionados) {
                const asiento = asientosSeleccionados[id];
                
                // Añadir a la lista visible
                const li = document.createElement('li');
                li.innerHTML = `<span>Asiento N° ${asiento.numero}</span> <strong>S/ ${asiento.precio.toFixed(2)}</strong>`;
                resumenAsientos.appendChild(li);
                
                // Añadir al input oculto para el form
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'asientos[]'; // Enviar como array
                input.value = id;
                hiddenInputsContainer.appendChild(input);
                
                total += asiento.precio;
            }
            
            // Actualizar total
            resumenTotal.textContent = `Total: S/ ${total.toFixed(2)}`;
            totalPagarInput.value = total; // Actualizar el input oculto del total

            // Habilitar/Deshabilitar botón
            if (total > 0) {
                btnPagar.disabled = false;
            } else {
                btnPagar.disabled = true;
            }
        }
    });
    </script>

</body>
</html>