USE u914095763_g7;

-- -----------------------------------------------------
-- 1. Inserción de 5 nuevas RUTAS de prueba
-- -----------------------------------------------------
-- Se añaden rutas populares de norte, centro y sur.
INSERT INTO rutas (origen, destino, distancia, duracion_estimada, precio_base) VALUES
('AREQUIPA', 'PUNO', 297, '06:00:00', 40.00),
('LIMA', 'HUANCAYO', 310, '08:00:00', 60.00),
('CUSCO', 'PUNO', 389, '07:00:00', 50.00),
('TRUJILLO', 'CHICLAYO', 206, '03:30:00', 30.00),
('LIMA', 'CHIMBOTE', 428, '07:00:00', 55.00);

-- -----------------------------------------------------
-- 2. Inserción de 5 nuevos VIAJES de prueba
-- -----------------------------------------------------
-- Programados entre el 26 y 30 de Diciembre de 2025.
-- CORREGIDO: Se añade LIMIT 1 a todas las subconsultas
--            para evitar el error #1242.

INSERT INTO viajes (id_ruta, id_bus, fecha_salida, fecha_llegada, estado) VALUES
(
    -- Ruta: LIMA a HUANCAYO
    (SELECT id_ruta FROM rutas WHERE origen = 'LIMA' AND destino = 'HUANCAYO' LIMIT 1), 
    1, -- Bus 1
    '2025-12-26 22:00:00', -- Fecha de Salida (Noche)
    '2025-12-27 06:00:00', -- Fecha de Llegada
    'programado'
),
(
    -- Ruta: AREQUIPA a PUNO
    (SELECT id_ruta FROM rutas WHERE origen = 'AREQUIPA' AND destino = 'PUNO' LIMIT 1), 
    2, -- Bus 2
    '2025-12-27 08:00:00', -- Fecha de Salida (Día)
    '2025-12-27 14:00:00', -- Fecha de Llegada
    'programado'
),
(
    -- Ruta: TRUJILLO a CHICLAYO
    (SELECT id_ruta FROM rutas WHERE origen = 'TRUJILLO' AND destino = 'CHICLAYO' LIMIT 1), 
    3, -- Bus 3
    '2025-12-28 14:00:00', -- Fecha de Salida (Tarde)
    '2025-12-28 17:30:00', -- Fecha de Llegada
    'programado'
),
(
    -- Ruta: LIMA a CHIMBOTE
    (SELECT id_ruta FROM rutas WHERE origen = 'LIMA' AND destino = 'CHIMBOTE' LIMIT 1), 
    1, -- Bus 1
    '2025-12-29 09:30:00', -- Fecha de Salida (Mañana)
    '2025-12-29 16:30:00', -- Fecha de Llegada
    'programado'
),
(
    -- Ruta: CUSCO a PUNO
    (SELECT id_ruta FROM rutas WHERE origen = 'CUSCO' AND destino = 'PUNO' LIMIT 1), 
    2, -- Bus 2
    '2025-12-30 07:00:00', -- Fecha de Salida (Mañana)
    '2025-12-30 14:00:00', -- Fecha de Llegada
    'programado'
);