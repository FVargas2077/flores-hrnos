USE db_buses;

DELIMITER $$

-- Borrar los SP antiguos si existen (para reemplazarlos)
DROP PROCEDURE IF EXISTS sp_reporte_pasajeros_tripulantes;
DROP PROCEDURE IF EXISTS sp_reporte_asientos_online;
DROP PROCEDURE IF EXISTS sp_reporte_asientos_presencial;
DROP PROCEDURE IF EXISTS sp_reporte_monto_por_piso;
DROP PROCEDURE IF EXISTS sp_reporte_viajes_alta_ocupacion;

$$
DELIMITER ;

DELIMITER $$

-- 1) Lista de personas (pasajeros y tripulantes) en un viaje dado.
--    CORREGIDO: Acepta DATETIME, usa nuevo JOIN
CREATE PROCEDURE sp_reporte_pasajeros_tripulantes(
    IN p_origen VARCHAR(100),
    IN p_destino VARCHAR(100),
    IN p_fecha_partida DATETIME
)
BEGIN
    DECLARE v_id_viaje INT;
    DECLARE v_id_conductor INT;
    DECLARE v_id_copiloto INT;

    -- Encontrar el viaje
    SELECT v.id_viaje, v.id_conductor, v.id_copiloto
    INTO v_id_viaje, v_id_conductor, v_id_copiloto
    FROM viajes v
    JOIN rutas r ON v.id_ruta = r.id_ruta
    WHERE r.origen = p_origen
      AND r.destino = p_destino
      AND v.fecha_salida = p_fecha_partida
    LIMIT 1;

    -- Reporte
    -- 1. Pasajeros
    SELECT 
        'Pasajero' as Tipo,
        u.nombre,
        u.apellidos,
        u.dni,
        u.telefono,
        a.numero_asiento,
        a.piso
    FROM reservas res
    JOIN usuarios u ON res.id_usuario = u.id_usuario
    JOIN asientos a ON res.id_asiento = a.id_asiento
    -- CORREGIDO: Join 'reservas' con 'pagos'
    JOIN pagos p ON res.id_pago = p.id_pago
    WHERE res.id_viaje = v_id_viaje
      AND res.estado = 'confirmada'
      AND p.estado = 'completado'

    UNION

    -- 2. Tripulantes (Esta parte estaba bien)
    SELECT 
        tip.nombre_tipo as Tipo,
        u.nombre,
        u.apellidos,
        u.dni,
        u.telefono,
        NULL as numero_asiento,
        NULL as piso
    FROM trabajadores t
    JOIN usuarios u ON t.id_usuario = u.id_usuario
    JOIN tipos_trabajador tip ON t.id_tipo = tip.id_tipo
    WHERE t.id_trabajador IN (v_id_conductor, v_id_copiloto);

END$$


-- 2) Cantidad de asientos comprados vía online
--    CORREGIDO: Acepta DATETIME, usa nuevo JOIN
CREATE PROCEDURE sp_reporte_asientos_online(
    IN p_origen VARCHAR(100),
    IN p_destino VARCHAR(100),
    IN p_fecha_partida DATETIME
)
BEGIN
    DECLARE v_id_viaje INT;

    SELECT v.id_viaje INTO v_id_viaje
    FROM viajes v
    JOIN rutas r ON v.id_ruta = r.id_ruta
    WHERE r.origen = p_origen
      AND r.destino = p_destino
      AND v.fecha_salida = p_fecha_partida
    LIMIT 1;

    SELECT 
        COUNT(res.id_reserva) as 'AsientosCompradosOnline'
    FROM reservas res
    -- CORREGIDO: Join 'reservas' con 'pagos'
    JOIN pagos p ON res.id_pago = p.id_pago
    WHERE res.id_viaje = v_id_viaje
      AND res.estado = 'confirmada'
      AND p.estado = 'completado';
    -- Nota: Sigue contando TODAS las ventas confirmadas como 'online'
    --       porque no hay un campo 'canal_venta'.
END$$


-- 3) Cantidad de asientos comprados vía presencial
--    CORREGIDO: Acepta DATETIME
CREATE PROCEDURE sp_reporte_asientos_presencial(
    IN p_origen VARCHAR(100),
    IN p_destino VARCHAR(100),
    IN p_fecha_partida DATETIME
)
BEGIN
    -- No hay forma de diferenciar online/presencial en la BD actual.
    -- Devuelve 0.
    SELECT 0 as 'AsientosCompradosPresencial';
END$$


-- 4) Monto de venta de asientos agrupados por pisos
--    CORREGIDO: Acepta DATETIME, usa nuevo JOIN
CREATE PROCEDURE sp_reporte_monto_por_piso(
    IN p_origen VARCHAR(100),
    IN p_destino VARCHAR(100),
    IN p_fecha_partida DATETIME
)
BEGIN
    DECLARE v_id_viaje INT;

    SELECT v.id_viaje INTO v_id_viaje
    FROM viajes v
    JOIN rutas r ON v.id_ruta = r.id_ruta
    WHERE r.origen = p_origen
      AND r.destino = p_destino
      AND v.fecha_salida = p_fecha_partida
    LIMIT 1;

    SELECT
        a.piso,
        SUM(res.precio_final) as MontoTotal
    FROM reservas res
    -- CORREGIDO: Join 'reservas' con 'pagos'
    JOIN pagos p ON res.id_pago = p.id_pago
    JOIN asientos a ON res.id_asiento = a.id_asiento
    WHERE res.id_viaje = v_id_viaje
      AND res.estado = 'confirmada'
      AND p.estado = 'completado'
    GROUP BY a.piso
    ORDER BY a.piso;
END$$


-- 5) Lista de viajes que vendieron asientos más del 80% de capacidad
--    para una fecha dada.
--    CORREGIDO: Usa nuevo JOIN en el LEFT JOIN
CREATE PROCEDURE sp_reporte_viajes_alta_ocupacion(
    IN p_fecha_partida_dia DATE
)
BEGIN
    SELECT 
        r.origen,
        r.destino,
        v.fecha_salida,
        b.capacidad_piso1,
        b.capacidad_piso2,
        (b.capacidad_piso1 + IFNULL(b.capacidad_piso2, 0)) as CapacidadTotal,
        COUNT(res.id_reserva) as AsientosVendidos,
        (COUNT(res.id_reserva) * 100.0 / (b.capacidad_piso1 + IFNULL(b.capacidad_piso2, 0))) as PorcentajeOcupacion
    FROM viajes v
    JOIN rutas r ON v.id_ruta = r.id_ruta
    JOIN buses b ON v.id_bus = b.id_bus
    -- CORREGIDO: 
    -- Hacemos LEFT JOIN a 'reservas' que CUMPLAN la condición
    -- de tener un pago completado.
    LEFT JOIN reservas res ON v.id_viaje = res.id_viaje 
                           AND res.estado = 'confirmada' 
                           AND res.id_pago IS NOT NULL
                           AND (SELECT p.estado FROM pagos p WHERE p.id_pago = res.id_pago) = 'completado'
    WHERE DATE(v.fecha_salida) = p_fecha_partida_dia
    GROUP BY v.id_viaje, r.origen, r.destino, v.fecha_salida, b.capacidad_piso1, b.capacidad_piso2
    HAVING PorcentajeOcupacion > 80.0
    ORDER BY PorcentajeOcupacion DESC;
END$$

DELIMITER ;