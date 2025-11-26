<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';
include '../templates/header.php';

$origen = $_GET['origen'] ?? '';
$destino = $_GET['destino'] ?? '';
$fecha = $_GET['fecha_ida'] ?? '';

if ($origen && $destino && $fecha) {
    $fecha_inicio = $fecha . ' 00:00:00';
    $fecha_fin = $fecha . ' 23:59:59';

    $query = "SELECT vvp.*, vvp.`N° Viaje` as id_viaje
              FROM v_viajes_programados AS vvp
              JOIN viajes AS v ON vvp.`N° Viaje` = v.id_viaje
              WHERE vvp.origen = ? 
                AND vvp.destino = ? 
                AND v.fecha_salida BETWEEN ? AND ?
                AND vvp.estado = 'programado'";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $origen, $destino, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <div class="contenedor-viajes">
      <h2>Tickets disponibles para <?= strtoupper($origen) ?> - <?= strtoupper($destino) ?> el <?= date('d/m/Y', strtotime($fecha)) ?></h2>

      <div class="resultados-scroll">
        <?php if ($result->num_rows > 0): ?>
          <?php while ($viaje = $result->fetch_assoc()): ?>
            <div class="tarjeta-viaje">
              <h3>Servicio: <?= htmlspecialchars($viaje['Servicio']) ?></h3>
              <p><strong>Salida:</strong> <?= htmlspecialchars($viaje['origen']) ?> <?= htmlspecialchars($viaje['Salida']) ?></p>
              <p><strong>Llegada:</strong> <?= htmlspecialchars($viaje['destino']) ?> <?= htmlspecialchars($viaje['Llegada']) ?></p>
              <p><strong>Duración:</strong> <?= htmlspecialchars($viaje['Duracion']) ?></p>
              <p><strong>Bus:</strong> <?= htmlspecialchars($viaje['Unidad']) ?></p>
              <p><strong>Asientos libres:</strong> Piso 1: <?= $viaje['Asientos_Libres_Piso1'] ?> | Piso 2: <?= $viaje['Asientos_Libres_Piso2'] ?></p>
              <p><strong>Precio desde:</strong> <?= $viaje['Precio_Piso1'] ?></p>
              <form action="../compra/seleccionar_asientos.php" method="GET">
                <input type="hidden" name="viaje" value="<?= urlencode($viaje['id_viaje']) ?>">
                <button class="btn-comprar">Elegir Asiento</button>
              </form>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-results"><p>No se encontraron viajes para la ruta y fecha seleccionadas.</p></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Secciones informativas -->
    <div class="servicios">
      <h3>Nuestros Servicios</h3>
      <div class="bloques-servicio">
        <div class="bloque"><p>Transporte de pasajeros</p></div>
        <div class="bloque"><p>Encomiendas y paquetería</p></div>
        <div class="bloque"><p>Convenios con empresas</p></div>
      </div>
    </div>

    <div class="info-compra">
      <h3>Compra Segura</h3>
      <p>Compra tus pasajes de forma segura desde casa.</p>
      <h3>Medios de Pago de Confianza</h3>
      <p>Cumplimos con estándares PCI DSS para proteger tus datos.</p>
      <h3>Facilidad</h3>
      <p>Consulta horarios y precios desde tu celular o computadora.</p>
    </div>

    <div class="faq">
      <h3>Preguntas Frecuentes</h3>
      <p><strong>¿Puedo transportar animales?</strong> Solo con permiso presencial.</p>
      <p><strong>¿Puedo posponer mi boleto?</strong> Sí, según condiciones de la empresa.</p>
      <p><strong>¿Cómo comprar pasajes para menores?</strong> Requiere autorización del tutor.</p>
    </div>

<?php
} else {
    echo "<div class='contenedor-viajes'><p>Faltan parámetros de búsqueda. Por favor, regresa al inicio y completa el formulario.</p></div>";
}

include '../templates/footer.php';
?>


