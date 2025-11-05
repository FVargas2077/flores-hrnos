USE db_buses;

-- 1. Eliminar el procedimiento anterior que tiene errores
DROP PROCEDURE IF EXISTS sp_crear_reserva;

-- 2. Crear el nuevo procedimiento corregido
DELIMITER //
CREATE PROCEDURE sp_crear_reserva(
    IN p_id_viaje INT,
    IN p_id_usuario INT,
    IN p_id_asiento INT,
    OUT p_id_reserva INT
)
BEGIN
    DECLARE v_precio_asiento DECIMAL(10,2);
    
    -- Corregido: Obtener el precio directamente de la tabla de asientos
    SELECT precio INTO v_precio_asiento
    FROM asientos
    WHERE id_asiento = p_id_asiento;
    
    -- Crear la reserva con el precio correcto
    INSERT INTO reservas (id_viaje, id_usuario, id_asiento, precio_final, estado)
    VALUES (p_id_viaje, p_id_usuario, p_id_asiento, v_precio_asiento, 'confirmada');
    
    -- Obtener el ID de la reserva recién creada
    SET p_id_reserva = LAST_INSERT_ID();
    
    -- Actualizar estado del asiento (esto ya estaba bien)
    UPDATE asientos SET estado = FALSE WHERE id_asiento = p_id_asiento;
END //
DELIMITER ;